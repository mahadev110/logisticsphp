<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../config/db.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $query = "SELECT * FROM shipments WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $shipment = $stmt->fetch(PDO::FETCH_ASSOC);
                sendResponse(['success' => true, 'data' => $shipment]);
            } else {
                sendResponse(['success' => false, 'message' => 'Shipment not found'], 404);
            }
        } else {
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $offset = ($page - 1) * $limit;

            $query = "SELECT * FROM shipments ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            $shipments = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $count_query = "SELECT COUNT(*) as total FROM shipments";
            $count_stmt = $db->prepare($count_query);
            $count_stmt->execute();
            $total = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];

            sendResponse([
                'success' => true,
                'data' => $shipments,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'pages' => ceil($total / $limit)
                ]
            ]);
        }
        break;

    case 'POST':
        $data = getRequestData();
        $required_fields = ['origin', 'destination'];
        $missing_fields = validateInput($data, $required_fields);

        if (!empty($missing_fields)) {
            sendResponse(['success' => false, 'message' => 'Missing required fields: ' . implode(', ', $missing_fields)], 400);
        }

        $tracking_number = 'TRK' . date('Ymd') . rand(1000, 9999);

        // Assign values to variables before binding
        $origin = $data['origin'];
        $destination = $data['destination'];
        $weight = $data['weight'] ?? null;
        $dimensions = $data['dimensions'] ?? null;
        $status = $data['status'] ?? 'pending';

        $query = "INSERT INTO shipments (tracking_number, origin, destination, weight, dimensions, status) 
                  VALUES (:tracking_number, :origin, :destination, :weight, :dimensions, :status)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':tracking_number', $tracking_number);
        $stmt->bindParam(':origin', $origin);
        $stmt->bindParam(':destination', $destination);
        $stmt->bindParam(':weight', $weight);
        $stmt->bindParam(':dimensions', $dimensions);
        $stmt->bindParam(':status', $status);

        if ($stmt->execute()) {
            $shipment_id = $db->lastInsertId();
            sendResponse([
                'success' => true,
                'message' => 'Shipment created successfully',
                'data' => ['id' => $shipment_id, 'tracking_number' => $tracking_number]
            ], 201);
        } else {
            sendResponse(['success' => false, 'message' => 'Failed to create shipment'], 500);
        }
        break;

    case 'PUT':
        $data = getRequestData();
        if (!isset($data['id'])) {
            sendResponse(['success' => false, 'message' => 'Shipment ID is required'], 400);
        }

        $fields_to_update = [];
        $params = [':id' => $data['id']];
        $allowed_fields = ['origin', 'destination', 'weight', 'dimensions', 'status'];

        foreach ($allowed_fields as $field) {
            if (isset($data[$field])) {
                $fields_to_update[] = "$field = :$field";
                $params[":$field"] = $data[$field];
            }
        }

        if (empty($fields_to_update)) {
            sendResponse(['success' => false, 'message' => 'No fields to update'], 400);
        }

        $query = "UPDATE shipments SET " . implode(', ', $fields_to_update) . " WHERE id = :id";
        $stmt = $db->prepare($query);

        if ($stmt->execute($params)) {
            sendResponse(['success' => true, 'message' => 'Shipment updated successfully']);
        } else {
            sendResponse(['success' => false, 'message' => 'Failed to update shipment'], 500);
        }
        break;

    case 'DELETE':
        $data = getRequestData();
        if (!isset($data['id'])) {
            sendResponse(['success' => false, 'message' => 'Shipment ID is required'], 400);
        }

        $query = "DELETE FROM shipments WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $data['id']);

        if ($stmt->execute()) {
            sendResponse(['success' => true, 'message' => 'Shipment deleted successfully']);
        } else {
            sendResponse(['success' => false, 'message' => 'Failed to delete shipment'], 500);
        }
        break;

    default:
        sendResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}
?>
