<?php
require_once __DIR__ . '/_require_admin.php';
$data = json_decode(file_get_contents('php://input'), true) ?: $_POST;

$job_id = (int)($data['job_id'] ?? 0);
if ($job_id <= 0) {
  http_response_code(400);
  echo json_encode(['ok' => false, 'error' => 'Invalid job id']);
  exit;
}

$pdo = get_pdo();
$st = $pdo->prepare('DELETE FROM jobs WHERE id = ?');
$st->execute([$job_id]);

echo json_encode(['ok' => true]);
