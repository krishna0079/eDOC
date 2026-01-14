<?php
require_once '../config.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

$method = $_SERVER['REQUEST_METHOD'];
$conn = getDBConnection();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'patient') {
    sendJSON(['success' => false, 'message' => 'Unauthorized'], 401);
}

// Get patient profile
$stmt = $conn->prepare("SELECT * FROM patients WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$patient = $stmt->get_result()->fetch_assoc();

if (!$patient) {
    sendJSON(['success' => false, 'message' => 'Patient profile not found'], 404);
}

if ($method === 'GET') {
    // Get patient's appointments
    $stmt = $conn->prepare("
        SELECT 
            a.*,
            s.title as session_title,
            s.scheduled_date,
            s.scheduled_time,
            d.name as doctor_name,
            d.specialty
        FROM appointments a
        JOIN sessions s ON a.session_id = s.id
        JOIN doctors d ON s.doctor_id = d.id
        WHERE a.patient_id = ?
        ORDER BY s.scheduled_date DESC, s.scheduled_time DESC
    ");
    $stmt->bind_param("i", $patient['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $appointments = [];
    while ($row = $result->fetch_assoc()) {
        $appointments[] = $row;
    }
    
    sendJSON(['success' => true, 'appointments' => $appointments]);
    
} elseif ($method === 'POST') {
    // Book appointment
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['session_id'])) {
        sendJSON(['success' => false, 'message' => 'Session ID required'], 400);
    }
    
    $sessionId = intval($data['session_id']);
    
    // Check session availability
    $stmt = $conn->prepare("
        SELECT * FROM sessions 
        WHERE id = ? 
          AND scheduled_date >= CURDATE()
          AND current_bookings < max_bookings
    ");
    $stmt->bind_param("i", $sessionId);
    $stmt->execute();
    $session = $stmt->get_result()->fetch_assoc();
    
    if (!$session) {
        sendJSON(['success' => false, 'message' => 'Session not available'], 400);
    }
    
    // Create appointment
    $appointmentNumber = $session['current_bookings'] + 1;
    $status = 'confirmed';
    $notes = $data['notes'] ?? null;
    
    $stmt = $conn->prepare("
        INSERT INTO appointments (patient_id, session_id, appointment_number, status, notes) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("iiiss", $patient['id'], $sessionId, $appointmentNumber, $status, $notes);
    $stmt->execute();
    
    // Update session booking count
    $stmt = $conn->prepare("UPDATE sessions SET current_bookings = current_bookings + 1 WHERE id = ?");
    $stmt->bind_param("i", $sessionId);
    $stmt->execute();
    
    sendJSON([
        'success' => true,
        'message' => 'Appointment booked successfully',
        'appointment_id' => $conn->insert_id,
        'appointment_number' => $appointmentNumber
    ]);
    
} elseif ($method === 'DELETE') {
    // Cancel appointment
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['id'])) {
        sendJSON(['success' => false, 'message' => 'Appointment ID required'], 400);
    }
    
    // Get appointment
    $stmt = $conn->prepare("SELECT * FROM appointments WHERE id = ? AND patient_id = ?");
    $stmt->bind_param("ii", $data['id'], $patient['id']);
    $stmt->execute();
    $appointment = $stmt->get_result()->fetch_assoc();
    
    if (!$appointment) {
        sendJSON(['success' => false, 'message' => 'Appointment not found'], 404);
    }
    
    // Update status to cancelled
    $stmt = $conn->prepare("UPDATE appointments SET status = 'cancelled' WHERE id = ?");
    $stmt->bind_param("i", $data['id']);
    $stmt->execute();
    
    // Decrease session booking count
    $stmt = $conn->prepare("UPDATE sessions SET current_bookings = current_bookings - 1 WHERE id = ?");
    $stmt->bind_param("i", $appointment['session_id']);
    $stmt->execute();
    
    sendJSON(['success' => true, 'message' => 'Appointment cancelled successfully']);
}

$conn->close();
?>
