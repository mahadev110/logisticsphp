<?php
// orders/update_status.php
require_once '../config/db.php';

$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    sendResponse(['success' => false, 'message' => 'Only PUT method allowed'], 405);
}

$data = getRequestData();

// Support order_number as alternative to id
if (!isset($data['id']) && isset($data['order_number'])) {
    $order_number = trim($data['order_number']);
    $stmt = $db->prepare("SELECT id FROM orders WHERE order_number = :order_number");
    $stmt->bindParam(':order_number', $order_number);
    $stmt->execute();

    if ($stmt->rowCount() === 0) {
        sendResponse(['success' => false, 'message' => 'Order not found'], 404);
    }

    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    $data['id'] = $order['id'];
}

$required_fields = ['id', 'status'];
$missing_fields = validateInput($data, $required_fields);

if (!empty($missing_fields)) {
    sendResponse(['success' => false, 'message' => 'Missing required fields: ' . implode(', ', $missing_fields)], 400);
}

// Allow extended statuses
$allowed_statuses = ['pending', 'confirmed', 'processing', 'shipped', 'picked_up', 'in_transit', 'delivered', 'cancelled'];
$status = strtolower(trim($data['status']));

if (!in_array($status, $allowed_statuses)) {
    sendResponse(['success' => false, 'message' => 'Invalid status. Allowed values: ' . implode(', ', $allowed_statuses)], 400);
}

try {
    $db->beginTransaction();

    // Update order status
    $order_query = "UPDATE orders SET status = :status WHERE id = :id";
    $order_stmt = $db->prepare($order_query);
    $order_stmt->bindParam(':status', $status);
    $order_stmt->bindParam(':id', $data['id']);
    $order_stmt->execute();

    if ($order_stmt->rowCount() === 0) {
        $db->rollback();
        sendResponse(['success' => false, 'message' => 'Order not found or already has this status'], 404);
    }

    // Get shipment ID
    $get_shipment_query = "SELECT shipment_id FROM orders WHERE id = :id";
    $get_shipment_stmt = $db->prepare($get_shipment_query);
    $get_shipment_stmt->bindParam(':id', $data['id']);
    $get_shipment_stmt->execute();
    $shipment_data = $get_shipment_stmt->fetch(PDO::FETCH_ASSOC);

    // If shipment is linked, update its status too
    if ($shipment_data && $shipment_data['shipment_id']) {
        $shipment_status_map = [
            'pending'     => 'pending',
            'confirmed'   => 'pending',
            'processing'  => 'pending',
            'shipped'     => 'in_transit',
            'picked_up'   => 'in_transit',
            'in_transit'  => 'in_transit',
            'delivered'   => 'delivered',
            'cancelled'   => 'cancelled'
        ];

        $shipment_status = $shipment_status_map[$status] ?? null;

        if ($shipment_status === null) {
            $db->rollback();
            sendResponse(['success' => false, 'message' => 'Invalid shipment status mapping.'], 400);
        }

        $shipment_query = "UPDATE shipments SET status = :status WHERE id = :shipment_id";
        $shipment_stmt = $db->prepare($shipment_query);
        $shipment_stmt->bindParam(':status', $shipment_status);
        $shipment_stmt->bindParam(':shipment_id', $shipment_data['shipment_id']);
        $shipment_stmt->execute();
    }

    $db->commit();

    sendResponse([
        'success' => true,
        'message' => 'Order status updated successfully',
        'data' => [
            'id' => $data['id'],
            'status' => $status,
            'notes' => $data['notes'] ?? null
        ]
    ]);
} catch (Exception $e) {
    $db->rollback();
    sendResponse(['success' => false, 'message' => 'Failed to update order status: ' . $e->getMessage()], 500);
}
