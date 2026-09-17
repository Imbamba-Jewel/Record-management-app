<?php
// api/edit_record.php
require 'db.php';

$data = json_decode(file_get_contents('php://input'), true);
$id = intval($data['id'] ?? 0);
$name = $data['name'] ?? '';
$email = $data['email'] ?? '';
$department = $data['department'] ?? '';
$status = $data['status'] ?? '';

if (!$id || !$name || !$email || !$department) {
    echo json_encode(['success' => false, 'message' => 'Missing fields']);
    exit;
}

$stmt = $conn->prepare("UPDATE records SET name=?, email=?, department=?, status=? WHERE id=?");
$stmt->bind_param('ssssi', $name, $email, $department, $status, $id);

echo json_encode(['success' => $stmt->execute()]);