<?php
// api/get_cities.php
header('Content-Type: application/json');
require_once('../db/db_connection.php');

// Get search query and validate
$query = isset($_GET['query']) ? trim($_GET['query']) : '';

// Prepare the SQL query to fetch distinct cities
$sql = "SELECT DISTINCT city FROM clients WHERE city LIKE ? ORDER BY city ASC LIMIT 10";

try {
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception($conn->error);
    }

    // Add wildcards to search term
    $searchTerm = "%{$query}%";
    $stmt->bind_param('s', $searchTerm);
    
    if (!$stmt->execute()) {
        throw new Exception($stmt->error);
    }

    $result = $stmt->get_result();
    $cities = [];

    while ($row = $result->fetch_assoc()) {
        $cities[] = [
            'city' => htmlspecialchars($row['city'])
        ];
    }

    echo json_encode($cities);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to fetch cities',
        'message' => $e->getMessage()
    ]);
}

// Close the database connection
$stmt->close();
$conn->close();