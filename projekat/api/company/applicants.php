<?php
require_once __DIR__ . '/../../init.php';
header('Content-Type: application/json; charset=utf-8');
if (!isset($_SESSION['company'])) {
  http_response_code(401); echo json_encode(['ok'=>false,'error'=>'Login required']); exit;
}
$job_id = intval($_GET['job_id'] ?? $_POST['job_id'] ?? 0);
if ($job_id <= 0) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'Invalid job id']); exit; }

$pdo = get_pdo();
// Ensure ownership via company_name
$own = $pdo->prepare('SELECT id FROM jobs WHERE id = ? AND company_name = ?');
$own->execute([$job_id, $_SESSION['company']['name']]);
if (!$own->fetch()) { http_response_code(403); echo json_encode(['ok'=>false,'error'=>'Forbidden']); exit; }

$st = $pdo->prepare('
  SELECT a.id, a.created_at, u.id AS user_id, u.name, u.email
  FROM applications a
  JOIN users u ON u.id = a.user_id
  WHERE a.job_id = ?
  ORDER BY a.created_at DESC
');
$st->execute([$job_id]);
echo json_encode(['ok'=>true,'applicants'=>$st->fetchAll()]);
