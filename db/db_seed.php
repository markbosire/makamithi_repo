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
// Add these arrays after creating the Faker instance
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
// Insert users
$user_ids = [];
for ($i = 0; $i < 10; $i++) {
    $username = $faker->userName;
    $password = password_hash('password', PASSWORD_DEFAULT);
    $full_name = $faker->name;
    $role = $faker->randomElement(['admin', 'veterinarian', 'staff']);
    $email = $faker->email;
    $contact = $faker->phoneNumber;

    $sql = "INSERT INTO users (username, password, full_name, role, email, contact) VALUES (?, ?, ?, ?, ?, ?)";
    executePreparedStatement($conn, $sql, [$username, $password, $full_name, $role, $email, $contact]);
    $user_ids[] = $conn->insert_id;
}

// Insert clients
$client_ids = [];
for ($i = 0; $i < 50; $i++) {
    $name = $faker->name;
    $email = $faker->email;
    $contact_primary = $faker->phoneNumber;
    $contact_emergency = $faker->phoneNumber;
    $address = $faker->address;
    $city = $faker->city;
    $postal_code = $faker->postcode;

    $sql = "INSERT INTO clients (name, email, contact_primary, contact_emergency, address, city, postal_code) VALUES (?, ?, ?, ?, ?, ?, ?)";
    executePreparedStatement($conn, $sql, [$name, $email, $contact_primary, $contact_emergency, $address, $city, $postal_code]);
    $client_ids[] = $conn->insert_id;
}

// Insert animals
$animal_ids = [];
for ($i = 0; $i < 100; $i++) {
    $client_id = $faker->randomElement($client_ids);
    $name = $faker->firstName;
    $species = $faker->randomElement(['Dog', 'Cat', 'Bird', 'Rabbit']);
    $breed = $faker->word;
    $dob = $faker->date('Y-m-d');
    $gender = $faker->randomElement(['male', 'female', 'unknown']);
    $weight = $faker->randomFloat(2, 2, 50);
    $microchip = $faker->uuid;

    $// In the animals insertion loop, modify the SQL query to
// Get random keys
$medical_keys = array_rand($medical_conditions, rand(1, 3));
$allergies_keys = array_rand($common_allergies, rand(1, 3));
$notes_keys = array_rand($special_notes, rand(1, 2));

// Convert to array if single key
if (!is_array($medical_keys)) $medical_keys = [$medical_keys];
if (!is_array($allergies_keys)) $allergies_keys = [$allergies_keys];
if (!is_array($notes_keys)) $notes_keys = [$notes_keys];

// Get the actual values using the keys
$selected_medical = [];
$selected_allergies = [];
$selected_notes = [];

foreach ($medical_keys as $key) {
    $selected_medical[] = $medical_conditions[$key];
}

foreach ($allergies_keys as $key) {
    $selected_allergies[] = $common_allergies[$key];
}

foreach ($notes_keys as $key) {
    $selected_notes[] = $special_notes[$key];
}

$medical_history = implode(". ", $selected_medical);
$allergies = implode(", ", $selected_allergies);
$notes = implode(". ", $selected_notes);
$medical_history = implode(". ", $selected_medical);
$allergies = implode(", ", $selected_allergies);
$notes = implode(". ", $selected_notes);

$sql = "INSERT INTO animals (client_id, name, species, breed, date_of_birth, gender, weight, microchip_number, medical_history, allergies, special_notes) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
executePreparedStatement($conn, $sql, [
    $client_id, $name, $species, $breed, $dob, $gender, $weight, $microchip, 
    $medical_history, $allergies, $notes
]);
    $animal_ids[] = $conn->insert_id;
}

// Insert inventory
$inventory_ids = [];
for ($i = 0; $i < 20; $i++) {
    $product_name = $faker->word;
    $category = $faker->randomElement(['medication', 'supplies', 'food', 'accessories']);
    $description = $faker->sentence;
    $price = $faker->randomFloat(2, 5, 500);
    $cost_price = $faker->randomFloat(2, 2, 300);
    $quantity = $faker->numberBetween(10, 100);
    $reorder_level = $faker->numberBetween(5, 20);
    $supplier = $faker->company;
    $expiry_date = $faker->date('Y-m-d');

    $sql = "INSERT INTO inventory (product_name, category, description, price, cost_price, quantity, reorder_level, supplier, expiry_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    executePreparedStatement($conn, $sql, [$product_name, $category, $description, $price, $cost_price, $quantity, $reorder_level, $supplier, $expiry_date]);
    $inventory_ids[] = $conn->insert_id;
}

// Insert sales
for ($i = 0; $i < 50; $i++) {
    $client_id = $faker->randomElement($client_ids);
    $product_id = $faker->randomElement($inventory_ids);
    $quantity = $faker->numberBetween(1, 5);
    $unit_price = $faker->randomFloat(2, 10, 200);
    $discount = $faker->randomFloat(2, 0, 20);
    $total = ($unit_price * $quantity) - $discount;
    $payment_method = $faker->randomElement(['cash', 'card', 'mobile_money']);
    $payment_status = $faker->randomElement(['pending', 'completed', 'cancelled']);

    $sql = "INSERT INTO sales (client_id, product_id, quantity, unit_price, discount, total, payment_method, payment_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    executePreparedStatement($conn, $sql, [$client_id, $product_id, $quantity, $unit_price, $discount, $total, $payment_method, $payment_status]);
}

// Insert appointments
for ($i = 0; $i < 30; $i++) {
    $client_id = $faker->randomElement($client_ids);
    $animal_id = $faker->randomElement($animal_ids);
    $vet_id = $faker->randomElement($user_ids);
    $date = $faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d H:i:s');
    $service_type = $faker->randomElement(['checkup', 'vaccination', 'surgery', 'grooming', 'emergency']);
    $description = $faker->sentence;
    $status = $faker->randomElement(['scheduled', 'confirmed', 'completed', 'cancelled', 'no_show']);
    $duration = $faker->numberBetween(15, 120); // Duration in minutes
    $fee = $faker->randomFloat(2, 20, 300);
    $payment_status = $faker->randomElement(['pending', 'completed', 'cancelled']);
    $notes = $faker->optional(0.7)->sentence() ?: null;

    $sql = "INSERT INTO appointments (client_id, animal_id, veterinarian_id, appointment_date, service_type, description, status, duration, fee, payment_status, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    executePreparedStatement($conn, $sql, [$client_id, $animal_id, $vet_id, $date, $service_type, $description, $status, $duration, $fee, $payment_status, $notes]);
}
$vet_ids = [];
$result = $conn->query("SELECT id FROM users WHERE role = 'veterinarian'");
while ($row = $result->fetch_assoc()) {
    $vet_ids[] = $row['id'];
}

// Ensure there are available vets
if (empty($vet_ids)) {
    die('No veterinarians found in the database. Please add some before seeding.');
}

// Insert medical records
for ($i = 0; $i < 20; $i++) {
    $animal_id = $faker->randomElement($animal_ids);
    $vet_id = $faker->randomElement($vet_ids); // Assign vet_id from vet_ids array
    $visit_date = $faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d H:i:s');
    $diagnosis = $faker->sentence;
    $treatment = $faker->sentence;
    $prescription = $faker->sentence;

    $sql = "INSERT INTO medical_records (animal_id, veterinarian_id, visit_date, diagnosis, treatment, prescription) 
            VALUES (?, ?, ?, ?, ?, ?)";
    executePreparedStatement($conn, $sql, [$animal_id, $vet_id, $visit_date, $diagnosis, $treatment, $prescription]);
}

// Insert vaccinations
$vaccine_types = [
    'Dog' => ['Rabies', 'Distemper', 'Parvovirus', 'Bordetella', 'Leptospirosis'],
    'Cat' => ['Rabies', 'FVRCP', 'FeLV', 'FIV'],
    'Rabbit' => ['Myxomatosis', 'RVHD', 'Pasteurella'],
    'Bird' => ['Polyomavirus', 'Pacheco Disease', 'Avian Pox']
];

for ($i = 0; $i < 150; $i++) {
    $animal_id = $faker->randomElement($animal_ids);

    // Get the animal's species
    $species_query = "SELECT species FROM animals WHERE id = ?";
    $stmt = $conn->prepare($species_query);
    $stmt->bind_param('i', $animal_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $species = $result->fetch_assoc()['species'];
    $stmt->close();

    $vaccine_name = $faker->randomElement($vaccine_types[$species]);
    $date_given = $faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d');
    $next_due_date = date('Y-m-d', strtotime($date_given . ' + 1 year'));
    $administered_by = $faker->randomElement($vet_ids); // Assign vet_id from vet_ids array
    $batch_number = $faker->bothify('VAX-####-????');
    $notes = $faker->optional(0.7)->sentence() ?: null;

    $sql = "INSERT INTO vaccinations (animal_id, vaccine_name, date_given, next_due_date, administered_by, batch_number, notes) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    executePreparedStatement($conn, $sql, [$animal_id, $vaccine_name, $date_given, $next_due_date, $administered_by, $batch_number, $notes]);
}
$conn->close();
echo "Database seeding completed successfully! All tables have been populated with sample data.";
?>
