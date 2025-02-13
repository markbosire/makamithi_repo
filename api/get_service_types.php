<?php
// api/get_service_types.php
header('Content-Type: application/json');
require_once('../db/db_connection.php');

// Get search query and validate
$query = isset($_GET['query']) ? trim($_GET['query']) : '';

// Get the enum values for service_type from the appointments table
$sql = "SELECT DISTINCT service_type 
        FROM appointments 
        WHERE service_type LIKE ? 
        GROUP BY service_type 
        ORDER BY service_type ASC 
        LIMIT 10";

$params = ["%{$query}%"];
$types = "s";

try {
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception($conn->error);
    }

    // Bind parameters
    $stmt->bind_param($types, ...$params);
    
    if (!$stmt->execute()) {
        throw new Exception($stmt->error);
    }

    $result = $stmt->get_result();
    $service_types = [];

    while ($row = $result->fetch_assoc()) {
        // Only add non-null service types
        if ($row['service_type']) {
            $service_types[] = [
                'service_type' => htmlspecialchars($row['service_type'])
            ];
        }
    }

    // If no results found in the table or the query is empty, 
    // return the default enum values from the schema
    if (empty($service_types)) {
        $default_types = ['checkup', 'vaccination', 'surgery', 'grooming', 'emergency'];
        $service_types = array_map(function($type) use ($query) {
            // Only include types that match the search query
            if (empty($query) || stripos($type, $query) !== false) {
                return ['service_type' => $type];
            }
        }, $default_types);
        // Remove null values from filtered results
        $service_types = array_filter($service_types);
    }

    echo json_encode(array_values($service_types));

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to fetch service types',
        'message' => $e->getMessage()
    ]);
}

// Close the database connection
$stmt->close();
$conn->close();