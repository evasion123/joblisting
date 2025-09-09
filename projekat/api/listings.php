<?php
require_once __DIR__ . '/../init.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = get_pdo();

    $uid = isset($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : 0;
    $categoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

    // When logged in, join applications to mark jobs already applied for
    $applyJoin = '';
    $appliedSelect = '0 AS has_applied';
    $params = [];

    if ($uid > 0) {
        $applyJoin = ' LEFT JOIN applications a ON a.job_id = j.id AND a.user_id = :uid ';
        $appliedSelect = 'CASE WHEN a.id IS NULL THEN 0 ELSE 1 END AS has_applied';
        $params[':uid'] = $uid;
    }

    $sql = "
      SELECT
        j.id, j.title, j.company_name, j.city, j.description,
        c.name AS category,
        {$appliedSelect}
      FROM jobs j
      LEFT JOIN categories c ON c.id = j.category_id
      {$applyJoin}
      WHERE j.is_active = 1
    ";

    if ($categoryId > 0) {
        $sql .= ' AND j.category_id = :cid';
        $params[':cid'] = $categoryId;
    }

    $sql .= ' ORDER BY j.created_at DESC, j.id DESC';

    $st = $pdo->prepare($sql);
    $st->execute($params);

    echo json_encode(['ok' => true, 'jobs' => $st->fetchAll()], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Server error']);
}
