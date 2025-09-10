<?php
require_once __DIR__ . '/_require_admin.php';
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'Invalid id']); exit; }
$st = get_pdo()->prepare('SELECT id, name, email, created_at FROM users WHERE id=? LIMIT 1');
$st->execute([$id]);
$user = $st->fetch();
if (!$user) { http_response_code(404); echo json_encode(['ok'=>false,'error'=>'Not found']); exit; }
echo json_encode(['ok'=>true,'user'=>$user]);
