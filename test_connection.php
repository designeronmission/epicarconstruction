<?php
header('Content-Type: application/json');
echo json_encode([
    'status' => 'success',
    'message' => 'PHP is working!',
    'timestamp' => date('Y-m-d H:i:s'),
    'php_version' => phpversion(),
    'post_enabled' => isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST'
]);
?>