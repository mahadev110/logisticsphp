<?php
// orders/get_orders.php
require_once '../config/db.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            // Get specific order
            $id = $_GET['id'];
            $query = "SELECT o.*, s.tracking_number, s.status as shipment_status 
                      FROM orders o 
                      LEFT JOIN shipments s ON o.shipment_id = s.id 
                      WHERE o.id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $order = $stmt->fetch(PDO::FETCH_ASSOC);
                sendResponse(['success' => true, 'data' => $order]);
            } else {
                sendResponse(['success' => false, 'message' => 'Order not found'], 404);
            }
        } else {
            // Get all orders with filtering
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $offset = ($page - 1) * $limit;
            $status_filter = isset($_GET['status']) ? $_GET['status'] : null;
            
            $where_clause = "";
            $params = [];
            
            if ($status_filter) {
                $where_clause = "WHERE o.status = :status";
                $params[':status'] = $status_filter;
            }
            
            $query = "SELECT o.*, s.tracking_number, s.status as shipment_status 
                      FROM orders o 
                      LEFT JOIN shipments s ON o.shipment_id = s.id 
                      $where_clause 
                      ORDER BY o.created_at DESC 
                      LIMIT :limit OFFSET :offset";
            
            $stmt = $db->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get total count
            $count_query = "SELECT COUNT(*) as total FROM orders o $where_clause";
            $count_stmt = $db->prepare($count_query);
            foreach ($params as $key => $value) {
                $count_stmt->bindValue($key, $value);
            }
            $count_stmt->execute();
            $total = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            sendResponse([
                'success' => true,
                'data' => $orders,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'pages' => ceil($total / $limit)
                ]
            ]);
        }
        break;
        
    default:
        sendResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}
?>