<?php
require_once '../config.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

$method = $_SERVER['REQUEST_METHOD'];
$conn = getDBConnection();

if ($method === 'GET') {
    // Get all sessions
    $result = $conn->query("
        SELECT s.*, d.name as doctor_name, d.specialty 
        FROM sessions s 
        JOIN doctors d ON s.doctor_id = d.id 
        ORDER BY s.scheduled_date DESC, s.scheduled_time DESC
    ");
    
    $sessions = [];
    while ($row = $result->fetch_assoc()) {
        $sessions[] = $row;
    }
    
    sendJSON(['success' => true, 'sessions' => $sessions]);
    
} elseif ($method === 'POST') {
    // Add new session
    $data = json_decode(file_get_contents('php://input'), true);
    
    $missing = validateRequired($data, ['doctor_id', 'title', 'scheduled_date', 'scheduled_time']);
    if (!empty($missing)) {
        sendJSON(['success' => false, 'message' => 'Missing fields: ' . implode(', ', $missing)], 400);
    }
    
    $maxBookings = $data['max_bookings'] ?? 50;
    
    $stmt = $conn->prepare("INSERT INTO sessions (doctor_id, title, scheduled_date, scheduled_time, max_bookings) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isssi", $data['doctor_id'], $data['title'], $data['scheduled_date'], $data['scheduled_time'], $maxBookings);
    $stmt->execute();
    
    sendJSON(['success' => true, 'message' => 'Session created successfully', 'session_id' => $conn->insert_id]);
    
} elseif ($method === 'DELETE') {
    // Delete session
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['id'])) {
        sendJSON(['success' => false, 'message' => 'Session ID required'], 400);
    }
    
    $stmt = $conn->prepare("DELETE FROM sessions WHERE id = ?");
    $stmt->bind_param("i", $data['id']);
    $stmt->execute();
    
    sendJSON(['success' => true, 'message' => 'Session deleted successfully']);
}

$conn->close();
?>
