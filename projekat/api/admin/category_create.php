<?php
require_once __DIR__ . '/_require_admin.php';

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$name = trim($input['name'] ?? '');

if ($name === '') {
  http_response_code(400);
  echo json_encode(['ok'=>false,'error'=>'Category name required']);
  exit;
}

// Enforce uniqueness (by name)
$pdo = get_pdo();
$st = $pdo->prepare('SELECT id FROM categories WHERE name = ? LIMIT 1');
$st->execute([$name]);
if ($st->fetch()) {
  http_response_code(409);
  echo json_encode(['ok'=>false,'error'=>'Category already exists']);
  exit;
}

// Create
$ins = $pdo->prepare('INSERT INTO categories (name) VALUES (?)');
$ins->execute([$name]);
$id = (int)$pdo->lastInsertId();

echo json_encode(['ok'=>true, 'category'=>['id'=>$id, 'name'=>$name]]);
