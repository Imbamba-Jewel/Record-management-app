<?php
// api/add_record.php
require 'db.php';
if (($_SESSION['user_role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Administrator access required']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$name = trim($data['name'] ?? '');
$email = trim($data['email'] ?? '');
$department = trim($data['department'] ?? '');
$status = $data['status'] ?? 'Active';
$password = $data['password'] ?? '';
if (!$name || !$email || !$department) {
    echo json_encode(['success' => false, 'message' => 'Missing fields']);
    exit;
}
if (!$password){
    echo json_encode(['success'=> false, 'message' => 'Password is required']);
    exit;
}
$check = $conn->prepare("SELECT id FROM records WHERE name=?");
$check->bind_param("s", $name);
$check->execute();
if ($check->get_result()->num_rows >0){
    echo json_encode(['success'=> false, 'message' => 'Record with this name already exists']);
    exit;
}
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare("INSERT INTO records (name, email, department, status, password) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param('sssss', $name, $email, $department, $status, $hashedPassword);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'id' => $conn->insert_id]);
} else {
    echo json_encode(['success' => false, 'message' => $conn->error]);
}