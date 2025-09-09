<?php
require_once __DIR__ . '/_require_admin.php';
$pdo = get_pdo();
$q = trim($_GET['q'] ?? '');

$sql = 'SELECT u.id, u.name, u.email, u.created_at,
               (SELECT COUNT(*) FROM applications a WHERE a.user_id=u.id) AS applications_count
        FROM users u WHERE 1=1';
$params = [];
if ($q !== '') {
  $sql .= ' AND (u.name LIKE :q OR u.email LIKE :q)';
  $params[':q'] = "%$q%";
}
$sql .= ' ORDER BY u.created_at DESC, u.id DESC';
$st = $pdo->prepare($sql);
$st->execute($params);
echo json_encode(['ok'=>true,'users'=>$st->fetchAll()], JSON_UNESCAPED_UNICODE);
