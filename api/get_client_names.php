<?php
// api/get_client_names.php
header('Content-Type: application/json');
require_once('../db/db_connection.php');

// Get search query and validate
$query = isset($_GET['query']) ? trim($_GET['query']) : '';

// Prepare SQL query to fetch client names
// We'll include the ID as well in case it's needed for future functionality
$sql = "SELECT id, name, contact_primary FROM clients WHERE name LIKE ? ORDER BY name ASC LIMIT 10";
$params = ["%{$query}%"];
$types = "s";

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
    $clients = [];

    while ($row = $result->fetch_assoc()) {
        $clients[] = [
            'id' => $row['id'],
            'name' => htmlspecialchars($row['name']),
            'contact' => htmlspecialchars($row['contact_primary'])
        ];
    }

    echo json_encode($clients);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to fetch client names',
        'message' => $e->getMessage()
    ]);
}

// Close the database connection
$stmt->close();
$conn->close();