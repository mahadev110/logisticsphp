<?php
// index.php - API Router and Documentation
require_once 'config/db.php';

// Initialize database tables if they don't exist
$database = new Database();
$db = $database->getConnection();
if ($db) {
    createTables($db);
}

// API Router
$request_uri = $_SERVER['REQUEST_URI'];
$request_method = $_SERVER['REQUEST_METHOD'];

// Remove query string and base path
$path = parse_url($request_uri, PHP_URL_PATH);
$path = str_replace('/logistics_portal', '', $path); // Adjust based on your folder structure

// Route requests
switch ($path) {
    case '/api/shipments':
    case '/api/shipments/':
        include 'shipments/get_shipment.php';
        break;
        
    case '/api/shipments/track':
        include 'shipments/track_shipment.php';
        break;
        
    case '/api/orders':
    case '/api/orders/':
        include 'orders/get_orders.php';
        break;
        
    case '/api/orders/create':
        include 'orders/create_order.php';
        break;
        
    case '/api/orders/update-status':
        include 'orders/update_status.php';
        break;
        
    case '/api/vehicles':
    case '/api/vehicles/':
        include 'vehicles/get_vehicles.php';
        break;
        
    case '/api/vehicles/update-location':
        include 'vehicles/update_location.php';
        break;
        
    case '/api/drivers':
    case '/api/drivers/':
        include 'drivers/get_drivers.php';
        break;
        
    case '/api/drivers/assign':
        include 'drivers/assign_driver.php';
        break;
        
    default:
        // API Documentation
        header('Content-Type: application/json');
        $api_docs = [
            'title' => 'Logistics Management API',
            'version' => '1.0.0',
            'description' => 'REST API for logistics and shipment management',
            'base_url' => 'http://' . $_SERVER['HTTP_HOST'] . '/logistics_portal/api',
            'endpoints' => [
                'shipments' => [
                    'GET /api/shipments' => 'Get all shipments (with pagination: ?page=1&limit=10)',
                    'GET /api/shipments?id={id}' => 'Get specific shipment',
                    'POST /api/shipments' => 'Create new shipment',
                    'PUT /api/shipments' => 'Update shipment',
                    'DELETE /api/shipments' => 'Delete shipment',
                    'GET /api/shipments/track?tracking_number={number}' => 'Track shipment'
                ],
                'orders' => [
                    'GET /api/orders' => 'Get all orders (with pagination and status filter)',
                    'GET /api/orders?id={id}' => 'Get specific order',
                    'POST /api/orders/create' => 'Create new order (auto-creates shipment)',
                    'PUT /api/orders/update-status' => 'Update order status'
                ],
                'vehicles' => [
                    'GET /api/vehicles' => 'Get all vehicles (filter: ?status=available&available=true)',
                    'GET /api/vehicles?id={id}' => 'Get specific vehicle',
                    'POST /api/vehicles' => 'Create new vehicle',
                    'PUT /api/vehicles/update-location' => 'Update vehicle location/status'
                ],
                'drivers' => [
                    'GET /api/drivers' => 'Get all drivers (filter: ?status=available&available=true)',
                    'GET /api/drivers?id={id}' => 'Get specific driver',
                    'POST /api/drivers' => 'Create new driver',
                    'PUT /api/drivers/assign' => 'Assign driver to vehicle'
                ]
            ],
            'sample_requests' => [
                'create_shipment' => [
                    'method' => 'POST',
                    'url' => '/api/shipments',
                    'body' => [
                        'origin' => 'Mumbai, India',
                        'destination' => 'Delhi, India',
                        'weight' => 25.5,
                        'dimensions' => '30x20x15 cm'
                    ]
                ],
                'create_order' => [
                    'method' => 'POST',
                    'url' => '/api/orders/create',
                    'body' => [
                        'customer_name' => 'John Doe',
                        'customer_email' => 'john@example.com',
                        'customer_phone' => '+91-9876543210',
                        'pickup_address' => '123 Main St, Mumbai, India',
                        'delivery_address' => '456 Park Ave, Delhi, India',
                        'total_amount' => 500.00
                    ]
                ],
                'track_shipment' => [
                    'method' => 'GET',
                    'url' => '/api/shipments/track?tracking_number=TRK202501011234',
                    'response' => 'Returns shipment details with tracking history'
                ]
            ],
            'status_codes' => [
                '200' => 'Success',
                '201' => 'Created',
                '400' => 'Bad Request',
                '404' => 'Not Found',
                '405' => 'Method Not Allowed',
                '500' => 'Internal Server Error'
            ]
        ];
        
        echo json_encode($api_docs, JSON_PRETTY_PRINT);
        break;
}
?>