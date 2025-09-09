<?php
require_once __DIR__ . '/_require_admin.php';
require_once __DIR__ . '/../../lib/ensure_companies.php';
ensure_companies_table(get_pdo());

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'Invalid id']); exit; }

$st = get_pdo()->prepare('SELECT id, name, email, website, address, about, created_at FROM companies WHERE id=? LIMIT 1');
$st->execute([$id]);
$company = $st->fetch();
if (!$company) { http_response_code(404); echo json_encode(['ok'=>false,'error'=>'Not found']); exit; }

echo json_encode(['ok'=>true,'company'=>$company]);
