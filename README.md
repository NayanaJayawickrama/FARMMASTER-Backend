
# 🌾 FARMMASTER Backend – Organic Farming Management System (PHP + MySQL)

This is the backend of **FARMMASTER**, a web-based platform for managing agricultural lands, crop reports, proposals, harvests, payments, and marketplace orders.  
The backend is built using **pure PHP (MVC architecture)** with **MySQL** as the database and **PHPMailer + Stripe** integrations.

---

## 🚀 Tech Stack

### **Backend**
- PHP 7.4+
- MySQL (XAMPP)
- MVC Architecture
- PHPMailer (Gmail SMTP)
- Stripe Payment API
- PDO (prepared statements)
- Apache Server

---

## 📁 Folder Structure

```

FARMMASTER-Backend/
├── api.php                     # Main API router
├── config/
│   └── Database.php            # DB config
├── controllers/
│   ├── UserController.php
│   ├── LandController.php
│   ├── ProposalController.php
│   ├── LandReportController.php
│   ├── HarvestController.php
│   ├── PaymentController.php
│   └── OrderController.php
├── models/
│   ├── UserModel.php
│   ├── LandModel.php
│   └── HarvestModel.php
├── services/
│   ├── EmailService.php
│   └── EmailServiceDev.php
├── middleware/
├── utils/
│   ├── Response.php
│   ├── SessionManager.php
│   └── Validator.php
└── farm_master_.sql

````

---

## ⚙️ Installation

### **1. Clone repository**
```bash
cd c:/xampp/htdocs
git clone <repo-url> FarmMaster
````

### **2. Install PHP dependencies**

```bash
cd FarmMaster/FARMMASTER-Backend
composer install
```

### **3. Import database**

* Start XAMPP → Apache + MySQL
* Open `http://localhost/phpmyadmin`
* Create DB: **farm_master**
* Import `farm_master_.sql`

### **4. Configure database**

Edit `config/Database.php`:

```php
private $host = "localhost";
private $db_name = "farm_master";
private $username = "root";
private $password = "";
```

### **5. Configure Email (Gmail SMTP)**

```php
$this->mailer->Host = 'smtp.gmail.com';
$this->mailer->Port = 587;
$this->mailer->Username = 'your-email@gmail.com';
$this->mailer->Password = 'your-app-password';
```

### **6. Start Local Server**

* Open XAMPP
* Start **Apache + MySQL**

---

## 🔑 API Base URL

```
http://localhost/FarmMaster/FARMMASTER-Backend/api.php
```

---

## 📡 Major API Endpoints

### **User**

```
POST /api/users/login
POST /api/users/register
POST /api/users/forgot-password
POST /api/users/reset-password
GET  /api/users/profile
```

### **Land Reports**

```
GET /api/land-reports/assignments-public
POST /api/land-reports
PUT /api/land-reports/{id}
```

### **Proposals**

```
GET /api/proposals/public
POST /api/proposals
PUT /api/proposals/{id}
```

### **Harvest**

```
POST /api/harvest
GET  /api/harvest/landowner/{userId}
```

### **Payments**

```
POST /api/payments/create-intent
POST /api/payments/confirm
GET  /api/payments/history
```

### **Products**

```
GET /api/products
POST /api/products
PUT /api/products/{id}
```

### **Orders**

```
POST /api/orders
GET /api/orders/{id}
```

---

## 🔐 Security Features

* Secure password hashing (bcrypt)
* PDO prepared statements
* CORS headers
* Session timeout management
* CSRF protection
* Validation sanitizer
* Stripe secure payments

---

## 🐛 Troubleshooting

### **Database errors**

* Ensure MySQL is running
* Check credentials in `Database.php`

### **Email not sending**

* Use Gmail App Password
* Check SMTP port and host

### **CORS issues**

* Update CORS in `api.php`


