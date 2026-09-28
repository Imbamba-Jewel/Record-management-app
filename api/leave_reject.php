<?php
// api/leave_reject.php
require 'db.php';
if (($_SESSION['user_role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Administrator access required']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$recordId = intval($data['recordId'] ?? 0);
$reason   = trim($data['reason'] ?? '');

if (!$recordId || !$reason) {
    echo json_encode(['success' => false, 'message' => 'Record ID and reason required']);
    exit;
}

$stmt = $conn->prepare("UPDATE leave_requests SET status='Rejected', admin_reason=? WHERE record_id=? AND status='Pending'");
$stmt->bind_param('si', $reason, $recordId);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'No pending leave found']);
}