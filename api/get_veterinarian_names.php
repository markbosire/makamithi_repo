<?php
// api/get_veterinarian_names.php
header('Content-Type: application/json');
require_once('../db/db_connection.php');

// Get search query and validate
$query = isset($_GET['query']) ? trim($_GET['query']) : '';

// Prepare the SQL query to fetch veterinarian names
// Only select users with role = 'veterinarian'
$sql = "SELECT DISTINCT full_name FROM users WHERE full_name LIKE ? ORDER BY full_name ASC LIMIT 10";
$params = ["%{$query}%"];
$types = "s";

try {
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception($conn->error);
    }

    // Bind parameters
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    if (!$stmt->execute()) {
        throw new Exception($stmt->error);
    }

    $result = $stmt->get_result();
    $veterinarians = [];

    while ($row = $result->fetch_assoc()) {
        $veterinarians[] = [
            'full_name' => htmlspecialchars($row['full_name'])
        ];
    }

    echo json_encode($veterinarians);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to fetch veterinarian names',
        'message' => $e->getMessage()
    ]);
}

// Close the database connection
$stmt->close();
$conn->close();