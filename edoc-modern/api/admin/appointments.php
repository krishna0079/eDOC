<?php
require_once '../config.php';

header('Access-Control-Allow-Origin: *');

$conn = getDBConnection();

// Get all appointments with patient and session details
$result = $conn->query("
    SELECT 
        a.*,
        p.name as patient_name,
        p.phone as patient_phone,
        s.title as session_title,
        s.scheduled_date,
        s.scheduled_time,
        d.name as doctor_name,
        d.specialty
    FROM appointments a
    JOIN patients p ON a.patient_id = p.id
    JOIN sessions s ON a.session_id = s.id
    JOIN doctors d ON s.doctor_id = d.id
    ORDER BY s.scheduled_date DESC, s.scheduled_time DESC
");

$appointments = [];
while ($row = $result->fetch_assoc()) {
    $appointments[] = $row;
}

$conn->close();

sendJSON(['success' => true, 'appointments' => $appointments]);
?>
