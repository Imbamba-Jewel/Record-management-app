<?php
// api/records.php
require 'db.php';

// Fetch records
$role = $_SESSION['user_role'] ?? '';
$userId = (int) ($_SESSION['user_id'] ?? 0);
$records = [];
if ($role === 'employee' && $userId > 0) {
    $stmt = $conn->prepare("SELECT * FROM records WHERE id=?");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $res = $stmt->get_result();
} elseif ($role === 'admin') {
    $res = $conn->query("SELECT * FROM records ORDER BY id");
} else {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'You must log in first']);
    exit;
}
while ($row = $res->fetch_assoc()) {
    unset($row['password']);
    // Attach leave request if any
    $lrStmt = $conn->prepare("SELECT * FROM leave_requests WHERE record_id=? ORDER BY id DESC LIMIT 1");
    $lrStmt->bind_param('i', $row['id']);
    $lrStmt->execute();
    $lr = $lrStmt->get_result();
    if ($lr && $lr->num_rows > 0) {
        $leave = $lr->fetch_assoc();
        $row['leaveRequest'] = [
            'type'        => $leave['leave_type'],
            'startDate'   => $leave['start_date'],
            'endDate'     => $leave['end_date'],
            'reason'      => $leave['reason'],
            'status'      => $leave['status'],
            'adminReason' => $leave['admin_reason']
        ];
    } else {
        $row['leaveRequest'] = null;
    }
    $records[] = $row;
}

echo json_encode(['success' => true, 'records' => $records]);