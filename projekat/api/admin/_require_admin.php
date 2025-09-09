<?php
// Place under /api/admin/_require_admin.php
require_once __DIR__ . '/../../init.php';
header('Content-Type: application/json; charset=utf-8');
if (!isset($_SESSION['admin'])) {
  http_response_code(401);
  echo json_encode(['ok' => false, 'error' => 'Admin login required']);
  exit;
}
