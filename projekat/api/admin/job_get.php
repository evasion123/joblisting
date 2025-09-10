<?php
require_once __DIR__ . '/_require_admin.php';
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'Invalid id']); exit; }

$pdo = get_pdo();
$st = $pdo->prepare('SELECT id, title, company_name, category_id, city, description, is_active FROM jobs WHERE id = ? LIMIT 1');
$st->execute([$id]);
$job = $st->fetch();
if (!$job) { http_response_code(404); echo json_encode(['ok'=>false,'error'=>'Not found']); exit; }

$cats = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
echo json_encode(['ok'=>true, 'job'=>$job, 'categories'=>$cats], JSON_UNESCAPED_UNICODE);
