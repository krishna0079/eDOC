<?php
require_once '../config.php';

header('Access-Control-Allow-Origin: *');

if (!isset($_GET['doctor_id'])) {
    sendJSON(['success' => false, 'message' => 'Doctor ID required'], 400);
}

$conn = getDBConnection();
$doctorId = intval($_GET['doctor_id']);

// Get available sessions for this doctor
$stmt = $conn->prepare("
    SELECT s.*, 
           (s.max_bookings - s.current_bookings) as available_slots
    FROM sessions s 
    WHERE s.doctor_id = ? 
      AND s.scheduled_date >= CURDATE()
      AND s.current_bookings < s.max_bookings
    ORDER BY s.scheduled_date ASC, s.scheduled_time ASC
");
$stmt->bind_param("i", $doctorId);
$stmt->execute();
$result = $stmt->get_result();

$sessions = [];
while ($row = $result->fetch_assoc()) {
    $sessions[] = $row;
}

$conn->close();

sendJSON(['success' => true, 'sessions' => $sessions]);
?>
