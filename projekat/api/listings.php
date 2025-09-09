<?php
require_once __DIR__ . '/../init.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = get_pdo();
    $categoryId = intval($_GET['category_id'] ?? 0);

    $sql = '
      SELECT j.id, j.title, j.company_name, j.city, j.description,
             c.name AS category
      FROM jobs j
      LEFT JOIN categories c ON c.id = j.category_id
      WHERE j.is_active = 1
    ';

    $params = [];
    if ($categoryId > 0) {
        $sql .= ' AND j.category_id = ?';
        $params[] = $categoryId;
    }

    $sql .= ' ORDER BY j.created_at DESC, j.id DESC';

    if ($params) {
        $st = $pdo->prepare($sql);
        $st->execute($params);
    } else {
        $st = $pdo->query($sql);
    }

    echo json_encode(['ok' => true, 'jobs' => $st->fetchAll()], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Server error']);
}
