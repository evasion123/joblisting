<?php
require_once __DIR__ . '/../../init.php';
header('Content-Type: application/json; charset=utf-8');
if (!isset($_SESSION['company'])) {
  http_response_code(401); echo json_encode(['ok'=>false,'error'=>'Login required']); exit;
}
try {
  $st = get_pdo()->prepare('
    SELECT j.id, j.title, j.city, j.description, j.is_active,
           (SELECT COUNT(*) FROM applications a WHERE a.job_id = j.id) AS applicants_count,
           c.name AS category
    FROM jobs j
    LEFT JOIN categories c ON c.id = j.category_id
    WHERE j.company_name = ?
    ORDER BY j.created_at DESC, j.id DESC
  ');
  $st->execute([$_SESSION['company']['name']]);
  echo json_encode(['ok'=>true,'jobs'=>$st->fetchAll()]);
} catch (Throwable $e) {
  http_response_code(500); echo json_encode(['ok'=>false,'error'=>'Server error']);
}
