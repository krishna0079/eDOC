<?php
require_once '../config.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

$method = $_SERVER['REQUEST_METHOD'];
$conn = getDBConnection();

if ($method === 'GET') {
    // Get all doctors
    $result = $conn->query("
        SELECT d.*, u.email 
        FROM doctors d 
        JOIN users u ON d.user_id = u.id 
        ORDER BY d.created_at DESC
    ");
    
    $doctors = [];
    while ($row = $result->fetch_assoc()) {
        $doctors[] = $row;
    }
    
    sendJSON(['success' => true, 'doctors' => $doctors]);
    
} elseif ($method === 'POST') {
    // Add new doctor
    $data = json_decode(file_get_contents('php://input'), true);
    
    $missing = validateRequired($data, ['name', 'email', 'password', 'specialty']);
    if (!empty($missing)) {
        sendJSON(['success' => false, 'message' => 'Missing fields: ' . implode(', ', $missing)], 400);
    }
    
    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $data['email']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        sendJSON(['success' => false, 'message' => 'Email already exists'], 409);
    }
    
    // Create user
    $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
    $role = 'doctor';
    $stmt = $conn->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $data['email'], $hashedPassword, $role);
    $stmt->execute();
    $userId = $conn->insert_id;
    
    // Create doctor profile
    $phone = $data['phone'] ?? null;
    $bio = $data['bio'] ?? null;
    $stmt = $conn->prepare("INSERT INTO doctors (user_id, name, specialty, phone, bio) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $userId, $data['name'], $data['specialty'], $phone, $bio);
    $stmt->execute();
    
    sendJSON(['success' => true, 'message' => 'Doctor added successfully', 'doctor_id' => $conn->insert_id]);
    
} elseif ($method === 'PUT') {
    // Update doctor
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['id'])) {
        sendJSON(['success' => false, 'message' => 'Doctor ID required'], 400);
    }
    
    $updates = [];
    $types = '';
    $values = [];
    
    if (isset($data['name'])) {
        $updates[] = 'name = ?';
        $types .= 's';
        $values[] = $data['name'];
    }
    if (isset($data['specialty'])) {
        $updates[] = 'specialty = ?';
        $types .= 's';
        $values[] = $data['specialty'];
    }
    if (isset($data['phone'])) {
        $updates[] = 'phone = ?';
        $types .= 's';
        $values[] = $data['phone'];
    }
    if (isset($data['bio'])) {
        $updates[] = 'bio = ?';
        $types .= 's';
        $values[] = $data['bio'];
    }
    
    if (empty($updates)) {
        sendJSON(['success' => false, 'message' => 'No fields to update'], 400);
    }
    
    $values[] = $data['id'];
    $types .= 'i';
    
    $sql = "UPDATE doctors SET " . implode(', ', $updates) . " WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$values);
    $stmt->execute();
    
    sendJSON(['success' => true, 'message' => 'Doctor updated successfully']);
    
} elseif ($method === 'DELETE') {
    // Delete doctor
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['id'])) {
        sendJSON(['success' => false, 'message' => 'Doctor ID required'], 400);
    }
    
    // Get user_id first
    $stmt = $conn->prepare("SELECT user_id FROM doctors WHERE id = ?");
    $stmt->bind_param("i", $data['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        sendJSON(['success' => false, 'message' => 'Doctor not found'], 404);
    }
    
    $userId = $result->fetch_assoc()['user_id'];
    
    // Delete user (will cascade to doctor)
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    
    sendJSON(['success' => true, 'message' => 'Doctor deleted successfully']);
}

$conn->close();
?>
