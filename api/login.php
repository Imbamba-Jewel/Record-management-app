<?php
require 'db.php';

$data = json_decode(file_get_contents('php://input'), true);
$role = $data['role'] ?? 'employee';
$name = trim($data['name'] ?? '');
$password = $data['password'] ?? '';

if ($role === 'admin') {
	$username = trim($data['username'] ?? '');
	if (!$username || !$password) {
		echo json_encode(['success' => false, 'message' => 'Username and password are required']);
		exit;
	}
	$stmt = $conn->prepare("SELECT id, username, password, role, full_name FROM users WHERE username=? AND role='admin' LIMIT 1");
	$stmt->bind_param('s', $username);
	$stmt->execute();
	$result = $stmt->get_result();
	if ($result->num_rows === 0) {
		http_response_code(401);
		echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
		exit;
	}
	$user = $result->fetch_assoc();
	$passwordMatches = password_verify($password, $user['password']) || hash_equals((string) $user['password'], $password);
	if (!$passwordMatches) {
		http_response_code(401);
		echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
		exit;
	}
	$_SESSION['user_id'] = (int) $user['id'];
	$_SESSION['user_role'] = 'admin';
	echo json_encode([
		'success' => true,
		'token' => bin2hex(random_bytes(16)),
		'role' => 'admin',
		'name' => $user['full_name'],
		'userId' => $user['id']
	]);
	exit;
}

if (!$name || !$password) {
	echo json_encode(['success' => false, 'message' => 'Name and password are required']);
	exit;
}
$stmt = $conn->prepare("SELECT id, name, email, department, status, password FROM records WHERE name=? LIMIT 1");
$stmt->bind_param('s', $name);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
	http_response_code(401);
	echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
	exit;
}

$record = $result->fetch_assoc();
if (empty($record['password']) || !password_verify($password, $record['password'])) {
	http_response_code(401);
	echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
	exit;
}

$token = bin2hex(random_bytes(16));
$isDepartmentAdmin = strcasecmp(trim($record['department']), 'Admin') === 0;
$sessionRole = $isDepartmentAdmin ? 'admin' : 'employee';

$_SESSION['user_id'] = (int) $record['id'];
$_SESSION['user_role'] = $sessionRole;

echo json_encode([
	'success' => true,
	'token'   => $token,
	'role'    => $sessionRole,
	'name'    => $record['name'],
	'userId'  => $record['id'],
	'recordId' => $record['id'],
	'department' => $record['department']
]);