<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require('../db/db_connection.php');

header('Content-Type: application/json');

try {
    if (!isset($_GET['id'])) {
        throw new Exception('No client ID provided', 400);
    }

    $client_id = intval($_GET['id']);

    // Set the client ID in a cookie for the edit button functionality
    setcookie("currentClientId", $client_id, 0, '/');

    // Main client information query
    $sql = "SELECT 
        c.*,
        COUNT(DISTINCT a.id) as total_pets,
        COUNT(DISTINCT s.id) as total_purchases,
        COUNT(DISTINCT ap.id) as total_appointments,
        (SELECT ap2.appointment_date 
         FROM appointments ap2 
         WHERE ap2.client_id = c.id 
         AND ap2.appointment_date > CURRENT_TIMESTAMP
         ORDER BY ap2.appointment_date ASC 
         LIMIT 1) as next_appointment,
        (SELECT SUM(s2.total) 
         FROM sales s2 
         WHERE s2.client_id = c.id) as lifetime_value
    FROM clients c
    LEFT JOIN animals a ON c.id = a.client_id
    LEFT JOIN sales s ON c.id = s.client_id
    LEFT JOIN appointments ap ON c.id = ap.client_id
    WHERE c.id = ?
    GROUP BY c.id";

    if (!$stmt = $conn->prepare($sql)) {
        throw new Exception('SQL preparation failed: ' . $conn->error, 500);
    }

    $stmt->bind_param("i", $client_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Query execution failed: ' . $stmt->error, 500);
    }

    $result = $stmt->get_result();
    $client = $result->fetch_assoc();

    if (!$client) {
        throw new Exception('Client not found', 404);
    }

    // Get recent pets
    $pets_sql = "SELECT id, name, species, breed, date_of_birth 
                 FROM animals 
                 WHERE client_id = ? 
                 ORDER BY registration_date DESC 
                 LIMIT 5";
    
    $pets_stmt = $conn->prepare($pets_sql);
    $pets_stmt->bind_param("i", $client_id);
    $pets_stmt->execute();
    $pets_result = $pets_stmt->get_result();
    $recent_pets = $pets_result->fetch_all(MYSQLI_ASSOC);

    // Get recent appointments
    $appointments_sql = "SELECT id, appointment_date, service_type, status 
                        FROM appointments 
                        WHERE client_id = ? 
                        ORDER BY appointment_date DESC 
                        LIMIT 5";
    
    $appointments_stmt = $conn->prepare($appointments_sql);
    $appointments_stmt->bind_param("i", $client_id);
    $appointments_stmt->execute();
    $appointments_result = $appointments_stmt->get_result();
    $recent_appointments = $appointments_result->fetch_all(MYSQLI_ASSOC);

    // Get recent purchases
    $purchases_sql = "SELECT s.id, s.sale_date, s.total, i.product_name 
                     FROM sales s
                     JOIN inventory i ON s.product_id = i.id
                     WHERE s.client_id = ? 
                     ORDER BY s.sale_date DESC 
                     LIMIT 5";
    
    $purchases_stmt = $conn->prepare($purchases_sql);
    $purchases_stmt->bind_param("i", $client_id);
    $purchases_stmt->execute();
    $purchases_result = $purchases_stmt->get_result();
    $recent_purchases = $purchases_result->fetch_all(MYSQLI_ASSOC);

    // Format the response
    $formatted_response = [
        'id' => $client['id'],
        'name' => $client['name'],
        'email' => $client['email'],
        'contact_primary' => $client['contact_primary'],
        'contact_emergency' => $client['contact_emergency'],
        'address' => $client['address'],
        'city' => $client['city'],
        'postal_code' => $client['postal_code'],
        'registration_date' => $client['registration_date'],
        'last_visit_date' => $client['last_visit_date'],
        'notes' => $client['notes'],
        'statistics' => [
            'total_pets' => (int)$client['total_pets'],
            'total_purchases' => (int)$client['total_purchases'],
            'total_appointments' => (int)$client['total_appointments'],
            'next_appointment' => $client['next_appointment'],
            'lifetime_value' => number_format((float)$client['lifetime_value'], 2)
        ],
        'recent_pets' => $recent_pets,
        'recent_appointments' => $recent_appointments,
        'recent_purchases' => $recent_purchases
    ];

    echo json_encode($formatted_response);

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>