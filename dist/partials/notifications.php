<?php

function notif_get_unread_count(PDO $pdo, int $userId): int {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM activity_logs WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$userId]);
    return (int)$stmt->fetchColumn();
}

function notif_get_latest(PDO $pdo, int $userId, int $limit = 5): array {
    $stmt = $pdo->prepare("
        SELECT log_id, action, entity, entity_id, title, detail, is_read, created_at
        FROM activity_logs
        WHERE user_id = ?
        ORDER BY log_id DESC
        LIMIT " . (int)$limit
    );
    $stmt->execute([$userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
