<?php
require_once __DIR__ . '/_require_admin.php';
$pdo = get_pdo();
$categoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
$q = trim($_GET['q'] ?? '');

$sql = "SELECT j.id, j.title, j.company_name, j.city, j.category_id, j.is_active,
               c.name AS category
        FROM jobs j
        LEFT JOIN categories c ON c.id = j.category_id
        WHERE 1=1";
$params = [];
if ($categoryId > 0) { $sql .= " AND j.category_id = :cid"; $params[':cid'] = $categoryId; }
if ($q !== '') {
  $sql .= " AND (j.title LIKE :q OR j.company_name LIKE :q OR j.city LIKE :q)";
  $params[':q'] = "%$q%";
}
$sql .= " ORDER BY j.created_at DESC, j.id DESC";
$st = $pdo->prepare($sql);
$st->execute($params);
echo json_encode(['ok' => true, 'jobs' => $st->fetchAll()], JSON_UNESCAPED_UNICODE);
