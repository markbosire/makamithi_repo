<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require('../db/db_connection.php');

header('Content-Type: application/json');

try {
    if (!isset($_GET['id'])) {
        throw new Exception('No sale ID provided', 400);
    }

    $sale_id = intval($_GET['id']);

    $sql = "SELECT 
        s.id,
        s.client_id,
        c.name as client_name,
        c.email,
        c.contact_primary as phone,
        s.product_id,
        i.product_name,
        i.category,
        i.description,
        s.quantity,
        s.unit_price,
        s.discount,
        s.total,
        s.payment_method,
        s.payment_status,
        s.sale_date,
        s.notes,
        i.price as original_price
    FROM sales s
    LEFT JOIN clients c ON s.client_id = c.id
    LEFT JOIN inventory i ON s.product_id = i.id
    WHERE s.id = ?";

    if (!$stmt = $conn->prepare($sql)) {
        throw new Exception('SQL preparation failed: ' . $conn->error, 500);
    }

    $stmt->bind_param("i", $sale_id);
    if (!$stmt->execute()) {
        throw new Exception('Query execution failed: ' . $stmt->error, 500);
    }

    $result = $stmt->get_result();
    $sale = $result->fetch_assoc();

    if (!$sale) {
        throw new Exception('Sale not found', 404);
    }

    // Format the data
    $formatted_sale = [
        'client_name' => $sale['client_name'] ?? null,
        'email' => $sale['email'] ?? null,
        'phone' => $sale['phone'] ?? null,
        'product_name' => $sale['product_name'] ?? null,
        'category' => $sale['category'] ?? null,
        'description' => $sale['description'] ?? null,
        'unit_price' => number_format((float)$sale['unit_price'], 2),
        'original_price' => number_format((float)$sale['original_price'], 2),
        'quantity' => (int)$sale['quantity'],
        'discount' => number_format((float)$sale['discount'], 2),
        'total' => number_format((float)$sale['total'], 2),
        'payment_method' => $sale['payment_method'] ?? null,
        'payment_status' => $sale['payment_status'] ?? null,
        'sale_date' => $sale['sale_date'] ?? null,
        'notes' => $sale['notes'] ?? null ,
        'id' => $sale['id'] ?? null
    ];

    echo json_encode($formatted_sale);
} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>