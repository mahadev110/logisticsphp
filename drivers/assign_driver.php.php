

<?php
// drivers/assign_driver.php
require_once '../config/db.php';

$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    sendResponse(['success' => false, 'message' => 'Only PUT method allowed'], 405);
}

$data = getRequestData();
$required_fields = ['driver_id', 'vehicle_id'];
$missing_fields = validateInput($data, $required_fields);

if (!empty($missing_fields)) {
    sendResponse(['success' => false, 'message' => 'Missing required fields: ' . implode(', ', $missing_fields)], 400);
}

try {
    $db->beginTransaction();
    
    // Check if driver exists and is available
    $driver_check = "SELECT status FROM drivers WHERE id = :driver_id";
    $driver_stmt = $db->prepare($driver_check);
    $driver_stmt->bindParam(':driver_id', $data['driver_id']);
    $driver_stmt->execute();
    $driver = $driver_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$driver) {
        $db->rollback();
        sendResponse(['success' => false, 'message' => 'Driver not found'], 404);
    }
    
    if ($driver['status'] !== 'available') {
        $db->rollback();
        sendResponse(['success' => false, 'message' => 'Driver is not available'], 400);
    }
    
    // Check if vehicle exists and is available
    $vehicle_check = "SELECT status FROM vehicles WHERE id = :vehicle_id";
    $vehicle_stmt = $db->prepare($vehicle_check);
    $vehicle_stmt->bindParam(':vehicle_id', $data['vehicle_id']);
    $vehicle_stmt->execute();
    $vehicle = $vehicle_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$vehicle) {
        $db->rollback();
        sendResponse(['success' => false, 'message' => 'Vehicle not found'], 404);
    }
    
    if ($vehicle['status'] !== 'available') {
        $db->rollback();
        sendResponse(['success' => false, 'message' => 'Vehicle is not available'], 400);
    }
    
    // Unassign vehicle from any other driver
    $unassign_query = "UPDATE drivers SET vehicle_id = NULL, status = 'available' WHERE vehicle_id = :vehicle_id";
    $unassign_stmt = $db->prepare($unassign_query);
    $unassign_stmt->bindParam(':vehicle_id', $data['vehicle_id']);
    $unassign_stmt->execute();
    
    // Assign driver to vehicle
    $assign_driver_query = "UPDATE drivers SET vehicle_id = :vehicle_id, status = 'assigned' WHERE id = :driver_id";
    $assign_driver_stmt = $db->prepare($assign_driver_query);
    $assign_driver_stmt->bindParam(':vehicle_id', $data['vehicle_id']);
    $assign_driver_stmt->bindParam(':driver_id', $data['driver_id']);
    $assign_driver_stmt->execute();
    
    // Update vehicle status
    $assign_vehicle_query = "UPDATE vehicles SET status = 'in_use' WHERE id = :vehicle_id";
    $assign_vehicle_stmt = $db->prepare($assign_vehicle_query);
    $assign_vehicle_stmt->bindParam(':vehicle_id', $data['vehicle_id']);
    $assign_vehicle_stmt->execute();
    
    $db->commit();
    
    sendResponse([
        'success' => true, 
        'message' => 'Driver assigned to vehicle successfully',
        'data' => [
            'driver_id' => $data['driver_id'],
            'vehicle_id' => $data['vehicle_id']
        ]
    ]);
    
} catch (Exception $e) {
    $db->rollback();
    sendResponse(['success' => false, 'message' => 'Failed to assign driver: ' . $e->getMessage()], 500);
}
?>