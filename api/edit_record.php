<?php
// api/edit_record.php
require 'db.php';
if (($_SESSION['user_role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Administrator access required']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$id = intval($data['id'] ?? 0);
$name = trim($data['name'] ?? '');
$email = trim($data['email'] ?? '');
$department = trim($data['department'] ?? '');
$status = $data['status'] ?? '';
$password = $data['password'] ?? '';

if (!$id || !$name || !$email || !$department) {
    echo json_encode(['success' => false, 'message' => 'Missing fields']);
    exit;
}

$check = $conn->prepare("SELECT id FROM records WHERE name=? AND id!=?");
$check->bind_param('si',$name, $id);
$check->execute();
if ($check->get_result()->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Record with this name already exists']);
    exit;
}
if ($password) {
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE records SET name=?, email=?, department=?, status=?, password=? WHERE id=?");
    $stmt->bind_param('sssssi', $name, $email, $department, $status, $hashed, $id);
} 
else {
    $stmt = $conn->prepare("UPDATE records SET name=?, email=?, department=?, status=? WHERE id=?");
    $stmt->bind_param('ssssi', $name, $email, $department, $status, $id);
}

echo json_encode(['success' => $stmt->execute()]);