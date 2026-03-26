DROP DATABASE IF EXISTS bikedb;
CREATE DATABASE bikedb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bikedb;

SET FOREIGN_KEY_CHECKS = 0;

-- ================= ADMINS =================
CREATE TABLE admins (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ================= CUSTOMERS =================
CREATE TABLE customers (
    customer_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(15) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    address TEXT,
    profile_picture VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ================= GST SETTINGS =================
CREATE TABLE gst_settings (
    gst_id INT AUTO_INCREMENT PRIMARY KEY,
    cgst_percent DECIMAL(5,2) DEFAULT 9.00,
    sgst_percent DECIMAL(5,2) DEFAULT 9.00,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO gst_settings(cgst_percent, sgst_percent) VALUES (9.00, 9.00);

-- ================= CATEGORIES =================
CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- ================= PRODUCTS =================
CREATE TABLE products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    product_name VARCHAR(150) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    stock_quantity INT NOT NULL DEFAULT 0,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_products_name(product_name),

    FOREIGN KEY (category_id)
        REFERENCES categories(category_id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ================= MANUFACTURERS =================
CREATE TABLE manufacturers (
    manufacturer_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    company_name VARCHAR(150) NOT NULL,
    phone VARCHAR(15) NOT NULL,
    email VARCHAR(150),
    gst_number VARCHAR(50) UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ================= PURCHASES =================
CREATE TABLE purchases (
    purchase_id INT AUTO_INCREMENT PRIMARY KEY,
    manufacturer_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    purchase_price DECIMAL(10,2) NOT NULL,
    total_amount DECIMAL(12,2),
    request_status ENUM('REQUESTED','ACCEPTED','DECLINED','COMPLETED') DEFAULT 'REQUESTED',
    manufacturer_response_date DATETIME,
    admin_notes TEXT,
    payment_status VARCHAR(50) DEFAULT 'Pending',
    purchase_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_purchase_date(purchase_date),
    INDEX idx_purchase_manufacturer(manufacturer_id, purchase_date),

    FOREIGN KEY (manufacturer_id) REFERENCES manufacturers(manufacturer_id),
    FOREIGN KEY (product_id) REFERENCES products(product_id)
) ENGINE=InnoDB;

-- ================= ORDERS =================
CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    order_status VARCHAR(50) DEFAULT 'Pending',
    payment_status VARCHAR(50) DEFAULT 'Pending',
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_orders_status(order_status),

    FOREIGN KEY (customer_id) REFERENCES customers(customer_id)
) ENGINE=InnoDB;

-- ================= ORDER ITEMS =================
CREATE TABLE order_items (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,

    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id)
) ENGINE=InnoDB;

-- ================= SERVICES =================
CREATE TABLE services (
    service_id INT AUTO_INCREMENT PRIMARY KEY,
    service_name VARCHAR(150) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL
) ENGINE=InnoDB;

-- ================= SERVICE BOOKINGS =================
CREATE TABLE service_bookings (
    booking_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    service_id INT NOT NULL,
    booking_date DATE NOT NULL,
    booking_time TIME NOT NULL,
    status VARCHAR(50) DEFAULT 'Pending',

    FOREIGN KEY (customer_id) REFERENCES customers(customer_id),
    FOREIGN KEY (service_id) REFERENCES services(service_id)
) ENGINE=InnoDB;

-- ================= AMC CONTRACTS =================
CREATE TABLE amc_contracts (
    amc_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    service_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status VARCHAR(50) DEFAULT 'Active',

    FOREIGN KEY (customer_id) REFERENCES customers(customer_id),
    FOREIGN KEY (service_id) REFERENCES services(service_id)
) ENGINE=InnoDB;

-- ================= WARRANTIES =================
CREATE TABLE warranties (
    warranty_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    product_id INT NOT NULL,
    warranty_start DATE NOT NULL,
    warranty_end DATE NOT NULL,
    status VARCHAR(50) DEFAULT 'Active',

    FOREIGN KEY (customer_id) REFERENCES customers(customer_id),
    FOREIGN KEY (product_id) REFERENCES products(product_id)
) ENGINE=InnoDB;

-- ================= NOTIFICATIONS =================
CREATE TABLE notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    message TEXT NOT NULL,
    type VARCHAR(50) NOT NULL,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ================= PASSWORD RESETS =================
CREATE TABLE password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255),
    otp VARCHAR(6),
    expires_at DATETIME
) ENGINE=InnoDB;

-- ================= ADMIN WALLET =================
CREATE TABLE admin_wallet (
    wallet_id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    total_investment DECIMAL(12,2) DEFAULT 0,
    total_sales DECIMAL(12,2) DEFAULT 0,
    total_expense DECIMAL(12,2) DEFAULT 0,
    total_profit DECIMAL(12,2) DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (admin_id) REFERENCES admins(admin_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ================= WALLET TRANSACTIONS =================
CREATE TABLE wallet_transactions (
    transaction_id INT AUTO_INCREMENT PRIMARY KEY,
    wallet_id INT NOT NULL,
    type ENUM('INVESTMENT','SALE','EXPENSE') NOT NULL,
    reference_id INT,
    amount DECIMAL(12,2) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_wallet_type(type),

    FOREIGN KEY (wallet_id) REFERENCES admin_wallet(wallet_id) ON DELETE CASCADE
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;
