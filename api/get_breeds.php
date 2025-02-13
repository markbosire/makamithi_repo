<?php
// api/get_breeds.php
header('Content-Type: application/json');
require_once('../db/db_connection.php');

// Get search query and validate
$query = isset($_GET['query']) ? trim($_GET['query']) : '';

// Get species filter if provided
$species = isset($_GET['species']) ? trim($_GET['species']) : '';

// Prepare the base SQL query to fetch distinct breeds
$sql = "SELECT DISTINCT breed FROM animals WHERE breed LIKE ?";
$params = ["%{$query}%"];
$types = "s";

// Add species filter if provided
if (!empty($species)) {
    $sql .= " AND species = ?";
    $params[] = $species;
    $types .= "s";
}

// Add ordering and limit
$sql .= " ORDER BY breed ASC LIMIT 10";

try {
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception($conn->error);
    }

    // Bind parameters dynamically
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    if (!$stmt->execute()) {
        throw new Exception($stmt->error);
    }

    $result = $stmt->get_result();
    $breeds = [];

    while ($row = $result->fetch_assoc()) {
        $breeds[] = [
            'breed' => htmlspecialchars($row['breed'])
        ];
    }

    echo json_encode($breeds);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to fetch breeds',
        'message' => $e->getMessage()
    ]);
}

// Close the database connection
$stmt->close();
$conn->close();