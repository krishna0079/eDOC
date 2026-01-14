<?php
require_once '../config.php';

header('Access-Control-Allow-Origin: *');

$conn = getDBConnection();

// Get statistics
$stats = [];

// Count doctors
$result = $conn->query("SELECT COUNT(*) as count FROM doctors");
$stats['doctors'] = $result->fetch_assoc()['count'];

// Count patients
$result = $conn->query("SELECT COUNT(*) as count FROM patients");
$stats['patients'] = $result->fetch_assoc()['count'];

// Count new bookings (appointments created today)
$result = $conn->query("SELECT COUNT(*) as count FROM appointments WHERE DATE(created_at) = CURDATE()");
$stats['new_bookings'] = $result->fetch_assoc()['count'];

// Count today's sessions
$result = $conn->query("SELECT COUNT(*) as count FROM sessions WHERE scheduled_date = CURDATE()");
$stats['today_sessions'] = $result->fetch_assoc()['count'];

$conn->close();

sendJSON([
    'success' => true,
    'stats' => $stats
]);
?>
