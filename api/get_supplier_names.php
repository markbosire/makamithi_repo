<?php
// api/get_supplier_names.php
header('Content-Type: application/json');
require_once('../db/db_connection.php');

// Get search query and validate
$query = isset($_GET['query']) ? trim($_GET['query']) : '';

// Get the unique supplier names from the inventory table
$sql = "SELECT DISTINCT supplier 
        FROM inventory 
        WHERE supplier LIKE ? 
        AND supplier IS NOT NULL AND supplier != ''
        ORDER BY supplier ASC 
        LIMIT 10";

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
    $suppliers = [];

    while ($row = $result->fetch_assoc()) {
        // Only add non-empty supplier names
        if ($row['supplier']) {
            $suppliers[] = [
                'supplier' => htmlspecialchars($row['supplier'])
            ];
        }
    }

    echo json_encode($suppliers);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to fetch supplier names',
        'message' => $e->getMessage()
    ]);
}

// Close the database connection
$stmt->close();
$conn->close();