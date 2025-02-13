<?php
// Enable CORS if needed
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");


// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

// Get client_id from URL parameter
$client_id = isset($_GET['client_id']) ? intval($_GET['client_id']) : 0;

if (!$client_id) {
    http_response_code(400);
    die(json_encode(["error" => "Client ID is required"]));
}

// Prepare and execute query to get animals for the specified client
$query = "SELECT id, name, species, breed, gender 
          FROM animals 
          WHERE client_id = ?
          ORDER BY name ASC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $client_id);
$stmt->execute();
$result = $stmt->get_result();

// Fetch all animals and convert to array
$animals = [];
while ($row = $result->fetch_assoc()) {
    $animals[] = [
        'id' => $row['id'],
        'name' => $row['name'],
        'species' => $row['species'],
        'breed' => $row['breed'],
        'gender' => $row['gender']
    ];
}

// Close connection
$stmt->close();
$conn->close();

// Return JSON response
echo json_encode($animals);
?>