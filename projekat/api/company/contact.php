<?php
require_once __DIR__ . '/../../init.php';
require_once __DIR__ . '/../../lib/ensure_companies.php';
require_once __DIR__ . '/../../lib/email.php';
ensure_companies_table(get_pdo());
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['company'])) {
  http_response_code(401); echo json_encode(['ok'=>false,'error'=>'Login required']); exit;
}
$data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$job_id = intval($data['job_id'] ?? 0);
$user_id = intval($data['user_id'] ?? 0);
$subject = trim($data['subject'] ?? '');
$message = trim($data['message'] ?? '');

if ($job_id<=0 || $user_id<=0 || $subject==='' || $message==='') {
  http_response_code(400); echo json_encode(['ok'=>false,'error'=>'Missing fields']); exit;
}

$pdo = get_pdo();
$own = $pdo->prepare('SELECT id FROM jobs WHERE id = ? AND company_name = ?');
$own->execute([$job_id, $_SESSION['company']['name']]);
if (!$own->fetch()) { http_response_code(403); echo json_encode(['ok'=>false,'error'=>'Forbidden']); exit; }

$st = $pdo->prepare('SELECT u.email FROM applications a JOIN users u ON u.id = a.user_id WHERE a.job_id = ? AND a.user_id = ?');
$st->execute([$job_id, $user_id]);
$row = $st->fetch();
if (!$row) { http_response_code(404); echo json_encode(['ok'=>false,'error'=>'Applicant not found']); exit; }

$ok = send_email_or_log($row['email'], $subject, $message);
echo json_encode(['ok'=>$ok]);
