<?php
// api/add_record.php
require 'db.php';

$data = json_decode(file_get_contents('php://input'), true);
$name = $data['name'] ?? '';
$email = $data['email'] ?? '';
$department = $data['department'] ?? '';
$status = $data['status'] ?? 'Active';

if (!$name || !$email || !$department) {
    echo json_encode(['success' => false, 'message' => 'Missing fields']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO records (name, email, department, status) VALUES (?, ?, ?, ?)");
$stmt->bind_param('ssss', $name, $email, $department, $status);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'id' => $conn->insert_id]);
} else {
    echo json_encode(['success' => false, 'message' => $conn->error]);
}