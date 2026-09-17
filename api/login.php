<?php
require 'db.php';

$data = json_decode(file_get_contents('php://input'), true);
$username = $data['username'] ?? '';
$password = $data['password'] ?? '';
$role     = $data['role'] ?? '';

if (!$username || !$password || !$role) {
	echo json_encode(['success' => false, 'message' => 'All fields are required']);
	exit;
}

$stmt = $conn->prepare("SELECT id, username, password, role, full_name FROM users WHERE username=? AND role=?");
$stmt->bind_param('ss', $username, $role);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
	http_response_code(401);
	echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
	exit;
}

$user = $result->fetch_assoc();

if ($password !== $user['password']) {
	http_response_code(401);
	echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
	exit;
}

$token = bin2hex(random_bytes(16));

echo json_encode([
	'success' => true,
	'token'   => $token,
	'role'    => $user['role'],
	'name'    => $user['full_name'],
	'userId'  => $user['id']
]);
