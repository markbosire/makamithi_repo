<?php
// api/search_users.php
header('Content-Type: application/json');
require_once('../db/db_connection.php');

// Get search query and validate
$query = isset($_GET['query']) ? trim($_GET['query']) : '';

// Check if query is empty
if (empty($query)) {
    echo json_encode([]);
    exit;
}

// Search in multiple fields
$sql = "SELECT id, username, role, full_name, email, contact
        FROM users
        WHERE 
            username LIKE ? OR
            full_name LIKE ? OR
            email LIKE ? OR
            contact LIKE ?
        ORDER BY username ASC
        LIMIT 10";

$search_param = "%{$query}%";
$params = [$search_param, $search_param, $search_param, $search_param];
$types = "ssss";

try {
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception($conn->error);
    }

    // Bind parameters
    $stmt->bind_param($types, ...$params);
    
    if (!$stmt->execute()) {
        throw new Exception($stmt->error);
    }

    $result = $stmt->get_result();
    $users = [];

    while ($row = $result->fetch_assoc()) {
        $users[] = [
            'id' => $row['id'],
            'username' => htmlspecialchars($row['username']),
            'role' => htmlspecialchars($row['role']),
            'full_name' => htmlspecialchars($row['full_name']),
            'email' => htmlspecialchars($row['email']),
            'contact' => htmlspecialchars($row['contact'])
        ];
    }

    echo json_encode($users);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to search users',
        'message' => $e->getMessage()
    ]);
}

// Close the database connection
$stmt->close();
$conn->close();
?>