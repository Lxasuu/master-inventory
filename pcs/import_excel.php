<?php
require_once __DIR__ . '/../partials/bootstrap.php';
require_once __DIR__ . '/../partials/csrf.php';
require_role(['pic', 'admin']);
require __DIR__ . '/../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

// Global Context (for get_current_user_id)
require_once __DIR__ . "/../partials/app_context.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validasi CSRF
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf($token)) {
        die("<script>alert('Token keamanan tidak valid. Silakan coba lagi.'); window.location.href='index.php';</script>");
    }

    if (!isset($_FILES['excelFile']) || $_FILES['excelFile']['error'] !== UPLOAD_ERR_OK) {
        die("<script>alert('Gagal mengupload file.'); window.location.href='index.php';</script>");
    }

    $fileTmpPath = $_FILES['excelFile']['tmp_name'];
    $fileName = $_FILES['excelFile']['name'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    $allowedExtensions = ['xls', 'xlsx', 'csv'];
    if (!in_array($fileExtension, $allowedExtensions)) {
        echo "<!DOCTYPE html><html><head><script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script></head><body><script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({ icon: 'error', title: 'Format Tidak Diizinkan', text: 'Hanya .xls, .xlsx, dan .csv yang diperbolehkan.' }).then(() => { window.location.href = 'index.php'; });
            });
        </script></body></html>";
        exit;
    }

    try {
        // Load Spreadsheet
        $spreadsheet = IOFactory::load($fileTmpPath);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        // Cek jika kosong
        if (count($rows) <= 1) {
            echo "<!DOCTYPE html><html><head><script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script></head><body><script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({ icon: 'warning', title: 'File Kosong', text: 'File Excel kosong atau tidak memiliki data.' }).then(() => { window.location.href = 'index.php'; });
                });
            </script></body></html>";
            exit;
        }

        $userId = (int)($_SESSION['user']['user_id'] ?? 0);
        $successCount = 0;
        $errorCount = 0;
        $errors = [];

        // Helper functions
        function getIdByName($pdo, $table, $columnId, $columnName, $value) {
            if (empty(trim((string)$value))) return null;
            $valStr = trim((string)$value);
            
            // Pencarian case-insensitive
            $stmt = $pdo->prepare("SELECT $columnId FROM $table WHERE LOWER($columnName) = LOWER(?) LIMIT 1");
            $stmt->execute([$valStr]);
            $res = $stmt->fetchColumn();
            
            if ($res) {
                return $res;
            } else {
                // Buat baru jika tidak ada
                $stmtInsert = $pdo->prepare("INSERT INTO $table ($columnName) VALUES (?)");
                $stmtInsert->execute([$valStr]);
                return $pdo->lastInsertId();
            }
        }

        // Loop data mulai dari baris 2 (index 1)
        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            
            // Ignore baris gantung yang kosong semua
            if (empty(array_filter($row))) {
                continue;
            }

            $unique_code = trim((string)($row[0] ?? ''));
            $unique_name = trim((string)($row[1] ?? ''));
            $loc_name    = trim((string)($row[2] ?? ''));
            $cond_name   = trim((string)($row[3] ?? ''));
            $stat_name   = trim((string)($row[4] ?? ''));
            $internet_str= strtolower(trim((string)($row[5] ?? '')));
            $ready_str   = strtolower(trim((string)($row[6] ?? '')));

            // Validasi wajib (Unique Code & Lokasi)
            if (empty($unique_code) || empty($loc_name)) {
                $errorCount++;
                $errors[] = "Baris " . ($i + 1) . ": Kode PC dan Lokasi wajib diisi.";
                continue;
            }

            // Cek duplikasi Unique Code
            $stmtCek = $pdo->prepare("SELECT pc_id FROM pcs WHERE unique_code = ?");
            $stmtCek->execute([$unique_code]);
            if ($stmtCek->fetchColumn()) {
                 $errorCount++;
                 $errors[] = "Baris " . ($i + 1) . ": Kode PC '$unique_code' sudah terdaftar.";
                 continue;
            }

            // Konversi nilai Ya/Tidak
            $internet = (in_array($internet_str, ['ya', 'yes', 'y', '1', 'true', 'aktif'])) ? 1 : 0;
            $is_ready = (in_array($ready_str, ['ya', 'yes', 'y', '1', 'true', 'ready'])) ? 1 : 0;

            // Proses Foreign Keys (Lookup/Insert logic)
            $location_id = getIdByName($pdo, 'locations', 'location_id', 'location_name', $loc_name);
            $condition_id = getIdByName($pdo, 'conditions', 'condition_id', 'condition_name', $cond_name);
            $check_status_id = getIdByName($pdo, 'check_statuses', 'check_status_id', 'status_name', $stat_name);

            // Insert Database
            try {
                $pdo->beginTransaction();

                $sql = "INSERT INTO pcs (unique_code, unique_name, location_id, condition_id, check_status_id, internet, is_ready, updated_by) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $unique_code,
                    empty($unique_name) ? '-' : $unique_name,
                    $location_id,
                    $condition_id ?: null,
                    $check_status_id ?: null,
                    $internet,
                    $is_ready,
                    $userId
                ]);

                $pc_id = $pdo->lastInsertId();

                // Catat Log
                $changeNote = "PC di-import via file Excel.";
                $sqlLog = "INSERT INTO pc_updates (pc_id, updated_by, condition_id, check_status_id, internet, is_ready, change_note) 
                           VALUES (?, ?, ?, ?, ?, ?, ?)";
                $pdo->prepare($sqlLog)->execute([
                    $pc_id,
                    $userId,
                    $condition_id ?: null,
                    $check_status_id ?: null,
                    $internet,
                    $is_ready,
                    $changeNote
                ]);

                $pdo->commit();
                $successCount++;
            } catch (Exception $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $errorCount++;
                $errors[] = "Baris " . ($i + 1) . ": Gagal menyimpan ke database (" . $e->getMessage() . ").";
            }
        } // End of For Loop

        // Siapkan pesan notifikasi
        $msgType = 'success';
        $finalMsg = "Berhasil mengimport $successCount data PC.";
        if ($errorCount > 0) {
            $msgType = 'warning';
            $finalMsg .= "\\nGagal mengimport $errorCount data.\\n";
            // Batasi tampilan error jika terlalu banyak
            if (count($errors) > 5) {
                $finalMsg .= implode("\\n", array_slice($errors, 0, 5)) . "\\n... dan " . (count($errors) - 5) . " pesan error lainnya.";
            } else {
                $finalMsg .= implode("\\n", $errors);
            }
        }
        
        $finalMsg = str_replace("'", "\\'", $finalMsg); // escape single quotes for JS
        $finalMsg = nl2br($finalMsg); // convert newlines to <br> for HTML
        $iconType = ($errorCount > 0 && $successCount === 0) ? 'error' : (($errorCount > 0) ? 'warning' : 'success');
        $title = ($iconType === 'success') ? 'Berhasil!' : (($iconType === 'warning') ? 'Selesai dengan Catatan' : 'Gagal');
        
        echo "<!DOCTYPE html><html><head>";
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "</head><body style='background-color:#f5f5f5;'>";
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: '$iconType',
                    title: '$title',
                    html: '$finalMsg',
                    confirmButtonColor: '#5b73e8'
                }).then(function() {
                    window.location.href = 'index.php';
                });
            });
        </script>";
        echo "</body></html>";
        exit;

    } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
        $errMsg = str_replace("'", "\\'", $e->getMessage());
        echo "<!DOCTYPE html><html><head>";
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "</head><body style='background-color:#f5f5f5;'>";
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Membaca File',
                    text: 'Pastikan file tidak rusak. Detail: $errMsg',
                    confirmButtonColor: '#e74c3c'
                }).then(function() {
                    window.location.href = 'index.php';
                });
            });
        </script>";
        echo "</body></html>";
        exit;
    }

} else {
    header('Location: index.php');
    exit;
}
