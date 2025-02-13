<?php
// api/get_animal_names.php
header('Content-Type: application/json');
require_once('../db/db_connection.php');

// Get search query and validate
$query = isset($_GET['query']) ? trim($_GET['query']) : '';

// Prepare the SQL query to fetch animal names
$sql = "SELECT DISTINCT name FROM animals WHERE name LIKE ? ORDER BY name ASC LIMIT 10";
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
    $animals = [];

    while ($row = $result->fetch_assoc()) {
        $animals[] = [
            'name' => htmlspecialchars($row['name'])
        ];
    }

    echo json_encode($animals);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to fetch animal names',
        'message' => $e->getMessage()
    ]);
}

// Close the database connection
$stmt->close();
$conn->close();