<?php
// api/search_medical_records.php
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
            mr.id,
            a.name as animal_name,
            a.species,
            a.breed,
            mr.visit_date,
            mr.diagnosis,
            mr.treatment,
            u.full_name as veterinarian_name,
            c.name as owner_name
        FROM medical_records mr
        INNER JOIN animals a ON mr.animal_id = a.id
        INNER JOIN users u ON mr.veterinarian_id = u.id
        INNER JOIN clients c ON a.client_id = c.id
        WHERE 
            a.name LIKE ? OR
            mr.diagnosis LIKE ? OR
            mr.treatment LIKE ? OR
            c.name LIKE ? OR
            u.full_name LIKE ?
        ORDER BY mr.visit_date DESC
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
        // Format the visit date
        $visitDate = new DateTime($row['visit_date']);
        $formattedDate = $visitDate->format('M d, Y');

        // Build a structured result object
        $searchResults[] = [
            'id' => $row['id'],
            'animal_name' => htmlspecialchars($row['animal_name']),
            'species' => htmlspecialchars($row['species']),
            'breed' => htmlspecialchars($row['breed']),
            'visit_date' => $formattedDate,
            'diagnosis' => htmlspecialchars($row['diagnosis']),
            'treatment' => htmlspecialchars($row['treatment']),
            'veterinarian_name' => htmlspecialchars($row['veterinarian_name']),
            'owner_name' => htmlspecialchars($row['owner_name']),
            // Add a formatted display string for the autocomplete dropdown
            'display_text' => sprintf(
                '%s - %s (%s: %s)',
                htmlspecialchars($row['animal_name']),
                $formattedDate,
                'Diagnosis',
                htmlspecialchars(substr($row['diagnosis'], 0, 50)) . (strlen($row['diagnosis']) > 50 ? '...' : '')
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