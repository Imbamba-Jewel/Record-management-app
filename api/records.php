<?php
// api/records.php
require 'db.php';

// Fetch records
$records = [];
$res = $conn->query("SELECT * FROM records ORDER BY id");
while ($row = $res->fetch_assoc()) {
    // Attach leave request if any
    $lr = $conn->query("SELECT * FROM leave_requests WHERE record_id={$row['id']} ORDER BY id DESC LIMIT 1");
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