<?php
require_once __DIR__ . '/_require_admin.php';
require_once __DIR__ . '/../../lib/ensure_companies.php';
ensure_companies_table(get_pdo());

$pdo = get_pdo();
$q = trim($_GET['q'] ?? '');

$sql = 'SELECT c.id, c.name, c.email, c.website, c.address, c.about, c.created_at,
               (SELECT COUNT(*) FROM jobs j WHERE j.company_name=c.name) AS jobs_count
        FROM companies c WHERE 1=1';
$params = [];
if ($q !== '') {
  $sql .= ' AND (c.name LIKE :q OR c.email LIKE :q)';
  $params[':q'] = "%$q%";
}
$sql .= ' ORDER BY c.created_at DESC, c.id DESC';

$st = $pdo->prepare($sql); $st->execute($params);
echo json_encode(['ok'=>true,'companies'=>$st->fetchAll()], JSON_UNESCAPED_UNICODE);
