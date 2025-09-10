<?php
require_once __DIR__ . '/_require_admin.php';
$data = json_decode(file_get_contents('php://input'), true) ?: $_POST;

$id = isset($data['id']) ? (int)$data['id'] : 0;
$title = trim($data['title'] ?? '');
$company_name = trim($data['company_name'] ?? '');
$category_id = (int)($data['category_id'] ?? 0);
$city = trim($data['city'] ?? '');
$description = trim($data['description'] ?? '');
$is_active = !empty($data['is_active']) ? 1 : 0;

if ($title==='' || $company_name==='' || $category_id<=0 || $city==='' || $description==='') {
  http_response_code(400);
  echo json_encode(['ok'=>false,'error'=>'Missing required fields']);
  exit;
}

$pdo = get_pdo();
if ($id > 0) {
  $st = $pdo->prepare('UPDATE jobs SET title=?, company_name=?, category_id=?, city=?, description=?, is_active=? WHERE id=?');
  $st->execute([$title, $company_name, $category_id, $city, $description, $is_active, $id]);
} else {
  $st = $pdo->prepare('INSERT INTO jobs (title, company_name, category_id, city, description, is_active) VALUES (?,?,?,?,?,?)');
  $st->execute([$title, $company_name, $category_id, $city, $description, $is_active]);
}
echo json_encode(['ok' => true]);
