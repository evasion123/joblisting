<?php
// api/login.php
require_once __DIR__ . '/../init.php';
header('Content-Type: application/json; charset=utf-8');

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
  http_response_code(400);
  echo json_encode(['ok' => false, 'error' => 'Email and password required']);
  exit;
}

$st = get_pdo()->prepare('SELECT id, name, email, password_hash FROM users WHERE email = ? LIMIT 1');
$st->execute([$email]);
$u = $st->fetch();

if ($u && password_verify($password, $u['password_hash'])) {
  $_SESSION['user'] = ['id' => $u['id'], 'name' => $u['name'], 'email' => $u['email']];
  echo json_encode(['ok' => true]);
  exit;
}

http_response_code(401);
echo json_encode(['ok' => false, 'error' => 'Invalid credentials']);
