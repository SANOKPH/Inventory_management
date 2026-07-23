-- ============================================================
-- Inventory Management System - Database Schema
-- ============================================================
CREATE DATABASE IF NOT EXISTS inventory_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE inventory_system;

-- ---------------- Users & Auth ----------------
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('Admin','Manager','Inventory','Cashier','Viewer') DEFAULT 'Viewer',
    status ENUM('Active','Inactive') DEFAULT 'Active',
    profile_image VARCHAR(255) DEFAULT NULL,
    reset_token VARCHAR(255) DEFAULT NULL,
    reset_expires DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE login_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    login_time DATETIME,
    logout_time DATETIME NULL,
    ip_address VARCHAR(45),
    device VARCHAR(255),
    FOREIGN KEY(user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- ---------------- Categories ----------------
CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ---------------- Suppliers ----------------
CREATE TABLE suppliers (
    supplier_id INT AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(150) NOT NULL,
    contact_person VARCHAR(100),
    phone VARCHAR(30),
    email VARCHAR(100),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ---------------- Products ----------------
CREATE TABLE products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    supplier_id INT,
    sku VARCHAR(50) UNIQUE NOT NULL,
    product_name VARCHAR(150) NOT NULL,
    cost_price DECIMAL(12,2) DEFAULT 0,
    selling_price DECIMAL(12,2) DEFAULT 0,
    stock_qty INT DEFAULT 0,
    minimum_stock INT DEFAULT 0,
    barcode VARCHAR(100),
    image VARCHAR(255),
    status ENUM('Active','Inactive') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE SET NULL,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id) ON DELETE SET NULL
);

-- ---------------- Stock In ----------------
CREATE TABLE stock_in (
    stock_in_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    supplier_id INT,
    quantity INT NOT NULL,
    cost_price DECIMAL(12,2),
    purchase_date DATE,
    reference_no VARCHAR(100),
    remark TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id) ON DELETE SET NULL
);

-- ---------------- Stock Out ----------------
CREATE TABLE stock_out (
    stock_out_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    reason ENUM('Sale','Damage','Expired','Lost') DEFAULT 'Sale',
    stock_out_date DATE,
    remark TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
);

-- ---------------- Stock Adjustment ----------------
CREATE TABLE stock_adjustment (
    adjustment_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    old_qty INT NOT NULL,
    new_qty INT NOT NULL,
    difference INT NOT NULL,
    reason ENUM('Count Difference','Damaged','Expired','Missing','Returned') DEFAULT 'Count Difference',
    adjustment_date DATE,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
);

-- ---------------- Seed default admin (username: admin | password: Admin@123) ----------------
INSERT INTO users (full_name, username, email, password, role, status)
VALUES ('System Admin', 'admin', 'admin@inventory.com',
'$2b$10$VnCPXrhQmW3PaWngOCc/F.rubWx.38j1I3mhjseG4EP3s50eEdj5W', 'Admin', 'Active');
-- Change this password immediately after first login.
