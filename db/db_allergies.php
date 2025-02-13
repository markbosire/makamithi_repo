<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'db_connection.php';

// Predefined lists of common conditions and allergies
$medical_conditions = [
    "Had surgery for broken leg in 2023",
    "Chronic ear infections",
    "Diagnosed with diabetes in 2022",
    "Regular dental cleanings required",
    "History of seizures",
    "Recovered from parvo in puppy years",
    "Heart murmur - grade 2",
    "Previous snake bite treatment",
    "Hip dysplasia diagnosed",
    "Ongoing thyroid medication"
];

$common_allergies = [
    "Chicken protein",
    "Beef protein",
    "Dairy products",
    "Wheat gluten",
    "Flea bite sensitivity",
    "Pollen allergies",
    "Certain antibiotics",
    "Grass sensitivity",
    "Dust mites",
    "Corn"
];

$special_notes = [
    "Anxious during thunderstorms",
    "Needs slow introduction to new animals",
    "Prefers female handlers",
    "Must be muzzled during nail trimming",
    "Very food motivated - great for training",
    "Sensitive stomach - special diet only",
    "Escape artist - check gates carefully",
    "Doesn't like having ears touched",
    "Super friendly but jumps on people",
    "Separation anxiety - needs extra attention"
];

// Get all animal IDs
$query = "SELECT id FROM animals";
$result = $conn->query($query);

while ($row = $result->fetch_assoc()) {
    $animal_id = $row['id'];
    
    // Randomly select 1-3 items from each array and combine them
    $selected_medical = array_rand(array_flip($medical_conditions), rand(1, 3));
    $selected_allergies = array_rand(array_flip($common_allergies), rand(1, 3));
    $selected_notes = array_rand(array_flip($special_notes), rand(1, 2));
    
    // Combine into strings
    $medical_history = implode(". ", (array)$selected_medical);
    $allergies = implode(", ", (array)$selected_allergies);
    $notes = implode(". ", (array)$selected_notes);
    
    // Update the animal record
    $update_query = "UPDATE animals SET 
                    medical_history = ?,
                    allergies = ?,
                    special_notes = ?
                    WHERE id = ?";
                    
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("sssi", $medical_history, $allergies, $notes, $animal_id);
    $stmt->execute();
    $stmt->close();
}

$conn->close();
echo "All animals have been updated with medical histories, allergies, and special notes!";
?>