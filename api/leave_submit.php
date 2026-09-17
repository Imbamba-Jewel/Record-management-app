<?php
// api/leave_submit.php
require 'db.php';

$data = json_decode(file_get_contents('php://input'), true);
$recordId  = intval($data['recordId'] ?? 0);
$type      = $data['type'] ?? '';
$startDate = $data['startDate'] ?? '';
$endDate   = $data['endDate'] ?? '';
$reason    = $data['reason'] ?? '';

if (!$recordId || !$type || !$startDate || !$endDate || !$reason) {
    echo json_encode(['success' => false, 'message' => 'Missing fields']);
    exit;
}

// Ensure no existing pending leave
$check = $conn->prepare("SELECT id FROM leave_requests WHERE record_id=? AND status='Pending'");
$check->bind_param('i', $recordId);
$check->execute();
if ($check->get_result()->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'You already have a pending leave request']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO leave_requests (record_id, leave_type, start_date, end_date, reason, status) VALUES (?, ?, ?, ?, ?, 'Pending')");
$stmt->bind_param('issss', $recordId, $type, $startDate, $endDate, $reason);

echo json_encode(['success' => $stmt->execute()]);