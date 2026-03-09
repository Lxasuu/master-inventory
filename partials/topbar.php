<?php
require_once __DIR__ . '/notifications.php';

// Pastikan variabel notifikasi tersedia untuk Topbar
if (!isset($notifUnread) || !isset($notifications)) {
    // Ambil user ID dari session (asumsi session sudah start di header utama)
    $currentUserId = $_SESSION['user']['user_id'] ?? 0;
    
    if ($currentUserId) {
        // Gunakan variable global $pdo dari koneksi db
        global $pdo; 
        if ($pdo) {
            $notifUnread   = notif_get_unread_count($pdo, $currentUserId);
            $notifications = notif_get_latest($pdo, $currentUserId);
        }
    }
}

// Default value jika masih null (misal belum login / error db)
$notifUnread   = $notifUnread   ?? 0;
$notifications = $notifications ?? [];
?>
<header id="page-topbar">
                <div class="navbar-header">
                    <div class="d-flex">
                        

                        <!-- LOGO -->
                    

                    <div class="navbar-brand-box d-flex align-items-center gap-2">

                    <!-- Logo -->
                    <a href="/HTML/index.php" class="topbar-logo-wrap d-flex align-items-center">
                        <img src="/HTML/assets/images/logo-meta copy.png"
                            alt="Meta Inventory"
                            class="topbar-logo-img">
                        
                    </a>

                    <!-- Hamburger -->
                    <button type="button"
                            class="btn header-item waves-effect topbar-hamburger"
                            id="vertical-menu-btn"
                            aria-label="Toggle menu">
                        <i class="mdi mdi-menu"></i>
                    </button>

                    

                    </div>

                        
                
                    </div>

                    <div class="d-flex">

                        <div class="dropdown d-none d-lg-inline-block ms-1">
                            <button type="button" class="btn header-item noti-icon waves-effect" data-toggle="fullscreen">
                                <i class="mdi mdi-fullscreen"></i>
                            </button>
                        </div>

                        <div class="dropdown d-inline-block">
                            <button type="button" class="btn header-item noti-icon waves-effect" id="page-header-notifications-dropdown"
                                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="mdi mdi-bell-outline bx-tada"></i>
                                <?php if ($notifUnread > 0): ?>
                                    <span class="badge bg-danger rounded-pill"><?= $notifUnread ?></span>
                                <?php endif; ?>
                            </button>
                            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0"
                                aria-labelledby="page-header-notifications-dropdown">
                                <div class="p-3">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <h6 class="m-0"> Notikasi </h6>
                                        </div>
                                        <div class="col-auto">
                                            <a href="#" class="small js-read-all-notif"> Baca semua pesan</a>
                                        </div>
                                    </div>
                                </div>
                                <div data-simplebar style="max-height: 230px;">
                                    <?php if (empty($notifications)): ?>
                                        <div class="p-3 text-muted">Belum ada notifikasi.</div>
                                    <?php else: ?>
                                    <?php foreach ($notifications as $n): ?>
                                    <?php
                                        // icon + warna berdasarkan action
                                        $icon = "mdi-information-outline";
                                        $bg   = "bg-primary";

                                        if ($n['action'] === 'CREATE_PC') { $icon = "mdi-plus-circle-outline"; $bg = "bg-success"; }
                                        if ($n['action'] === 'UPDATE_PC') { $icon = "mdi-pencil-outline";      $bg = "bg-warning"; }
                                        if ($n['action'] === 'DELETE_PC') { $icon = "mdi-delete-outline";      $bg = "bg-danger"; }

                                        // link (kalau entity pcs, arahkan ke edit)
                                        $link = "javascript:void(0)";
                                        if ($n['entity'] === 'pcs' && !empty($n['entity_id'])) {
                                        $target = "pcs/edit.php?id=" . (int)$n['entity_id'];
                                        $link = "/HTML/notification/read.php?id=".(int)$n['log_id']."&to=".urlencode($target);

                                        }

                                        $isUnread = ((int)$n['is_read'] === 0);
                                    ?>

                                    <a href="<?= htmlspecialchars($link) ?>"
                                        class="text-reset notification-item <?= $isUnread ? 'bg-light' : '' ?>">
                                        <div class="media">
                                        <div class="avatar-xs me-3">
                                            <span class="avatar-title <?= $bg ?> rounded-circle font-size-16">
                                            <i class="mdi <?= $icon ?> text-white"></i>
                                            </span>
                                        </div>
                                        <div class="media-body">
                                            <h6 class="mt-0 mb-1"><?= htmlspecialchars($n['title']) ?></h6>
                                            <div class="font-size-13 text-muted">
                                            <?php if (!empty($n['created_at'])): ?>
                                                <p class="mb-0">
                                                <i class="mdi mdi-clock-outline"></i>
                                                <?= htmlspecialchars($n['created_at']) ?>
                                                </p>
                                            <?php endif; ?>
                                            <?php if ($isUnread): ?>
                                                <span class="badge bg-danger mt-1">New</span>
                                            <?php endif; ?>
                                            </div>
                                        </div>
                                        </div>
                                    </a>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>

                            <div class="p-2 border-top">
                                <a class="btn btn-sm btn-link font-size-14 w-100 text-center js-read-all-notif" href="#">
                                    <i class="mdi mdi-arrow-right-circle me-1"></i> View More..
                                </a>
                            </div>
                        </div>
                    </div>

                     <div class="dropdown d-inline-block">
                        <button type="button" class="btn header-item waves-effect" id="page-header-user-dropdown"
                            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <img class="rounded-circle header-profile-user"
                            src="<?= htmlspecialchars($photo) ?>"
                            alt="Header Avatar">

                            <span class="d-none d-xl-inline-block ms-1">
                                <?= htmlspecialchars($fullName) ?>
                            </span>

                            <small class="d-none d-xl-inline-block text-muted ms-1">
                                <?= htmlspecialchars($email) ?>
                            </small>

                            <i class="mdi mdi-chevron-down d-none d-xl-inline-block"></i>
                        </button>

                    <div class="dropdown-menu dropdown-menu-end">
                    <!-- item-->
                        <a class="dropdown-item" href="/HTML/profile/profile.php"><i class="mdi mdi-account-circle-outline font-size-16 align-middle me-1"></i> Profil</a>
                        <!-- <a class="dropdown-item" href="#"><i class="mdi mdi-wallet-outline font-size-16 align-middle me-1"></i> My Wallet</a> -->
                         <a class="dropdown-item d-block" href="/HTML/profile/profile.php"><span class="badge badge-success float-end">11</span><i class="mdi mdi-cog-outline font-size-16 align-middle me-1"></i> Pengaturan</a>
                         <!-- <a class="dropdown-item" href="#"><i class="mdi mdi-lock-open-outline font-size-16 align-middle me-1"></i> Lock screen</a> -->
                        <div class="dropdown-divider"></div>
                            <a class="dropdown-item text-danger" href="/HTML/logout.php">
                                <i class="mdi mdi-power font-size-16 align-middle me-1 text-danger"></i> Keluar
                            </a>
                        </div>
                    </div>
                </div>
                </div>
            </header>

            <!-- Notification JS -->
            <script>
            document.addEventListener("DOMContentLoaded", function () {
                document.querySelectorAll(".js-read-all-notif").forEach(function (btn) {
                    btn.addEventListener("click", async function (e) {
                        e.preventDefault();
                        try {
                            const res = await fetch("/HTML/notification/read_all_ajax.php", {
                                method: "POST",
                                headers: { "X-Requested-With": "XMLHttpRequest" }
                            });
                            const data = await res.json();
                            if (!data.ok) return;

                            const bellBtn = document.getElementById("page-header-notifications-dropdown");
                            if (bellBtn) {
                                const badge = bellBtn.querySelector(".badge.bg-danger.rounded-pill");
                                if (badge) badge.remove();
                            }

                            // Hapus highlight & badge "New" dari tiap item
                            document.querySelectorAll(".dropdown-menu .notification-item").forEach(function (item) {
                                item.classList.remove("bg-light");
                                const newBadge = item.querySelector(".badge.bg-danger.mt-1");
                                if (newBadge) newBadge.remove();
                            });

                            // Ganti isi notif list dengan pesan kosong
                            const notifList = document.querySelector("[data-simplebar]");
                            if (notifList) {
                                notifList.innerHTML = '<div class="p-3 text-muted text-center" style="font-size:13px;"><i class="mdi mdi-bell-off-outline d-block mb-1" style="font-size:24px;opacity:.4;"></i>Semua notifikasi telah dibaca.</div>';
                            }
                        } catch (err) {
                            console.error("Read all notifications failed:", err);
                        }
                    });
                });
            });
            </script>