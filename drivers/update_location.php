
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