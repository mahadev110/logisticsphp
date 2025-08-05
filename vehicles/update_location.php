

<?php
// vehicles/update_location.php
require_once '../config/db.php';

$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    sendResponse(['success' => false, 'message' => 'Only PUT method allowed'], 405);
}

$data = getRequestData();
$required_fields = ['id'];
$missing_fields = validateInput($data, $required_fields);

if (!empty($missing_fields)) {
    sendResponse(['success' => false, 'message' => 'Missing required fields: ' . implode(', ', $missing_fields)], 400);
}

$fields_to_update = [];
$params = [':id' => $data['id']];

if (isset($data['current_location'])) {
    $fields_to_update[] = "current_location = :current_location";
    $params[':current_location'] = $data['current_location'];
}

if (isset($data['latitude']) && isset($data['longitude'])) {
    $fields_to_update[] = "latitude = :latitude, longitude = :longitude";
    $params[':latitude'] = $data['latitude'];
    $params[':longitude'] = $data['longitude'];
}

if (isset($data['status'])) {
    $allowed_statuses = ['available', 'in_use', 'maintenance'];
    if (!in_array($data['status'], $allowed_statuses)) {
        sendResponse(['success' => false, 'message' => 'Invalid status'], 400);
    }
    $fields_to_update[] = "status = :status";
    $params[':status'] = $data['status'];
}

if (empty($fields_to_update)) {
    sendResponse(['success' => false, 'message' => 'No fields to update'], 400);
}

$query = "UPDATE vehicles SET " . implode(', ', $fields_to_update) . " WHERE id = :id";
$stmt = $db->prepare($query);

if ($stmt->execute($params)) {
    if ($stmt->rowCount() > 0) {
        sendResponse(['success' => true, 'message' => 'Vehicle location updated successfully']);
    } else {
        sendResponse(['success' => false, 'message' => 'Vehicle not found'], 404);
    }
} else {
    sendResponse(['success' => false, 'message' => 'Failed to update vehicle location'], 500);
}
?>

<?php
// drivers/get_drivers.php
require_once '../config/db.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            // Get specific driver
            $id = $_GET['id'];
            $query = "SELECT d.*, v.vehicle_number, v.vehicle_type 
                      FROM drivers d 
                      LEFT JOIN vehicles v ON d.vehicle_id = v.id 
                      WHERE d.id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $driver = $stmt->fetch(PDO::FETCH_ASSOC);
                sendResponse(['success' => true, 'data' => $driver]);
            } else {
                sendResponse(['success' => false, 'message' => 'Driver not found'], 404);
            }
        } else {
            // Get all drivers with filtering
            $status_filter = isset($_GET['status']) ? $_GET['status'] : null;
            $available_only = isset($_GET['available']) && $_GET['available'] === 'true';
            
            $where_conditions = [];
            $params = [];
            
            if ($status_filter) {
                $where_conditions[] = "d.status = :status";
                $params[':status'] = $status_filter;
            }
            
            if ($available_only) {
                $where_conditions[] = "d.status = 'available'";
            }
            
            $where_clause = !empty($where_conditions) ? "WHERE " . implode(' AND ', $where_conditions) : "";
            
            $query = "SELECT d.*, v.vehicle_number, v.vehicle_type 
                      FROM drivers d 
                      LEFT JOIN vehicles v ON d.vehicle_id = v.id 
                      $where_clause 
                      ORDER BY d.name";
            
            $stmt = $db->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            
            $drivers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            sendResponse(['success' => true, 'data' => $drivers]);
        }
        break;
        
    case 'POST':
        // Create new driver
        $data = getRequestData();
        $required_fields = ['name', 'license_number', 'phone'];
        $missing_fields = validateInput($data, $required_fields);
        
        if (!empty($missing_fields)) {
            sendResponse(['success' => false, 'message' => 'Missing required fields: ' . implode(', ', $missing_fields)], 400);
        }
        
        $query = "INSERT INTO drivers (name, license_number, phone, email, status) 
                  VALUES (:name, :license_number, :phone, :email, :status)";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':license_number', $data['license_number']);
        $stmt->bindParam(':phone', $data['phone']);
        $stmt->bindParam(':email', $data['email'] ?? null);
        $stmt->bindParam(':status', $data['status'] ?? 'available');
        
        if ($stmt->execute()) {
            $driver_id = $db->lastInsertId();
            sendResponse([
                'success' => true, 
                'message' => 'Driver created successfully',
                'data' => ['id' => $driver_id]
            ], 201);
        } else {
            sendResponse(['success' => false, 'message' => 'Failed to create driver'], 500);
        }
        break;
        
    default:
        sendResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}
?>