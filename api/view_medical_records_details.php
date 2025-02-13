<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require('../db/db_connection.php');

header('Content-Type: application/json');

try {
    if (!isset($_GET['id'])) {
        throw new Exception('No medical record ID provided', 400);
    }

    $record_id = intval($_GET['id']);

    // Main medical record query with related information
    $sql = "SELECT 
        mr.*,
        a.name as animal_name,
        a.species,
        a.breed,
        u.full_name as veterinarian_name,
        c.name as client_name,
        c.contact_primary as client_contact
    FROM medical_records mr
    LEFT JOIN animals a ON mr.animal_id = a.id
    LEFT JOIN users u ON mr.veterinarian_id = u.id
    LEFT JOIN clients c ON a.client_id = c.id
    WHERE mr.id = ?";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('SQL preparation failed: ' . $conn->error, 500);
    }

    $stmt->bind_param("i", $record_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Query execution failed: ' . $stmt->error, 500);
    }

    $result = $stmt->get_result();
    $record = $result->fetch_assoc();

    if (!$record) {
        throw new Exception('Medical record not found', 404);
    }

    // Format the response
    $formatted_response = [
        'id' => $record['id'],
        'animal_name' => $record['animal_name'],
        'animal_details' => [
            'species' => $record['species'],
            'breed' => $record['breed']
        ],
        'veterinarian_name' => $record['veterinarian_name'],
        'visit_date' => $record['visit_date'],
        'diagnosis' => $record['diagnosis'],
        'treatment' => $record['treatment'],
        'prescription' => $record['prescription'],
        'lab_results' => $record['lab_results'],
        'next_visit_date' => $record['next_visit_date'],
        'notes' => $record['notes'],
        'client_info' => [
            'name' => $record['client_name'],
            'contact' => $record['client_contact']
        ]
    ];

    echo json_encode($formatted_response);

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>