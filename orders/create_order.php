<?php
// orders/create_order.php
require_once '../config/db.php';

$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(['success' => false, 'message' => 'Only POST method allowed'], 405);
}

$data = getRequestData();
$required_fields = ['customer_name', 'pickup_address', 'delivery_address'];
$missing_fields = validateInput($data, $required_fields);

if (!empty($missing_fields)) {
    sendResponse(['success' => false, 'message' => 'Missing required fields: ' . implode(', ', $missing_fields)], 400);
}

try {
    $db->beginTransaction();
    
    // Generate order number
    $order_number = 'ORD' . date('Ymd') . rand(1000, 9999);
    
    // Create shipment first
    $shipment_query = "INSERT INTO shipments (tracking_number, origin, destination, status) 
                       VALUES (:tracking_number, :origin, :destination, 'pending')";
    
    $tracking_number = 'TRK' . date('Ymd') . rand(1000, 9999);
    $shipment_stmt = $db->prepare($shipment_query);
    $shipment_stmt->bindParam(':tracking_number', $tracking_number);
    $shipment_stmt->bindParam(':origin', $data['pickup_address']);
    $shipment_stmt->bindParam(':destination', $data['delivery_address']);
    $shipment_stmt->execute();
    
    $shipment_id = $db->lastInsertId();
    
    // Create order
    $order_query = "INSERT INTO orders (order_number, customer_name, customer_email, customer_phone, 
                    pickup_address, delivery_address, total_amount, shipment_id, status) 
                    VALUES (:order_number, :customer_name, :customer_email, :customer_phone, 
                    :pickup_address, :delivery_address, :total_amount, :shipment_id, 'pending')";
    
    $customer_name = $data['customer_name'];
$customer_email = $data['customer_email'] ?? null;
$customer_phone = $data['customer_phone'] ?? null;
$pickup_address = $data['pickup_address'];
$delivery_address = $data['delivery_address'];
$total_amount = $data['total_amount'] ?? 0;

$order_stmt = $db->prepare($order_query);
$order_stmt->bindParam(':order_number', $order_number);
$order_stmt->bindParam(':customer_name', $customer_name);
$order_stmt->bindParam(':customer_email', $customer_email);
$order_stmt->bindParam(':customer_phone', $customer_phone);
$order_stmt->bindParam(':pickup_address', $pickup_address);
$order_stmt->bindParam(':delivery_address', $delivery_address);
$order_stmt->bindParam(':total_amount', $total_amount);
$order_stmt->bindParam(':shipment_id', $shipment_id);


    $order_stmt->execute();
    
    $order_id = $db->lastInsertId();
    
    $db->commit();
    
    sendResponse([
        'success' => true, 
        'message' => 'Order created successfully',
        'data' => [
            'order_id' => $order_id,
            'order_number' => $order_number,
            'tracking_number' => $tracking_number,
            'shipment_id' => $shipment_id
        ]
    ], 201);
    
} catch (Exception $e) {
    $db->rollback();
    sendResponse(['success' => false, 'message' => 'Failed to create order: ' . $e->getMessage()], 500);
}
?>
