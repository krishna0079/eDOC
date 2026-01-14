<?php
require_once '../config.php';

header('Access-Control-Allow-Origin: *');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'doctor') {
    sendJSON(['success' => false, 'message' => 'Unauthorized'], 401);
}

$conn = getDBConnection();

// Get doctor's profile
$stmt = $conn->prepare("SELECT * FROM doctors WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$doctor = $stmt->get_result()->fetch_assoc();

if (!$doctor) {
    sendJSON(['success' => false, 'message' => 'Doctor profile not found'], 404);
}

// Get appointments
$stmt = $conn->prepare("
    SELECT 
        a.*,
        p.name as patient_name,
        p.phone as patient_phone,
        s.title as session_title,
        s.scheduled_date,
        s.scheduled_time
    FROM appointments a
    JOIN patients p ON a.patient_id = p.id
    JOIN sessions s ON a.session_id = s.id
    WHERE s.doctor_id = ?
    ORDER BY s.scheduled_date DESC, s.scheduled_time DESC
");
$stmt->bind_param("i", $doctor['id']);
$stmt->execute();
$result = $stmt->get_result();

$appointments = [];
while ($row = $result->fetch_assoc()) {
    $appointments[] = $row;
}

$conn->close();

sendJSON([
    'success' => true,
    'appointments' => $appointments
]);
?>
