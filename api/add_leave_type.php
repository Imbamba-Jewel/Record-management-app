<?php
require_once 'db.php';

// connect to the database table leave_types
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_SESSION['user_role'] ?? '') !== 'admin') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Administrator access required']);
        exit;
    }

    $data = json_decode(file_get_contents('php://input'), true);
    $leaveType = trim($data['leaveType'] ?? '');
    $description = trim($data['description'] ?? '');

    if (!$leaveType || !$description) {
        echo json_encode(['success' => false, 'message' => 'Missing fields']);
        exit;
    }

    // Check if the leave type already exists
    $check = $conn->prepare("SELECT id FROM leave_types WHERE leave_type=?");
    $check->bind_param("s", $leaveType);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Leave type already exists']);
        exit;
    }

    // Insert the new leave type into the database
    $stmt = $conn->prepare("INSERT INTO leave_types (leave_type, description) VALUES (?, ?)");
    $stmt->bind_param('ss', $leaveType, $description);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'id' => $conn->insert_id]);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
