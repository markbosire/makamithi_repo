<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../vendor/autoload.php';
include 'db_connection.php';

use Faker\Factory as Faker;

// Initialize Faker
$faker = Faker::create();

// Helper function to prepare and execute a query
function executePreparedStatement($conn, $sql, $params) {
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }
    $types = '';
    foreach ($params as $param) {
        if (is_int($param)) {
            $types .= 'i'; // Integer
        } elseif (is_float($param)) {
            $types .= 'd'; // Double
        } elseif (is_string($param)) {
            $types .= 's'; // String
        } else {
            $types .= 'b'; // Blob or other
        }
    }
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $stmt->close();
}

// Update appointments table
$result = $conn->query("SELECT id FROM appointments WHERE description IS NULL OR duration IS NULL OR notes IS NULL OR payment_status IS NULL");
while ($row = $result->fetch_assoc()) {
    $appointment_id = $row['id'];
    $description = $faker->sentence;
    $duration = $faker->numberBetween(15, 120); // Duration in minutes
    $payment_status = $faker->randomElement(['pending', 'completed', 'cancelled']);
    $notes = $faker->optional(0.7)->sentence() ?: null;

    $sql = "UPDATE appointments SET description = ?, duration = ?, payment_status = ?, notes = ? WHERE id = ?";
    executePreparedStatement($conn, $sql, [$description, $duration, $payment_status, $notes, $appointment_id]);
}

echo "Missing data updated successfully!";
$conn->close();
?>