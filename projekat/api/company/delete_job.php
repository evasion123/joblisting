<?php
require_once __DIR__ . '/../../init.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['company'])) {
  http_response_code(401);
  echo json_encode(['ok' => false, 'error' => 'Login required']);
  exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$job_id = intval($input['job_id'] ?? 0);
if ($job_id <= 0) {
  http_response_code(400);
  echo json_encode(['ok' => false, 'error' => 'Invalid job id']);
  exit;
}

$pdo = get_pdo();

/** Ownership check (matches the add-on that ties ownership by company_name) */
$own = $pdo->prepare('SELECT id FROM jobs WHERE id = ? AND company_name = ?');
$own->execute([$job_id, $_SESSION['company']['name']]);


if (!$own->fetch()) {
  http_response_code(403);
  echo json_encode(['ok' => false, 'error' => 'Forbidden']);
  exit;
}

/** Delete the job (applications will cascade if your FK is ON DELETE CASCADE) */
$del = $pdo->prepare('DELETE FROM jobs WHERE id = ?');
$del->execute([$job_id]);

echo json_encode(['ok' => true]);
