<?php
// api/register.php
require_once __DIR__ . '/../init.php';
header('Content-Type: application/json; charset=utf-8');

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$password2 = $_POST['password2'] ?? '';

if ($name === '' || $email === '' || $password === '' || $password2 === '') {
  http_response_code(400);
  echo json_encode(['ok' => false, 'error' => 'All fields are required']);
  exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  http_response_code(400);
  echo json_encode(['ok' => false, 'error' => 'Invalid email']);
  exit;
}

if ($password !== $password2) {
  http_response_code(400);
  echo json_encode(['ok' => false, 'error' => 'Passwords do not match']);
  exit;
}

$pdo = get_pdo();
$st = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
$st->execute([$email]);
if ($st->fetch()) {
  http_response_code(409);
  echo json_encode(['ok' => false, 'error' => 'Email already registered']);
  exit;
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$ins = $pdo->prepare('INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)');
$ins->execute([$name, $email, $hash]);
$_SESSION['user'] = ['id' => $pdo->lastInsertId(), 'name' => $name, 'email' => $email];
echo json_encode(['ok' => true]);
