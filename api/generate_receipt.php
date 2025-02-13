<?php
// api/get_sale_details.php
require_once('../db/db_connection.php');

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Sale ID is required']);
    exit;
}

$sale_id = intval($_GET['id']);

$sql = "SELECT s.*, c.name as client_name, i.product_name, i.price as unit_price, i.description
        FROM sales s
        LEFT JOIN clients c ON s.client_id = c.id
        LEFT JOIN inventory i ON s.product_id = i.id
        WHERE s.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $sale_id);
$stmt->execute();
$result = $stmt->get_result();
$sale = $result->fetch_assoc();

if (!$sale) {
    http_response_code(404);
    echo json_encode(['error' => 'Sale not found']);
    exit;
}

echo json_encode($sale);