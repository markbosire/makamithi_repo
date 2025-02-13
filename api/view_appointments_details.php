<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require('../db/db_connection.php');

header('Content-Type: application/json');

try {
    if (!isset($_GET['id'])) {
        throw new Exception('No appointment ID provided', 400);
    }

    $appointment_id = intval($_GET['id']);

    // Enhanced query with more details
    $sql = "SELECT 
        a.*,
        c.name as client_name,
        c.contact_primary as client_contact,
        c.contact_emergency as client_emergency_contact,
        c.email as client_email,
        c.address as client_address,
        c.city as client_city,
        c.postal_code as client_postal_code,
        an.name as animal_name,
        an.species,
        an.breed,
        an.date_of_birth as animal_dob,
        an.gender as animal_gender,
        an.weight as animal_weight,
        an.microchip_number,
        an.medical_history,
        an.allergies,
        an.special_notes as animal_special_notes,
        u.full_name as veterinarian_name,
        u.email as veterinarian_email,
        u.contact as veterinarian_contact
    FROM appointments a
    LEFT JOIN clients c ON a.client_id = c.id
    LEFT JOIN animals an ON a.animal_id = an.id
    LEFT JOIN users u ON a.veterinarian_id = u.id
    WHERE a.id = ?";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('SQL preparation failed: ' . $conn->error, 500);
    }

    $stmt->bind_param("i", $appointment_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Query execution failed: ' . $stmt->error, 500);
    }

    $result = $stmt->get_result();
    $appointment = $result->fetch_assoc();

    if (!$appointment) {
        throw new Exception('Appointment record not found', 404);
    }

    // Enhanced formatted response
    $formatted_response = [
        'id' => $appointment['id'],
        'client_name' => $appointment['client_name'],
        'animal_name' => $appointment['animal_name'],
        'service_type' => $appointment['service_type'],
        'appointment_date' => $appointment['appointment_date'],
        'veterinarian_name' => $appointment['veterinarian_name'],
        'status' => $appointment['status'],
        'duration' => $appointment['duration'],
        'fee' => $appointment['fee'],
        'payment_status' => $appointment['payment_status'],
        'description' => $appointment['description'],
        'notes' => $appointment['notes'],
        'animal_details' => [
            'species' => $appointment['species'],
            'breed' => $appointment['breed'],
            'date_of_birth' => $appointment['animal_dob'],
            'gender' => $appointment['animal_gender'],
            'weight' => $appointment['animal_weight'],
            'microchip_number' => $appointment['microchip_number'],
            'medical_history' => $appointment['medical_history'],
            'allergies' => $appointment['allergies'],
            'special_notes' => $appointment['animal_special_notes']
        ],
        'client_details' => [
            'email' => $appointment['client_email'],
            'contact_primary' => $appointment['client_contact'],
            'contact_emergency' => $appointment['client_emergency_contact'],
            'address' => $appointment['client_address'],
            'city' => $appointment['client_city'],
            'postal_code' => $appointment['client_postal_code']
        ],
        'veterinarian_details' => [
            'name' => $appointment['veterinarian_name'],
            'email' => $appointment['veterinarian_email'],
            'contact' => $appointment['veterinarian_contact']
        ]
    ];

    echo json_encode($formatted_response);

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>