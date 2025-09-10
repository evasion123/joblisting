<?php
require_once __DIR__ . '/_require_admin.php';

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$category_id = (int)($input['category_id'] ?? 0);

if ($category_id <= 0) {
  http_response_code(400);
  echo json_encode(['ok'=>false,'error'=>'Invalid category id']);
  exit;
}

$pdo = get_pdo();

// Check existence
$st = $pdo->prepare('SELECT id, name FROM categories WHERE id = ? LIMIT 1');
$st->execute([$category_id]);
$cat = $st->fetch();
if (!$cat) {
  http_response_code(404);
  echo json_encode(['ok'=>false,'error'=>'Category not found']);
  exit;
}

// Block delete if in use
$cnt = $pdo->prepare('SELECT COUNT(*) FROM jobs WHERE category_id = ?');
$cnt->execute([$category_id]);
$inUse = (int)$cnt->fetchColumn();

if ($inUse > 0) {
  http_response_code(409);
  echo json_encode(['ok'=>false,'error'=>"Category is used by $inUse job(s). Move or delete those jobs first."]);
  exit;
}

// Delete
$del = $pdo->prepare('DELETE FROM categories WHERE id = ?');
$del->execute([$category_id]);

echo json_encode(['ok'=>true]);
