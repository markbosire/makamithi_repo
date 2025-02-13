<?php
// api/view_inventory_details.php
header('Content-Type: application/json');
require_once('../db/db_connection.php');

// Get and validate inventory ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    http_response_code(400);
    echo json_encode([
        'error' => 'Invalid inventory ID',
        'message' => 'A valid inventory ID is required'
    ]);
    exit;
}

// Fetch detailed information for the specific inventory item
$sql = "SELECT 
            id,
            product_name,
            category,
            description,
            price,
            cost_price,
            quantity,
            reorder_level,
            supplier,
            expiry_date,
            last_updated
        FROM inventory
        WHERE id = ?";

try {
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception($conn->error);
    }

    // Bind parameter
    $stmt->bind_param("i", $id);
    
    if (!$stmt->execute()) {
        throw new Exception($stmt->error);
    }

    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode([
            'error' => 'Inventory item not found',
            'message' => 'No inventory item exists with the provided ID'
        ]);
        exit;
    }

    $item = $result->fetch_assoc();
    
    // Format and sanitize the data
    $formatted_item = [
        'id' => $item['id'],
        'product_name' => htmlspecialchars($item['product_name']),
        'category' => htmlspecialchars($item['category']),
        'description' => htmlspecialchars($item['description']),
        'price' => $item['price'],
        'cost_price' => $item['cost_price'],
        'quantity' => $item['quantity'],
        'reorder_level' => $item['reorder_level'],
        'supplier' => htmlspecialchars($item['supplier']),
        'expiry_date' => $item['expiry_date'],
        'last_updated' => $item['last_updated']
    ];

    echo json_encode($formatted_item);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to fetch inventory details',
        'message' => $e->getMessage()
    ]);
}

// Close the database connection
$stmt->close();
$conn->close();