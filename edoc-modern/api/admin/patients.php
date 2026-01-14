<?php
require_once '../config.php';

header('Access-Control-Allow-Origin: *');

$conn = getDBConnection();

// Get all patients
$result = $conn->query("
    SELECT p.*, u.email 
    FROM patients p 
    JOIN users u ON p.user_id = u.id 
    ORDER BY p.created_at DESC
");

$patients = [];
while ($row = $result->fetch_assoc()) {
    $patients[] = $row;
}

$conn->close();

sendJSON(['success' => true, 'patients' => $patients]);
?>
