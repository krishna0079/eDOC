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
$missing = validateRequired($data, ['email', 'password', 'name', 'role']);
if (!empty($missing)) {
    sendJSON(['success' => false, 'message' => 'Missing fields: ' . implode(', ', $missing)], 400);
}

$email = $data['email'];
$password = password_hash($data['password'], PASSWORD_DEFAULT);
$name = $data['name'];
$role = $data['role'];

// Validate role
if (!in_array($role, ['doctor', 'patient'])) {
    sendJSON(['success' => false, 'message' => 'Invalid role. Must be "doctor" or "patient"'], 400);
}

$conn = getDBConnection();

// Check if email already exists
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    sendJSON(['success' => false, 'message' => 'Email already registered'], 409);
}

// Insert user
$stmt = $conn->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, ?)");
if (!$stmt) {
    sendJSON(['success' => false, 'message' => 'Database preparation error: ' . $conn->error], 500);
}

$stmt->bind_param("sss", $email, $password, $role);

if (!$stmt->execute()) {
    sendJSON(['success' => false, 'message' => 'Registration failed: ' . $stmt->error], 500);
}

$userId = $conn->insert_id;

// Insert role-specific data
if ($role === 'doctor') {
    $specialty = isset($data['specialty']) && !empty($data['specialty']) ? $data['specialty'] : 'General Practice';
    $phone = isset($data['phone']) && !empty($data['phone']) ? $data['phone'] : null;
    
    $stmt = $conn->prepare("INSERT INTO doctors (user_id, name, specialty, phone) VALUES (?, ?, ?, ?)");
    if (!$stmt) {
        sendJSON(['success' => false, 'message' => 'Failed to prepare doctor insert: ' . $conn->error], 500);
    }
    $stmt->bind_param("isss", $userId, $name, $specialty, $phone);
    if (!$stmt->execute()) {
        sendJSON(['success' => false, 'message' => 'Failed to create doctor profile: ' . $stmt->error], 500);
    }
} elseif ($role === 'patient') {
    $phone = isset($data['phone']) && !empty($data['phone']) ? $data['phone'] : null;
    $dob = isset($data['date_of_birth']) && !empty($data['date_of_birth']) ? $data['date_of_birth'] : null;
    
    $stmt = $conn->prepare("INSERT INTO patients (user_id, name, phone, date_of_birth) VALUES (?, ?, ?, ?)");
    if (!$stmt) {
        sendJSON(['success' => false, 'message' => 'Failed to prepare patient insert: ' . $conn->error], 500);
    }
    $stmt->bind_param("isss", $userId, $name, $phone, $dob);
    if (!$stmt->execute()) {
        sendJSON(['success' => false, 'message' => 'Failed to create patient profile: ' . $stmt->error], 500);
    }
}

$stmt->close();
$conn->close();

sendJSON([
    'success' => true,
    'message' => 'Registration successful',
    'userId' => $userId
]);
?>
