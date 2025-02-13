<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require('../db/db_connection.php');

header('Content-Type: application/json');

try {
    if (!isset($_GET['id'])) {
        throw new Exception('No animal ID provided', 400);
    }

    $animal_id = intval($_GET['id']);

    // Main animal information query with statistics
    $sql = "SELECT 
        a.*,
        COUNT(DISTINCT mr.id) as total_visits,
        COUNT(DISTINCT v.id) as total_vaccinations,
        c.name as owner_name,
        c.contact_primary as owner_contact,
        c.email as owner_email,
        c.address as owner_address,
        (SELECT COUNT(*) 
         FROM medical_records mr2 
         WHERE mr2.animal_id = a.id 
         AND mr2.diagnosis LIKE '%surgery%' 
         OR mr2.treatment LIKE '%surgery%') as total_procedures,
        (SELECT mr3.visit_date 
         FROM medical_records mr3 
         WHERE mr3.animal_id = a.id 
         ORDER BY mr3.visit_date DESC 
         LIMIT 1) as last_visit_date,
        (SELECT ap.appointment_date 
         FROM appointments ap 
         WHERE ap.animal_id = a.id 
         AND ap.appointment_date > CURRENT_TIMESTAMP
         AND ap.status NOT IN ('cancelled', 'no_show')
         ORDER BY ap.appointment_date ASC 
         LIMIT 1) as next_appointment
    FROM animals a
    LEFT JOIN medical_records mr ON a.id = mr.animal_id
    LEFT JOIN vaccinations v ON a.id = v.animal_id
    LEFT JOIN clients c ON a.client_id = c.id
    WHERE a.id = ?
    GROUP BY a.id";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('SQL preparation failed: ' . $conn->error, 500);
    }

    $stmt->bind_param("i", $animal_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Query execution failed: ' . $stmt->error, 500);
    }

    $result = $stmt->get_result();
    $animal = $result->fetch_assoc();

    if (!$animal) {
        throw new Exception('Animal not found', 404);
    }

    // Get recent medical records
    $records_sql = "SELECT 
        mr.id,
        mr.visit_date,
        mr.diagnosis,
        mr.treatment,
        u.full_name as veterinarian_name
    FROM medical_records mr
    LEFT JOIN users u ON mr.veterinarian_id = u.id
    WHERE mr.animal_id = ?
    ORDER BY mr.visit_date DESC
    LIMIT 5";
    
    $records_stmt = $conn->prepare($records_sql);
    $records_stmt->bind_param("i", $animal_id);
    $records_stmt->execute();
    $records_result = $records_stmt->get_result();
    $medical_records = $records_result->fetch_all(MYSQLI_ASSOC);

    // Get vaccination history
    $vaccinations_sql = "SELECT 
        v.id,
        v.vaccine_name,
        v.date_given,
        v.next_due_date,
        v.batch_number,
        u.full_name as administered_by_name
    FROM vaccinations v
    LEFT JOIN users u ON v.administered_by = u.id

    WHERE v.animal_id = ?
    ORDER BY v.date_given DESC
    LIMIT 5";
    
    $vaccinations_stmt = $conn->prepare($vaccinations_sql);
    $vaccinations_stmt->bind_param("i", $animal_id);
    $vaccinations_stmt->execute();
    $vaccinations_result = $vaccinations_stmt->get_result();
    $vaccinations = $vaccinations_result->fetch_all(MYSQLI_ASSOC);

    // Format the response
    $formatted_response = [
        'id' => $animal['id'],
        'name' => $animal['name'],
        'species' => $animal['species'],
        'breed' => $animal['breed'],
        'date_of_birth' => $animal['date_of_birth'],
        'gender' => $animal['gender'],
        'weight' => $animal['weight'],
        'microchip_number' => $animal['microchip_number'],
        'registration_date' => $animal['registration_date'],
        'medical_history' => $animal['medical_history'],
        'allergies' => $animal['allergies'],
        'special_notes' => $animal['special_notes'],
        'statistics' => [
            'total_visits' => (int)$animal['total_visits'],
            'total_vaccinations' => (int)$animal['total_vaccinations'],
            'total_procedures' => (int)$animal['total_procedures'],
            'last_visit_date' => $animal['last_visit_date'],
            'next_appointment' => $animal['next_appointment']
        ],
        'medical_records' => $medical_records,
        'vaccinations' => $vaccinations ,
        'owner_info' => [
        'name' => $animal['owner_name'],
        'contact' => $animal['owner_contact'], 
        'email' => $animal['owner_email'],
        'address' => $animal['owner_address']
],
    ];

    echo json_encode($formatted_response);

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>