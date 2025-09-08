<?php
require_once __DIR__ . '/../../init.php';
require_once __DIR__ . '/../../lib/ensure_companies.php';
ensure_companies_table(get_pdo());
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['company'])) {
  http_response_code(401); echo json_encode(['ok'=>false,'error'=>'Login required']); exit;
}
$data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$title = trim($data['title'] ?? '');
$city = trim($data['city'] ?? '');
$category_id = intval($data['category_id'] ?? 0);
$desc = trim($data['description'] ?? '');
$is_active = !empty($data['is_active']) ? 1 : 0;

if ($title==='' || $city==='' || $desc==='' || $category_id<=0) {
  http_response_code(400); echo json_encode(['ok'=>false,'error'=>'Missing required fields']); exit;
}
try {
  $st = get_pdo()->prepare('INSERT INTO jobs (title, company_name, category_id, city, description, is_active) VALUES (?,?,?,?,?,?)');
  $st->execute([$title, $_SESSION['company']['name'], $category_id, $city, $desc, $is_active]);
  echo json_encode(['ok'=>true,'id'=>get_pdo()->lastInsertId()]);
} catch (Throwable $e) {
  http_response_code(500); echo json_encode(['ok'=>false,'error'=>'Server error']);
}
