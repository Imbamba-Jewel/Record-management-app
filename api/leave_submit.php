<?php
// api/leave_submit.php
require 'db.php';

$sessionRole = $_SESSION['user_role'] ?? '';
$sessionUserId = (int) ($_SESSION['user_id'] ?? 0);
if ($sessionRole === '' || $sessionUserId < 1) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'You must log in first']);
    exit;
}

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

if ($sessionRole !== 'admin' && $recordId !== $sessionUserId) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'You can only submit leave for your own record']);
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