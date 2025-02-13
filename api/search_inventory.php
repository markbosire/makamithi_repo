<?php
// api/search_inventory.php
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
$sql = "SELECT id, product_name, category, description, price, quantity
        FROM inventory
        WHERE 
            product_name LIKE ? OR
            category LIKE ? OR
            description LIKE ? OR
            supplier LIKE ?
        ORDER BY product_name ASC
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
    $items = [];

    while ($row = $result->fetch_assoc()) {
        $items[] = [
            'id' => $row['id'],
            'product_name' => htmlspecialchars($row['product_name']),
            'category' => htmlspecialchars($row['category']),
            'description' => htmlspecialchars($row['description']),
            'price' => $row['price'],
            'quantity' => $row['quantity']
        ];
    }

    echo json_encode($items);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to search inventory',
        'message' => $e->getMessage()
    ]);
}

// Close the database connection
$stmt->close();
$conn->close();