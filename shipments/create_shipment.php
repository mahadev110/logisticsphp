<?php
// shipments/create_shipment.php
require_once '../config/db.php';

$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(['success' => false, 'message' => 'Only POST method allowed'], 405);
}

$data = getRequestData();
$required_fields = ['origin', 'destination'];
$missing_fields = validateInput($data, $required_fields);

if (!empty($missing_fields)) {
    sendResponse([
        'success' => false,
        'message' => 'Missing required fields: ' . implode(', ', $missing_fields)
    ], 400);
}

try {
    // Generate tracking number
    $tracking_number = 'TRK' . date('Ymd') . rand(1000, 9999);

    $query = "INSERT INTO shipments (tracking_number, origin, destination, weight, dimensions, status)
              VALUES (:tracking_number, :origin, :destination, :weight, :dimensions, :status)";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':tracking_number', $tracking_number);
    $stmt->bindParam(':origin', $data['origin']);
    $stmt->bindParam(':destination', $data['destination']);
    $stmt->bindParam(':weight', $data['weight']);
    $stmt->bindParam(':dimensions', $data['dimensions']);
    $status = 'pending';
    $stmt->bindParam(':status', $status);

    if ($stmt->execute()) {
        $shipment_id = $db->lastInsertId();
        sendResponse([
            'success' => true,
            'message' => 'Shipment created successfully',
            'data' => [
                'id' => $shipment_id,
                'tracking_number' => $tracking_number
            ]
        ], 201);
    } else {
        sendResponse(['success' => false, 'message' => 'Failed to create shipment'], 500);
    }

} catch (Exception $e) {
    sendResponse(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
}
