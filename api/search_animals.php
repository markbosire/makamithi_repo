<?php
// api/search_animals.php
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
            a.name,
            a.species,
            a.breed,
            a.gender,
            a.date_of_birth,
            c.name as owner_name
        FROM animals a
        LEFT JOIN clients c ON a.client_id = c.id
        WHERE 
            a.name LIKE ? OR
            a.species LIKE ? OR
            a.breed LIKE ? OR
            c.name LIKE ?
        ORDER BY a.registration_date DESC
        LIMIT 10";

try {
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception($conn->error);
    }

    // Add wildcards to search term
    $searchTerm = "%{$query}%";
    $stmt->bind_param('ssss', $searchTerm, $searchTerm, $searchTerm, $searchTerm);
    
    if (!$stmt->execute()) {
        throw new Exception($stmt->error);
    }

    $result = $stmt->get_result();
    $searchResults = [];

    while ($row = $result->fetch_assoc()) {
        // Calculate age from date of birth
        $age = '';
        if ($row['date_of_birth']) {
            $dob = new DateTime($row['date_of_birth']);
            $now = new DateTime();
            $interval = $now->diff($dob);
            $age = $interval->y > 0 ? $interval->y . ' years' : $interval->m . ' months';
        }

        // Build a structured result object
        $searchResults[] = [
            'id' => $row['id'],
            'name' => htmlspecialchars($row['name']),
            'species' => htmlspecialchars($row['species']),
            'breed' => htmlspecialchars($row['breed']),
            'gender' => htmlspecialchars($row['gender']),
            'age' => $age,
            'owner_name' => htmlspecialchars($row['owner_name']),
            // Add a formatted display string for the autocomplete dropdown
            'display_text' => sprintf(
                '%s - %s (%s)',
                htmlspecialchars($row['name']),
                htmlspecialchars($row['species']),
                htmlspecialchars($row['breed'])
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