# Logistics Management REST API

## 🚀 Setup Instructions

### Prerequisites
- PHP 7.4+ with PDO extension
- MySQL 5.7+ or MariaDB
- Web server (Apache/Nginx)

### Installation Steps

1. **Database Setup**
   ```sql
   CREATE DATABASE logistics_db;
   ```

2. **Configuration**
   - Update database credentials in `config/db.php`
   - Ensure proper file permissions

3. **Access**
   - Frontend: `http://your-domain/logistics_portal/index.html`
   - API Docs: `http://your-domain/logistics_portal/index.php`

## 📚 API Endpoints

### Base URL
```
http://your-domain/logistics_portal/api
```

### 📦 Shipments API

#### Get All Shipments
```http
GET /shipments?page=1&limit=10
```

#### Get Specific Shipment
```http
GET /shipments?id={shipment_id}
```

#### Create Shipment
```http
POST /shipments
Content-Type: application/json

{
  "origin": "Mumbai, India",
  "destination": "Delhi, India",
  "weight": 25.5,
  "dimensions": "30x20x15 cm"
}
```

#### Update Shipment
```http
PUT /shipments
Content-Type: application/json

{
  "id": 1,
  "status": "in_transit",
  "weight": 30.0
}
```

#### Track Shipment
```http
GET /shipments/track?tracking_number=TRK202501011234
```

### 📋 Orders API

#### Get All Orders
```http
GET /orders?page=1&limit=10&status=pending
```

#### Create Order
```http
POST /orders/create
Content-Type: application/json

{
  "customer_name": "John Doe",
  "customer_email": "john@example.com",
  "customer_phone": "+91-9876543210",
  "pickup_address": "123 Main St, Mumbai, India",
  "delivery_address": "456 Park Ave, Delhi, India",
  "total_amount": 500.00
}
```

#### Update Order Status
```http
PUT /orders/update-status
Content-Type: application/json

{
  "id": 1,
  "status": "delivered"
}
```

### 🚛 Vehicles API

#### Get All Vehicles
```http
GET /vehicles?status=available&available=true
```

#### Create Vehicle
```http
POST /vehicles
Content-Type: application/json

{
  "vehicle_number": "MH01AB1234",
  "vehicle_type": "truck",
  "capacity": 1000.0,
  "current_location": "Mumbai Depot"
}
```

#### Update Vehicle Location
```http
PUT /vehicles/update-location
Content-Type: application/json

{
  "id": 1,
  "current_location": "Delhi Hub",
  "latitude": 28.6139,
  "longitude": 77.2090,
  "status": "in_use"
}
```

### 👨‍💼 Drivers API

#### Get All Drivers
```http
GET /drivers?status=available
```

#### Create Driver
```http
POST /drivers
Content-Type: application/json

{
  "name": "Rajesh Kumar",
  "license_number": "DL123456789",
  "phone": "+91-9876543210",
  "email": "rajesh@example.com"
}
```

#### Assign Driver to Vehicle
```http
PUT /drivers/assign
Content-Type: application/json

{
  "driver_id": 1,
  "vehicle_id": 2
}
```

## 📊 Database Schema

### Tables Structure

#### shipments
- `id` (Primary Key)
- `tracking_number` (Unique)
- `origin`
- `destination`
- `status` (pending, in_transit, delivered, cancelled)
- `weight`
- `dimensions`
- `created_at`, `updated_at`

#### orders
- `id` (Primary Key)
- `order_number` (Unique)
- `customer_name`, `customer_email`, `customer_phone`
- `pickup_address`, `delivery_address`
- `status` (pending, confirmed, picked_up, in_transit, delivered, cancelled)
- `total_amount`
- `shipment_id` (Foreign Key)
- `created_at`, `updated_at`

#### vehicles
- `id` (Primary Key)
- `vehicle_number` (Unique)
- `vehicle_type`
- `capacity`
- `current_location`, `latitude`, `longitude`
- `status` (available, in_use, maintenance)
- `created_at`, `updated_at`

#### drivers
- `id` (Primary Key)
- `name`
- `license_number` (Unique)
- `phone`, `email`
- `status` (available, assigned, off_duty)
- `vehicle_id` (Foreign Key)
- `created_at`, `updated_at`

## 🔄 Status Flow

### Order Status Flow
```
pending → confirmed → picked_up → in_transit → delivered
                                             ↘ cancelled
```

### Shipment Status Flow
```
pending → in_transit → delivered
                    ↘ cancelled
```

## 💡 Usage Examples

### Complete Order Creation Flow
1. Create order with customer details
2. System auto-creates linked shipment
3. Assign available driver and vehicle
4. Update statuses as shipment progresses
5. Track shipment using tracking number

### Driver-Vehicle Assignment
1. Check available drivers: `GET /drivers?available=true`
2. Check available vehicles: `GET /vehicles?available=true`
3. Assign driver to vehicle: `PUT /drivers/assign`

## 🛡️ Error Handling

All endpoints return standardized JSON responses:

```json
{
  "success": true/false,
  "message": "Description",
  "data": {...}
}
```

### HTTP Status Codes
- `200` - Success
- `201` - Created
- `400` - Bad Request
- `404` - Not Found
- `405` - Method Not Allowed
- `500` - Internal Server Error

## 🔐 Security Features

- Input validation on all endpoints
- SQL injection protection via prepared statements
- CORS headers for cross-origin requests
- Transaction support for data consistency
- Error logging for debugging

## 📱 Frontend Interface

The included `index.html` provides:
- Interactive forms for all operations
- Real-time API testing
- Responsive design
- Tabbed interface for different modules
- JSON response display

## 🚀 Deployment Notes

1. Ensure database credentials are secure
2. Configure proper web server rewrites for clean URLs
3. Set appropriate file permissions
4. Enable error logging in production
5. Consider implementing authentication for production use

## 🔧 Extending the API

To add new features:
1. Create new endpoint files following the same structure
2. Add routes in `index.php`
3. Update database schema if needed
4. Add corresponding frontend forms
5. Update this documentation