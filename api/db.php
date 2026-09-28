<?php

header('Content-Type: application/json');
if (!empty($_SERVER['HTTP_ORIGIN'])) {
    header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
    header('Access-Control-Allow-Credentials: true');
}
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

$host = 'localhost';
$username = 'root';
$password = '';
$database = 'employee_mgmt';

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed'
    ]);
    exit;
}

$conn->set_charset('utf8mb4');