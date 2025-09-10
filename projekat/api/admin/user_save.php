<?php
require_once __DIR__ . '/_require_admin.php';
$data = json_decode(file_get_contents('php://input'), true) ?: $_POST;

$id   = (int)($data['id'] ?? 0);
$name = trim($data['name'] ?? '');
$email= trim($data['email'] ?? '');
$newp = (string)($data['new_password'] ?? '');

if ($name==='' || $email==='') {
  http_response_code(400); echo json_encode(['ok'=>false,'error'=>'Name and email are required']); exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  http_response_code(400); echo json_encode(['ok'=>false,'error'=>'Invalid email']); exit;
}

$pdo = get_pdo();

// Uniqueness
$st = $pdo->prepare('SELECT id FROM users WHERE email=?' . ($id>0?' AND id<>?':''));
$st->execute($id>0?[$email,$id]:[$email]);
if ($st->fetch()) { http_response_code(409); echo json_encode(['ok'=>false,'error'=>'Email already in use']); exit; }

if ($id > 0) {
  // Update
  $pdo->prepare('UPDATE users SET name=?, email=? WHERE id=?')->execute([$name,$email,$id]);
  if ($newp !== '') {
    if (strlen($newp) < 6) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'Password must be at least 6 chars']); exit; }
    $hash = password_hash($newp, PASSWORD_DEFAULT);
    $pdo->prepare('UPDATE users SET password_hash=? WHERE id=?')->execute([$hash,$id]);
  }
} else {
  // Create
  if (strlen($newp) < 6) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'Password required (min 6)']); exit; }
  $hash = password_hash($newp, PASSWORD_DEFAULT);
  $pdo->prepare('INSERT INTO users(name,email,password_hash) VALUES (?,?,?)')->execute([$name,$email,$hash]);
}

echo json_encode(['ok'=>true]);
