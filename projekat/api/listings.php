<?php
require_once __DIR__ . '/../init.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = get_pdo();
    $stmt = $pdo->query('
        SELECT j.id, j.title, j.company_name, j.city, j.description,
               c.name AS category
        FROM jobs j
        LEFT JOIN categories c ON c.id = j.category_id
        WHERE j.is_active = 1
        ORDER BY j.created_at DESC, j.id DESC
    ');
    $rows = $stmt->fetchAll();
    echo json_encode(['ok' => true, 'jobs' => $rows], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Server error']);
}
?>
