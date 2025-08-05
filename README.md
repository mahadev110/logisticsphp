# 🚚 Logistics Management Portal

A web-based logistics management system to streamline orders, shipments, drivers, and vehicle tracking.

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
