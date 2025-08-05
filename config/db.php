<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

class Database {
    private $host = "localhost";
    private $db_name = "log_db";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo json_encode(array("error" => "Connection error: " . $exception->getMessage()));
        }
        return $this->conn;
    }
}

// Utility functions
function sendResponse($data, $status_code = 200) {
    http_response_code($status_code);
    echo json_encode($data);
    exit;
}

function validateInput($data, $required_fields) {
    $missing_fields = [];
    foreach ($required_fields as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            $missing_fields[] = $field;
        }
    }
    return $missing_fields;
}

function getRequestData() {
    $input = file_get_contents('php://input');
    return json_decode($input, true);
}

// Database Schema Creation (run once)
function createTables($conn) {
    $tables = [
        "shipments" => "
            CREATE TABLE IF NOT EXISTS shipments (
                id INT AUTO_INCREMENT PRIMARY KEY,
                tracking_number VARCHAR(50) UNIQUE NOT NULL,
                origin VARCHAR(255) NOT NULL,
                destination VARCHAR(255) NOT NULL,
                status ENUM('pending', 'in_transit', 'delivered', 'cancelled') DEFAULT 'pending',
                weight DECIMAL(10,2),
                dimensions VARCHAR(100),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )",
        
        "orders" => "
            CREATE TABLE IF NOT EXISTS orders (
                id INT AUTO_INCREMENT PRIMARY KEY,
                order_number VARCHAR(50) UNIQUE NOT NULL,
                customer_name VARCHAR(255) NOT NULL,
                customer_email VARCHAR(255),
                customer_phone VARCHAR(20),
                pickup_address TEXT NOT NULL,
                delivery_address TEXT NOT NULL,
                status ENUM('pending', 'confirmed', 'picked_up', 'in_transit', 'delivered', 'cancelled') DEFAULT 'pending',
                total_amount DECIMAL(10,2),
                shipment_id INT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (shipment_id) REFERENCES shipments(id)
            )",
        
        "vehicles" => "
            CREATE TABLE IF NOT EXISTS vehicles (
                id INT AUTO_INCREMENT PRIMARY KEY,
                vehicle_number VARCHAR(50) UNIQUE NOT NULL,
                vehicle_type VARCHAR(50) NOT NULL,
                capacity DECIMAL(10,2),
                current_location VARCHAR(255),
                latitude DECIMAL(10, 8),
                longitude DECIMAL(11, 8),
                status ENUM('available', 'in_use', 'maintenance') DEFAULT 'available',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )",
        
        "drivers" => "
            CREATE TABLE IF NOT EXISTS drivers (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                license_number VARCHAR(50) UNIQUE NOT NULL,
                phone VARCHAR(20) NOT NULL,
                email VARCHAR(255),
                status ENUM('available', 'assigned', 'off_duty') DEFAULT 'available',
                vehicle_id INT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (vehicle_id) REFERENCES vehicles(id)
            )"
    ];

    foreach ($tables as $table_name => $sql) {
        try {
            $conn->exec($sql);
        } catch(PDOException $e) {
            error_log("Error creating table $table_name: " . $e->getMessage());
        }
    }
}
?>