<?php
// shipments/update_status.php
require_once '../config/db.php';

$database = new Database();
$db = $database->getConnection();

// Only allow PUT method
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    sendResponse(['success' => false, 'message' => 'Only PUT method allowed'], 405);
}

// Get JSON data
$data = getRequestData();

// Validate required fields
$required_fields = ['tracking_number', 'status', 'location', 'description'];
$missing = validateInput($data, $required_fields);

if (!empty($missing)) {
    sendResponse([
        'success' => false,
        'message' => 'Missing required fields: ' . implode(', ', $missing)
    ], 400);
}

try {
    // Check if shipment exists
    $checkQuery = "SELECT id FROM shipments WHERE tracking_number = :tracking_number";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->bindParam(':tracking_number', $data['tracking_number']);
    $checkStmt->execute();

    if ($checkStmt->rowCount() === 0) {
        sendResponse(['success' => false, 'message' => 'Shipment not found'], 404);
    }

    // Update shipment status
    $updateQuery = "UPDATE shipments 
                    SET status = :status, updated_at = NOW() 
                    WHERE tracking_number = :tracking_number";
    $stmt = $db->prepare($updateQuery);
    $stmt->bindParam(':status', $data['status']);
    $stmt->bindParam(':tracking_number', $data['tracking_number']);
    $stmt->execute();

    // (Optional) You can insert tracking logs into another table if needed
    // For now, only the shipment status is updated.

    sendResponse(['success' => true, 'message' => 'Shipment status updated successfully']);

} catch (Exception $e) {
    sendResponse([
        'success' => false,
        'message' => 'Failed to update shipment status: ' . $e->getMessage()
    ], 500);
}
