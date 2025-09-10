<?php
require_once __DIR__ . '/_require_admin.php';
require_once __DIR__ . '/../../lib/ensure_companies.php';
ensure_companies_table(get_pdo());

$data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$company_id = (int)($data['company_id'] ?? 0);
$cascade = (int)($data['cascade'] ?? 0); // 0=keep jobs, 1=null out company_name

if ($company_id <= 0) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'Invalid company id']); exit; }

$pdo = get_pdo();
$st = $pdo->prepare('SELECT name FROM companies WHERE id=?');
$st->execute([$company_id]);
$name = $st->fetchColumn();
if (!$name) { http_response_code(404); echo json_encode(['ok'=>false,'error'=>'Not found']); exit; }

if ($cascade === 1) {
  // Detach jobs from this company name
  $pdo->prepare('UPDATE jobs SET company_name=NULL WHERE company_name=?')->execute([$name]);
}

$pdo->prepare('DELETE FROM companies WHERE id=?')->execute([$company_id]);

echo json_encode(['ok'=>true]);
