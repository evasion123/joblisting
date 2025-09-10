<?php
// api/admin/login.php
require_once __DIR__ . '/../../init.php';       // has get_pdo() + session_start()
require_once __DIR__ . '/../../lib/ensure_admins.php';
header('Content-Type: application/json; charset=utf-8');

ensure_admins(get_pdo()); // make sure table exists / default admin seeded

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
  http_response_code(400);
  echo json_encode(['ok' => false, 'error' => 'Email and password required']);
  exit;
}

$st = get_pdo()->prepare('SELECT id, name, email, password_hash FROM admins WHERE email = ? LIMIT 1');
$st->execute([$email]);
$adm = $st->fetch();

if ($adm && password_verify($password, $adm['password_hash'])) {
  $_SESSION['admin'] = ['id' => $adm['id'], 'name' => $adm['name'], 'email' => $adm['email']];
  echo json_encode(['ok' => true]);
  exit;
}

http_response_code(401);
echo json_encode(['ok' => false, 'error' => 'Invalid credentials']);
