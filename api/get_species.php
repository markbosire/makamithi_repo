<?php
// api/get_species.php
header('Content-Type: application/json');
require_once('../db/db_connection.php');

// Get search query and validate
$query = isset($_GET['query']) ? trim($_GET['query']) : '';

// Prepare the SQL query to fetch distinct species
$sql = "SELECT DISTINCT species FROM animals WHERE species LIKE ? ORDER BY species ASC LIMIT 10";

try {
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception($conn->error);
    }

    // Bind the search parameter
    $stmt->bind_param('s', $param);
    $param = "%{$query}%";
    
    if (!$stmt->execute()) {
        throw new Exception($stmt->error);
    }

    $result = $stmt->get_result();
    $species = [];

    while ($row = $result->fetch_assoc()) {
        $species[] = [
            'species' => htmlspecialchars($row['species'])
        ];
    }

    echo json_encode($species);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to fetch species',
        'message' => $e->getMessage()
    ]);
}

// Close the database connection
$stmt->close();
$conn->close();