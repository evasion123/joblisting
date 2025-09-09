<?php
require_once __DIR__ . '/../init.php';
header('Content-Type: application/json; charset=utf-8');

try {
  $st = get_pdo()->query('SELECT id, name FROM categories ORDER BY name');
  echo json_encode(['ok' => true, 'categories' => $st->fetchAll()]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok' => false, 'error' => 'Server error']);
}
