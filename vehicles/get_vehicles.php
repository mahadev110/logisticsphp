<?php
// vehicles/get_vehicles.php
require_once '../config/db.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            // Get specific vehicle with assigned driver
            $id = $_GET['id'];
            $query = "SELECT v.*, d.name as driver_name, d.phone as driver_phone 
                      FROM vehicles v 
                      LEFT JOIN drivers d ON v.id = d.vehicle_id 
                      WHERE v.id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $vehicle = $stmt->fetch(PDO::FETCH_ASSOC);
                sendResponse(['success' => true, 'data' => $vehicle]);
            } else {
                sendResponse(['success' => false, 'message' => 'Vehicle not found'], 404);
            }
        } else {
            // Get all vehicles with filtering
            $status_filter = isset($_GET['status']) ? $_GET['status'] : null;
            $available_only = isset($_GET['available']) && $_GET['available'] === 'true';
            
            $where_conditions = [];
            $params = [];
            
            if ($status_filter) {
                $where_conditions[] = "v.status = :status";
                $params[':status'] = $status_filter;
            }
            
            if ($available_only) {
                $where_conditions[] = "v.status = 'available'";
            }
            
            $where_clause = !empty($where_conditions) ? "WHERE " . implode(' AND ', $where_conditions) : "";
            
            $query = "SELECT v.*, d.name as driver_name, d.phone as driver_phone 
                      FROM vehicles v 
                      LEFT JOIN drivers d ON v.id = d.vehicle_id 
                      $where_clause 
                      ORDER BY v.vehicle_number";
            
            $stmt = $db->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            
            $vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);
            sendResponse(['success' => true, 'data' => $vehicles]);
        }
        break;
        
    case 'POST':
        // Create new vehicle
        $data = getRequestData();
        $required_fields = ['vehicle_number', 'vehicle_type'];
        $missing_fields = validateInput($data, $required_fields);
        
        if (!empty($missing_fields)) {
            sendResponse(['success' => false, 'message' => 'Missing required fields: ' . implode(', ', $missing_fields)], 400);
        }
        
        $query = "INSERT INTO vehicles (vehicle_number, vehicle_type, capacity, current_location, status) 
                  VALUES (:vehicle_number, :vehicle_type, :capacity, :current_location, :status)";
        
        $stmt = $db->prepare($query);
        $stmt->bindValue(':vehicle_number', $data['vehicle_number']);
        $stmt->bindValue(':vehicle_type', $data['vehicle_type']);
        $stmt->bindValue(':capacity', $data['capacity'] ?? null);
        $stmt->bindValue(':current_location', $data['current_location'] ?? null);
        $stmt->bindValue(':status', $data['status'] ?? 'available');
        
        if ($stmt->execute()) {
            $vehicle_id = $db->lastInsertId();
            sendResponse([
                'success' => true, 
                'message' => 'Vehicle created successfully',
                'data' => ['id' => $vehicle_id]
            ], 201);
        } else {
            sendResponse(['success' => false, 'message' => 'Failed to create vehicle'], 500);
        }
        break;
        
    default:
        sendResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}
?>