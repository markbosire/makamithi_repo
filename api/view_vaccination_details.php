<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require('../db/db_connection.php');

header('Content-Type: application/json');

try {
    if (!isset($_GET['id'])) {
        throw new Exception('No vaccination ID provided', 400);
    }

    $vaccination_id = intval($_GET['id']);

    // Main vaccination query with related information
    $sql = "SELECT 
        v.*,
        a.name as animal_name,
        a.species,
        a.breed,
        u.full_name as veterinarian_name,
        c.name as client_name,
        c.contact_primary as client_contact
    FROM vaccinations v
    LEFT JOIN animals a ON v.animal_id = a.id
    LEFT JOIN users u ON v.administered_by = u.id
    LEFT JOIN clients c ON a.client_id = c.id
    WHERE v.id = ?";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('SQL preparation failed: ' . $conn->error, 500);
    }

    $stmt->bind_param("i", $vaccination_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Query execution failed: ' . $stmt->error, 500);
    }

    $result = $stmt->get_result();
    $vaccination = $result->fetch_assoc();

    if (!$vaccination) {
        throw new Exception('Vaccination record not found', 404);
    }

    // Format the response
    $formatted_response = [
        'id' => $vaccination['id'],
        'animal_name' => $vaccination['animal_name'],
        'animal_details' => [
            'species' => $vaccination['species'],
            'breed' => $vaccination['breed']
        ],
        'veterinarian_name' => $vaccination['veterinarian_name'],
        'vaccine_name' => $vaccination['vaccine_name'],
        'date_given' => $vaccination['date_given'],
        'next_due_date' => $vaccination['next_due_date'],
        'batch_number' => $vaccination['batch_number'],
        'notes' => $vaccination['notes'],
        'client_info' => [
            'name' => $vaccination['client_name'],
            'contact' => $vaccination['client_contact']
        ]
    ];

    echo json_encode($formatted_response);

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>