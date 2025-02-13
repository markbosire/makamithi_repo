<?php
// api/search_sales.php
header('Content-Type: application/json');
require_once('../db/db_connection.php');

// Get search query and validate
$query = isset($_GET['query']) ? trim($_GET['query']) : '';

if (empty($query)) {
    echo json_encode([]);
    exit;
}

// Prepare the search query with multiple conditions
$sql = "SELECT DISTINCT 
            s.id,
            i.product_name,
            c.name as client_name,
            s.sale_date,
            s.total,
            s.payment_status
        FROM sales s
        LEFT JOIN inventory i ON s.product_id = i.id
        LEFT JOIN clients c ON s.client_id = c.id
        WHERE 
            i.product_name LIKE ? OR
            c.name LIKE ? OR
            s.total LIKE ?
        ORDER BY s.sale_date DESC
        LIMIT 10";

try {
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception($conn->error);
    }

    // Add wildcards to search term
    $searchTerm = "%{$query}%";
    $stmt->bind_param('sss', $searchTerm, $searchTerm, $searchTerm);
    
    if (!$stmt->execute()) {
        throw new Exception($stmt->error);
    }

    $result = $stmt->get_result();
    $searchResults = [];

    while ($row = $result->fetch_assoc()) {
        // Format the date
        $formattedDate = date('M d, Y', strtotime($row['sale_date']));
        
        // Format the total
        $formattedTotal = number_format($row['total'], 2);

        // Build a structured result object
        $searchResults[] = [
            'id' => $row['id'],
            'product_name' => htmlspecialchars($row['product_name']),
            'client_name' => htmlspecialchars($row['client_name']),
            'sale_date' => $formattedDate,
            'total' => $formattedTotal,
            'payment_status' => $row['payment_status'],
            // Add a formatted display string for the autocomplete dropdown
            'display_text' => sprintf(
                '%s - %s ($%s)',
                htmlspecialchars($row['product_name']),
                htmlspecialchars($row['client_name']),
                $formattedTotal
            )
        ];
    }

    echo json_encode($searchResults);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Search failed',
        'message' => $e->getMessage()
    ]);
}

// Close the database connection
$stmt->close();
$conn->close();