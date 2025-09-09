<?php
require_once __DIR__ . '/_require_admin.php';
require_once __DIR__ . '/../../lib/ensure_companies.php';
ensure_companies_table(get_pdo());

$data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$id   = (int)($data['id'] ?? 0);
$name = trim($data['name'] ?? '');
$email= trim($data['email'] ?? '');
$website = trim($data['website'] ?? '');
$address = trim($data['address'] ?? '');
$about   = trim($data['about'] ?? '');

if ($name==='' || $email==='') { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'Name and email are required']); exit; }
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'Invalid email']); exit; }

$pdo = get_pdo();

// Uniqueness (name + email)
if ($id > 0) {
  $st = $pdo->prepare('SELECT id FROM companies WHERE (email=? OR name=?) AND id<>? LIMIT 1');
  $st->execute([$email,$name,$id]);
} else {
  $st = $pdo->prepare('SELECT id FROM companies WHERE email=? OR name=? LIMIT 1');
  $st->execute([$email,$name]);
}
if ($st->fetch()) { http_response_code(409); echo json_encode(['ok'=>false,'error'=>'Company email or name already exists']); exit; }

if ($id > 0) {
  // Detect name change to migrate jobs.company_name
  $old = $pdo->prepare('SELECT name FROM companies WHERE id=?');
  $old->execute([$id]); $oldName = $old->fetchColumn();

  $pdo->prepare('UPDATE companies SET name=?, email=?, website=?, address=?, about=? WHERE id=?')
      ->execute([$name,$email,$website,$address,$about,$id]);

  if ($oldName && $oldName !== $name) {
    // Keep ownership consistent (string-based)
    $pdo->prepare('UPDATE jobs SET company_name=? WHERE company_name=?')->execute([$name,$oldName]);
  }
} else {
  $pdo->prepare('INSERT INTO companies(name,email,website,address,about,password_hash) VALUES (?,?,?,?,?,?)')
      ->execute([$name,$email,$website,$address,$about,password_hash(bin2hex(random_bytes(6)), PASSWORD_DEFAULT)]);
  // Note: companies.password_hash seeded with a random; not used unless you have company auth.
}

echo json_encode(['ok'=>true]);
