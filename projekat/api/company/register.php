<?php
// api/company/register.php
require_once __DIR__ . '/../../init.php';
require_once __DIR__ . '/../../lib/ensure_companies.php';
header('Content-Type: application/json; charset=utf-8');

ensure_companies_table(get_pdo());

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$password2 = $_POST['password2'] ?? '';
$website = trim($_POST['website'] ?? '');
$address = trim($_POST['address'] ?? '');
$about = trim($_POST['about'] ?? '');

if ($name === '' || $email === '' || $password === '' || $password2 === '') {
  http_response_code(400);
  echo json_encode(['ok' => false, 'error' => 'Required fields: name, email, password']);
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
$st = $pdo->prepare('SELECT id FROM companies WHERE email = ? OR name = ? LIMIT 1');
$st->execute([$email, $name]);
if ($st->fetch()) {
  http_response_code(409);
  echo json_encode(['ok' => false, 'error' => 'Company email or name already registered']);
  exit;
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$ins = $pdo->prepare('INSERT INTO companies (name,email,password_hash,website,address,about) VALUES (?,?,?,?,?,?)');
$ins->execute([$name,$email,$hash,$website,$address,$about]);
$_SESSION['company'] = ['id' => $pdo->lastInsertId(), 'name' => $name, 'email' => $email];
echo json_encode(['ok' => true]);
