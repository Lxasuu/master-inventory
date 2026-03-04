<?php

function log_activity(PDO $pdo, int $userId, string $action, string $entity, ?int $entityId, string $title, $detail = null): void
{
    $json = $detail !== null ? json_encode($detail, JSON_UNESCAPED_UNICODE) : null;

    // kalau kolom log_id auto-increment dan created_at default, ini cukup.
    $stmt = $pdo->prepare("
        INSERT INTO activity_logs (user_id, action, entity, entity_id, title, detail, is_read, created_at)
        VALUES (?, ?, ?, ?, ?, ?, 0, NOW())
    ");
    $stmt->execute([$userId, $action, $entity, $entityId, $title, $json]);
}
