<?php
// api/get_vaccine_names.php
header('Content-Type: application/json');
require_once('../db/db_connection.php');

// Get search query and validate
$query = isset($_GET['query']) ? trim($_GET['query']) : '';

// Prepare SQL query to fetch distinct vaccine names
$sql = "SELECT DISTINCT vaccine_name FROM vaccinations WHERE vaccine_name LIKE ?";
$params = ["%{$query}%"];
$types = "s";

// Add ordering and limit
$sql .= " ORDER BY vaccine_name ASC LIMIT 10";

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
    $vaccines = [];

    while ($row = $result->fetch_assoc()) {
        $vaccines[] = [
            'vaccine_name' => htmlspecialchars($row['vaccine_name'])
        ];
    }

    echo json_encode($vaccines);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to fetch vaccine names',
        'message' => $e->getMessage()
    ]);
}

// Close the database connection
$stmt->close();
$conn->close();