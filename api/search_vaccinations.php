<?php
// api/search_vaccinations.php
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
            v.id,
            a.name as animal_name,
            a.species,
            a.breed,
            v.vaccine_name,
            v.date_given,
            v.next_due_date,
            v.batch_number,
            u.full_name as administered_by_name,
            c.name as owner_name
        FROM vaccinations v
        INNER JOIN animals a ON v.animal_id = a.id
        INNER JOIN users u ON v.administered_by = u.id
        INNER JOIN clients c ON a.client_id = c.id
        WHERE 
            a.name LIKE ? OR
            v.vaccine_name LIKE ? OR
            c.name LIKE ? OR
            u.full_name LIKE ? OR
            v.batch_number LIKE ?
        ORDER BY v.date_given DESC
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
        // Format the dates
        $dateGiven = new DateTime($row['date_given']);
        $formattedDateGiven = $dateGiven->format('M d, Y');
        
        $nextDueDate = new DateTime($row['next_due_date']);
        $formattedNextDueDate = $nextDueDate->format('M d, Y');

        // Build a structured result object
        $searchResults[] = [
            'id' => $row['id'],
            'animal_name' => htmlspecialchars($row['animal_name']),
            'species' => htmlspecialchars($row['species']),
            'breed' => htmlspecialchars($row['breed']),
            'vaccine_name' => htmlspecialchars($row['vaccine_name']),
            'date_given' => $formattedDateGiven,
            'next_due_date' => $formattedNextDueDate,
            'batch_number' => htmlspecialchars($row['batch_number']),
            'administered_by_name' => htmlspecialchars($row['administered_by_name']),
            'owner_name' => htmlspecialchars($row['owner_name']),
            // Add a formatted display string for the autocomplete dropdown
            'display_text' => sprintf(
                '%s - %s (Due: %s)',
                htmlspecialchars($row['animal_name']),
                htmlspecialchars($row['vaccine_name']),
                $formattedNextDueDate
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