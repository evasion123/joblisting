<?php
require_once __DIR__ . '/_require_admin.php';
$data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$user_id = (int)($data['user_id'] ?? 0);
if ($user_id <= 0) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'Invalid user id']); exit; }

$pdo = get_pdo();
// Clean applications first (safe even if FK CASCADE is set)
$pdo->prepare('DELETE FROM applications WHERE user_id=?')->execute([$user_id]);
$pdo->prepare('DELETE FROM users WHERE id=?')->execute([$user_id]);

echo json_encode(['ok'=>true]);
