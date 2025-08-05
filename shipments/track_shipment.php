
<?php
// shipments/track_shipment.php
require_once '../config/db.php';

$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendResponse(['success' => false, 'message' => 'Only GET method allowed'], 405);
}

if (!isset($_GET['tracking_number'])) {
    sendResponse(['success' => false, 'message' => 'Tracking number is required'], 400);
}

$tracking_number = $_GET['tracking_number'];

// Get shipment details with related order information
$query = "SELECT s.*, o.customer_name, o.customer_email, o.pickup_address, o.delivery_address 
          FROM shipments s 
          LEFT JOIN orders o ON s.id = o.shipment_id 
          WHERE s.tracking_number = :tracking_number";

$stmt = $db->prepare($query);
$stmt->bindParam(':tracking_number', $tracking_number);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    $shipment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Generate tracking history based on status
    $tracking_history = [];
    $statuses = ['pending', 'in_transit', 'delivered'];
    $current_status_index = array_search($shipment['status'], $statuses);
    
    for ($i = 0; $i <= $current_status_index; $i++) {
        $tracking_history[] = [
            'status' => $statuses[$i],
            'timestamp' => $shipment['updated_at'],
            'location' => $i == 0 ? $shipment['origin'] : ($i == count($statuses) - 1 ? $shipment['destination'] : 'In Transit'),
            'description' => ucfirst(str_replace('_', ' ', $statuses[$i]))
        ];
    }
    
    $response = [
        'success' => true,
        'data' => [
            'shipment' => $shipment,
            'tracking_history' => $tracking_history
        ]
    ];
    
    sendResponse($response);
} else {
    sendResponse(['success' => false, 'message' => 'Shipment not found with this tracking number'], 404);
}
?>