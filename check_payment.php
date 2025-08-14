<?php
require 'includes/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['nickname'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
    exit();
}

$nickname = trim($_POST['nickname']);

try {
    // Select both payment_status AND access_code
    $stmt = $pdo->prepare("SELECT payment_status, access_code FROM users WHERE nickname = ?");
    $stmt->execute([$nickname]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo json_encode([
            'status' => 'found',
            'payment_status' => $user['payment_status'],
            'access_code' => $user['access_code']
        ]);
    } else {
        echo json_encode(['status' => 'not_found']);
    }
} catch (PDOException $e) {
    error_log("Payment check error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error.']);
}
?>
