<?php
require_once __DIR__ . '/../init.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Please login to apply.']);
    exit;
}

$input = $_POST;
if (empty($input)) {
    // Also allow JSON body
    $raw = file_get_contents('php://input');
    $json = json_decode($raw, true);
    if (is_array($json)) $input = $json;
}

$job_id = intval($input['job_id'] ?? 0);
if ($job_id <= 0) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid job id.']);
    exit;
}

try {
    $pdo = get_pdo();
    // Confirm the job exists and is active
    $stmt = $pdo->prepare('SELECT id FROM jobs WHERE id = ? AND is_active = 1');
    $stmt->execute([$job_id]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => 'Job not found.']);
        exit;
    }

    // Insert application; prevent duplicates
    $stmt = $pdo->prepare('INSERT INTO applications (job_id, user_id) VALUES (?, ?)');
    $stmt->execute([$job_id, $_SESSION['user']['id']]);
    echo json_encode(['ok' => true, 'message' => 'Application submitted.']);
} catch (PDOException $e) {
    if ($e->getCode() === '23000') { // duplicate key
        http_response_code(409);
        echo json_encode(['ok' => false, 'error' => 'You already applied to this job.']);
    } else {
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => 'Server error']);
    }
}
?>
