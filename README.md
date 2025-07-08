# Grocery Shop Management System

A modern, full-featured web application for managing a grocery shop, built with PHP, MySQL, Bootstrap 5, and JavaScript.

---

## 🚀 Features

- **User Authentication**
  - Registration (customer/admin)
  - Secure login/logout with session management
  - Passwords hashed with `password_hash()`
  - Role-based access (admin/customer)

- **Admin Panel**
  - Dashboard overview
  - Manage products (CRUD with image upload)
  - Inventory management (update stock, low stock warning)
  - Orders management (view, update status, order details)
  - Sales reports with Chart.js (last 7 days, total sales/orders)

- **Customer Portal**
  - Modern dashboard with product search and add-to-cart
  - Shopping cart (session-based, update/remove items)
  - Checkout (place order, stock check, order saved to DB)
  - Order history (view past orders and details)
  - Profile management (edit name, phone, address)

- **Landing Page**
  - Beautiful, responsive homepage listing all groceries
  - Navigation adapts to user state (guest, customer, admin)
  - Modern UI with Bootstrap 5 and custom CSS

- **Security**
  - Passwords hashed and verified securely
  - Prepared statements (MySQLi) to prevent SQL injection
  - Server-side and client-side validation
  - Session-based access control for all protected pages
  - File upload validation (type/size)

---

## 🛠️ Technologies Used

- **Frontend:**
  - HTML5, CSS3, Bootstrap 5 (CDN)
  - JavaScript (with Chart.js for admin reports)
  - Custom CSS for enhanced UI

- **Backend:**
  - PHP 7+
  - MySQL (with MySQLi prepared statements)

- **Database:**
  - MySQL (see `database/grocery_shop.sql` for schema)

---

## 📁 Folder Structure

```
grocery_shop/
├── assets/
│   ├── css/style.css
│   ├── js/script.js
│   └── images/ (product images)
├── includes/
│   ├── db_connection.php
│   ├── header.php
│   └── footer.php
├── admin/
│   ├── admin_dashboard.php
│   ├── manage_products.php
│   ├── add_product.php
│   ├── orders.php
│   ├── inventory.php
│   └── reports.php
├── customer/
│   ├── customer_dashboard.php
│   ├── cart.php
│   ├── checkout.php
│   ├── order_history.php
│   └── profile.php
├── auth/
│   ├── login.php
│   ├── register.php
│   └── logout.php
├── database/
│   └── grocery_shop.sql
├── index.php
└── README.md
```

---

## ⚙️ Setup Instructions

1. **Clone or download the repository.**
2. **Import the database:**
   - Create a MySQL database named `grocery_shop`.
   - Import `database/grocery_shop.sql` into your MySQL server.
3. **Configure database connection:**
   - Edit `includes/db_connection.php` with your MySQL credentials.
4. **Set up your web server:**
   - Place the project in your web root (e.g., `htdocs` for XAMPP).
   - Make sure PHP and MySQL are running.
5. **Access the app:**
   - Open `http://localhost/grocery_shop/index.php` in your browser.

---

## ✨ Credits
- UI: Bootstrap 5 (CDN)
- Charts: Chart.js (CDN)
- PDF/CSV export, email, and advanced analytics can be added as optional features.

---

## 📢 Notes
- All code is written with security and best practices in mind.
- For any issues or feature requests, please open an issue or contact the maintainer.
