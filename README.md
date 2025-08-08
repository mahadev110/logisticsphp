# 🚚 Logistics Management Portal

A web-based logistics management system to streamline orders, shipments, drivers, and vehicle tracking.
Complete Workflow Now:
1. Create Order → Customer details saved
2. Create Shipment → Select Vehicle + Driver + Route
3. Update Shipment → Change status, reassign vehicle/driver
4. Track Shipment → See complete info including assigned resources
5. Vehicle/Driver Status → Shows current assignments and availability

## 📌 Features

- 📋 Create and manage customer orders
- 🚛 Create and track shipments with real-time status
- 🧍‍♂️ Manage drivers and assign them to vehicles
- 🚐 Track available vehicles and update their locations
- 🛰️ Track orders using unique tracking numbers
- 📊 Responsive Bootstrap frontend with tabbed interface
- 🛠️ REST API with JSON responses

## 🚀 Tech Stack

- **Frontend**: HTML, Bootstrap 5, Font Awesome, JavaScript
- **Backend**: PHP (PDO), REST API
- **Database**: MySQL
- **Tools**: XAMPP/Apache, Git

## 🛠️ Setup Instructions

### 1. Clone the Repo

```bash
git clone https://github.com/mahadev110/logistics-portal.git
cd logistics-portal
2. Configure the Database
Create the database:

sql
Copy
Edit
CREATE DATABASE logistics_db;
Import tables using your own .sql or setup script.

3. Set DB Credentials
Update config/db.php:

php
Copy
Edit
private $host = "localhost";
private $db_name = "logistics_db";
private $username = "root";
private $password = "";
4. Run the App
Start Apache & MySQL via XAMPP

Open in browser:

bash
Copy
Edit
http://localhost/Logistics_portal/index.html
🔗 API Endpoints
View full documentation in api_documentation.md

Example:

http
Copy
Edit
POST /orders/create
{
  "customer_name": "John",
  "pickup_address": "Chennai",
  "delivery_address": "Delhi"
}
📁 Project Structure
arduino
Copy
Edit
Logistics_portal/
├── api/
│   ├── orders/
│   ├── shipments/
│   └── drivers/
├── vehicles/
├── config/
├── index.html
├── api_documentation.md
└── README.md
📈 Roadmap Ideas
Multi-order shipments

Route optimization

Real-time tracking via GPS

Admin login & role-based access

Proof of Delivery (POD)


🔧 Key Features Working:
1. Dashboard

Real-time statistics (Orders, Shipments, Vehicles, Drivers)
Recent activities timeline
System status monitoring

2. Orders Management

Create new orders with customer details
Display all orders in card layout
Order status tracking
Integration with shipments

3. Shipments Management

Create new shipments
Update shipment status with location tracking
Visual tracking display
Quick actions for tracking and updates

4. Vehicles Management

Add new vehicles with details
Display vehicle information and status
Driver assignment integration

5. Drivers Management

Add new drivers with license information
Display driver details and vehicle assignments
Status management

6. Tracking System

Real-time shipment tracking
Timeline view of tracking history
Quick track buttons throughout the system

🚀 Integration Features:

Cross-referencing: Orders, shipments, vehicles, and drivers are linked
Quick Actions: Fast navigation between related items
Real-time Updates: Dashboard updates when data changes
Error Handling: User-friendly error messages
Toast Notifications: Success/error feedback for all actions

📱 UI/UX Improvements:

Responsive Design: Works on all device sizes
Card-based Layout: Modern, clean interface
Status Badges: Color-coded status indicators
Loading States: Visual feedback during operations
Hover Effects: Interactive elements

🔌 API Endpoints Expected:

/orders/create_order.php - POST
/orders/get_orders.php - GET
/shipments/create_shipment.php - POST
/shipments/get_shipment.php - GET
/shipments/update_status.php - PUT
/shipments/track_shipment.php - GET
/vehicles/add_vehicle.php - POST
/vehicles/get_vehicles.php - GET
/drivers/add_driver.php - POST
/drivers/get_drivers.php - GET

The portal is now complete and should load properly without any errors. All functions are integrated and working together as a comprehensive logistics management system.