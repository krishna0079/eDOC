<?php
require_once '../config.php';

header('Access-Control-Allow-Origin: *');

session_destroy();

sendJSON([
    'success' => true,
    'message' => 'Logout successful'
]);
?>
