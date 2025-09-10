<?php
// lib/ensure_admins.php
function ensure_admins(PDO $pdo) {
  $pdo->exec("
    CREATE TABLE IF NOT EXISTS admins (
      id INT AUTO_INCREMENT PRIMARY KEY,
      name VARCHAR(120) NOT NULL,
      email VARCHAR(190) NOT NULL UNIQUE,
      password_hash VARCHAR(255) NOT NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
  ");
  $cnt = (int)$pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn();
  if ($cnt === 0) {
    $name = 'Site Admin';
    $email = 'admin@example.com';
    $password_hash = password_hash('change_me_123', PASSWORD_DEFAULT);
    $st = $pdo->prepare("INSERT INTO admins (name, email, password_hash) VALUES (?, ?, ?)");
    $st->execute([$name, $email, $password_hash]);
  }
}
