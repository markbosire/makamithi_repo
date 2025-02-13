<?php
// api/search_clients.php
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
            id,
            name,
            email,
            contact_primary,
            city,
            registration_date
        FROM clients
        WHERE 
            name LIKE ? OR
            email LIKE ? OR
            contact_primary LIKE ? OR
            city LIKE ?
        ORDER BY registration_date DESC
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
        // Format the registration date
        $formattedDate = date('M d, Y', strtotime($row['registration_date']));
        
        // Build a structured result object
        $searchResults[] = [
            'id' => $row['id'],
            'name' => htmlspecialchars($row['name']),
            'email' => htmlspecialchars($row['email']),
            'contact_primary' => htmlspecialchars($row['contact_primary']),
            'city' => htmlspecialchars($row['city']),
            'registration_date' => $formattedDate,
            // Add a formatted display string for the autocomplete dropdown
            'display_text' => sprintf(
                '%s - %s (%s)',
                htmlspecialchars($row['name']),
                htmlspecialchars($row['email']),
                htmlspecialchars($row['contact_primary'])
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