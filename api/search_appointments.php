<?php
// api/search_appointments.php
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
            a.id,
            c.name as client_name,
            an.name as animal_name,
            an.species,
            an.breed,
            a.appointment_date,
            a.service_type,
            a.status,
            a.duration,
            a.fee,
            u.full_name as veterinarian_name
        FROM appointments a
        INNER JOIN clients c ON a.client_id = c.id
        INNER JOIN animals an ON a.animal_id = an.id
        INNER JOIN users u ON a.veterinarian_id = u.id
        WHERE 
            c.name LIKE ? OR
            an.name LIKE ? OR
            u.full_name LIKE ? OR
            a.service_type LIKE ? OR
            a.status LIKE ?
        ORDER BY a.appointment_date DESC
        LIMIT 10";

try {
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception($conn->error);
    }

    // Add wildcards to search term
    $searchTerm = "%{$query}%";
    $stmt->bind_param('sssss', $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
    
    if (!$stmt->execute()) {
        throw new Exception($stmt->error);
    }

    $result = $stmt->get_result();
    $searchResults = [];

    while ($row = $result->fetch_assoc()) {
        // Format the appointment date
        $appointmentDate = new DateTime($row['appointment_date']);
        $formattedAppointmentDate = $appointmentDate->format('M d, Y h:i A');

        // Build a structured result object
        $searchResults[] = [
            'id' => $row['id'],
            'client_name' => htmlspecialchars($row['client_name']),
            'animal_name' => htmlspecialchars($row['animal_name']),
            'species' => htmlspecialchars($row['species']),
            'breed' => htmlspecialchars($row['breed']),
            'appointment_date' => $formattedAppointmentDate,
            'service_type' => htmlspecialchars($row['service_type']),
            'status' => htmlspecialchars($row['status']),
            'duration' => $row['duration'],
            'fee' => $row['fee'],
            'veterinarian_name' => htmlspecialchars($row['veterinarian_name']),
            // Add a formatted display string for the autocomplete dropdown
            'display_text' => sprintf(
                '%s - %s - %s (%s)',
                htmlspecialchars($row['client_name']),
                htmlspecialchars($row['animal_name']),
                htmlspecialchars($row['service_type']),
                $formattedAppointmentDate
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