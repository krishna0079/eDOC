<?php
require_once '../config.php';

header('Access-Control-Allow-Origin: *');

$conn = getDBConnection();

// Get all doctors with their details
$result = $conn->query("
    SELECT d.*, u.email 
    FROM doctors d 
    JOIN users u ON d.user_id = u.id 
    ORDER BY d.name ASC
");

$doctors = [];
while ($row = $result->fetch_assoc()) {
    $doctors[] = $row;
}

$conn->close();

sendJSON(['success' => true, 'doctors' => $doctors]);
?>
