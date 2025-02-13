<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require('../db/db_connection.php');

header('Content-Type: application/json');

try {
    if (!isset($_GET['id'])) {
        throw new Exception('No user ID provided', 400);
    }

    $user_id = intval($_GET['id']);

    $sql = "SELECT 
        id,
        username,
        full_name,
        role,
        email,
        contact,
        created_at
    FROM users
    WHERE id = ?";

    if (!$stmt = $conn->prepare($sql)) {
        throw new Exception('SQL preparation failed: ' . $conn->error, 500);
    }

    $stmt->bind_param("i", $user_id);
    if (!$stmt->execute()) {
        throw new Exception('Query execution failed: ' . $stmt->error, 500);
    }

    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        throw new Exception('User not found', 404);
    }

    // Format the data
    $formatted_user = [
        'id' => $user['id'] ?? null,
        'username' => $user['username'] ?? null,
        'full_name' => $user['full_name'] ?? null,
        'role' => $user['role'] ?? null,
        'email' => $user['email'] ?? null,
        'contact' => $user['contact'] ?? null,
        'created_at' => $user['created_at'] ?? null
    ];

    echo json_encode($formatted_user);
} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>