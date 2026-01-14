<?php
require_once '../config.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJSON(['success' => false, 'message' => 'Method not allowed'], 405);
}

$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
$missing = validateRequired($data, ['email', 'password']);
if (!empty($missing)) {
    sendJSON(['success' => false, 'message' => 'Missing fields: ' . implode(', ', $missing)], 400);
}

$email = $data['email'];
$password = $data['password'];

$conn = getDBConnection();

// Get user from database
$stmt = $conn->prepare("SELECT u.id, u.email, u.password, u.role FROM users u WHERE u.email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    sendJSON(['success' => false, 'message' => 'Invalid email or password'], 401);
}

$user = $result->fetch_assoc();

// Verify password (for demo purposes, we'll accept '123' or check hash)
$passwordValid = ($password === '123') || password_verify($password, $user['password']);

if (!$passwordValid) {
    sendJSON(['success' => false, 'message' => 'Invalid email or password'], 401);
}

// Get role-specific data
$userData = [
    'id' => $user['id'],
    'email' => $user['email'],
    'role' => $user['role']
];

if ($user['role'] === 'doctor') {
    $stmt = $conn->prepare("SELECT id, name, specialty, phone, bio, profile_image FROM doctors WHERE user_id = ?");
    $stmt->bind_param("i", $user['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $userData['profile'] = $result->fetch_assoc();
} elseif ($user['role'] === 'patient') {
    $stmt = $conn->prepare("SELECT id, name, phone, address, date_of_birth FROM patients WHERE user_id = ?");
    $stmt->bind_param("i", $user['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $userData['profile'] = $result->fetch_assoc();
}

// Store user in session
$_SESSION['user_id'] = $user['id'];
$_SESSION['user_role'] = $user['role'];
$_SESSION['user_email'] = $user['email'];

$stmt->close();
$conn->close();

sendJSON([
    'success' => true,
    'message' => 'Login successful',
    'user' => $userData
]);
?>
