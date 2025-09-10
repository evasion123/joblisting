<?php
// api/company/login.php
require_once __DIR__ . '/../../init.php';
require_once __DIR__ . '/../../lib/ensure_companies.php';
header('Content-Type: application/json; charset=utf-8');

ensure_companies_table(get_pdo());

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
  http_response_code(400);
  echo json_encode(['ok' => false, 'error' => 'Email and password required']);
  exit;
}

$st = get_pdo()->prepare('SELECT id, name, email, password_hash FROM companies WHERE email = ? LIMIT 1');
$st->execute([$email]);
$c = $st->fetch();

if ($c && password_verify($password, $c['password_hash'])) {
  $_SESSION['company'] = ['id' => $c['id'], 'name' => $c['name'], 'email' => $c['email']];
  echo json_encode(['ok' => true]);
  exit;
}

http_response_code(401);
echo json_encode(['ok' => false, 'error' => 'Invalid credentials']);
