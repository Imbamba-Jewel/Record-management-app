<?php
// api/delete_record.php
require 'db.php';
if (($_SESSION['user_role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Administrator access required']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$id = intval($data['id'] ?? 0);

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Missing ID']);
    exit;
}

$stmt = $conn->prepare("DELETE FROM records WHERE id=?");
$stmt->bind_param('i', $id);

echo json_encode(['success' => $stmt->execute()]);