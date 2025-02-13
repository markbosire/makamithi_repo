<?php
require '../config/env.php';
$servername = 'mysql-makamithi.alwaysdata.net';
$username = 'makamithi';
$password = 'Makamithi@456';
$dbname = 'makamithi_vetcare';
// Create connection to MySQL
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === TRUE) {
    echo "Database '$dbname' created successfully<br>";
} else {
    echo "Error creating database: " . $conn->error;
}

// Select the database
$conn->select_db($dbname);

// Create users table for staff accounts
$create_users_table = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE,
    password VARCHAR(255),
    full_name VARCHAR(100),
    role ENUM('admin', 'veterinarian', 'staff'),
    email VARCHAR(100),
    contact VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

// Enhanced clients table with more fields
$create_clients_table = "CREATE TABLE IF NOT EXISTS clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100),
    contact_primary VARCHAR(50),
    contact_emergency VARCHAR(50),
    address TEXT,
    city VARCHAR(50),
    postal_code VARCHAR(20),
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_visit_date DATETIME,
    notes TEXT
)";

// Enhanced animals table with medical history
$create_animals_table = "CREATE TABLE IF NOT EXISTS animals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT,
    name VARCHAR(100),
    species VARCHAR(50),
    breed VARCHAR(50),
    date_of_birth DATE,
    gender ENUM('male', 'female', 'unknown'),
    weight DECIMAL(5,2),
    microchip_number VARCHAR(50),
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    medical_history TEXT,
    allergies TEXT,
    special_notes TEXT,
    FOREIGN KEY (client_id) REFERENCES clients(id)
)";

// Enhanced inventory table with categories and tracking
$create_inventory_table = "CREATE TABLE IF NOT EXISTS inventory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(100),
    category ENUM('medication', 'supplies', 'food', 'accessories'),
    description TEXT,
    price DECIMAL(10,2),
    cost_price DECIMAL(10,2),
    quantity INT,
    reorder_level INT,
    supplier VARCHAR(100),
    expiry_date DATE,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

// Enhanced sales table with payment tracking
$create_sales_table = "CREATE TABLE IF NOT EXISTS sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT,
    product_id INT,
    quantity INT,
    unit_price DECIMAL(10,2),
    discount DECIMAL(10,2),
    total DECIMAL(10,2),
    payment_method ENUM('cash', 'card', 'mobile_money'),
    payment_status ENUM('pending', 'completed', 'cancelled'),
    sale_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    FOREIGN KEY (client_id) REFERENCES clients(id),
    FOREIGN KEY (product_id) REFERENCES inventory(id)
)";

// Enhanced appointments table with more detail
$create_appointments_table = "CREATE TABLE IF NOT EXISTS appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT,
    animal_id INT,
    veterinarian_id INT,
    appointment_date DATETIME,
    service_type ENUM('checkup', 'vaccination', 'surgery', 'grooming', 'emergency'),
    description TEXT,
    status ENUM('scheduled', 'confirmed', 'completed', 'cancelled', 'no_show'),
    duration INT,
    fee DECIMAL(10,2),
    payment_status ENUM('pending', 'completed', 'cancelled'),
    notes TEXT,
    FOREIGN KEY (client_id) REFERENCES clients(id),
    FOREIGN KEY (animal_id) REFERENCES animals(id),
    FOREIGN KEY (veterinarian_id) REFERENCES users(id)
)";

// Create medical records table
$create_medical_records_table = "CREATE TABLE IF NOT EXISTS medical_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    animal_id INT,
    veterinarian_id INT,
    visit_date DATETIME,
    diagnosis TEXT,
    treatment TEXT,
    prescription TEXT,
    lab_results TEXT,
    next_visit_date DATE,
    notes TEXT,
    FOREIGN KEY (animal_id) REFERENCES animals(id),
    FOREIGN KEY (veterinarian_id) REFERENCES users(id)
)";

// Create vaccinations table
$create_vaccinations_table = "CREATE TABLE IF NOT EXISTS vaccinations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    animal_id INT,
    vaccine_name VARCHAR(100),
    date_given DATE,
    next_due_date DATE,
    administered_by INT,
    batch_number VARCHAR(50),
    notes TEXT,
    FOREIGN KEY (animal_id) REFERENCES animals(id),
    FOREIGN KEY (administered_by) REFERENCES users(id)
)";

// Execute each table creation query
$tables = [
    'users' => $create_users_table,
    'clients' => $create_clients_table,
    'animals' => $create_animals_table,
    'inventory' => $create_inventory_table,
    'sales' => $create_sales_table,
    'appointments' => $create_appointments_table,
    'medical_records' => $create_medical_records_table,
    'vaccinations' => $create_vaccinations_table
];

foreach ($tables as $table_name => $query) {
    if ($conn->query($query) === TRUE) {
        echo "Table '$table_name' created successfully<br>";
    } else {
        echo "Error creating '$table_name' table: " . $conn->error . "<br>";
    }
}

$conn->close();
?>
