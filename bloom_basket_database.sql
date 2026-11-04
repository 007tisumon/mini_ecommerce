-- Bloom Basket database | MySQL 8.4+
-- WARNING: This script drops and recreates the Bloom Basket tables.
-- Demo account passwords are placeholders (CHANGE_ME), not login-ready hashes.

CREATE DATABASE IF NOT EXISTS bloom_basket
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bloom_basket;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS order_status_history;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS addresses;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(100) NOT NULL,
 email VARCHAR(150) NOT NULL UNIQUE,
 password VARCHAR(255) NOT NULL,
 phone VARCHAR(20),
 role ENUM('admin','customer') NOT NULL DEFAULT 'customer',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE addresses (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id BIGINT UNSIGNED NOT NULL,
 recipient_name VARCHAR(100) NOT NULL,
 phone VARCHAR(20) NOT NULL,
 address_line VARCHAR(255) NOT NULL,
 area VARCHAR(100),
 city VARCHAR(100) NOT NULL,
 postal_code VARCHAR(20),
 is_default BOOLEAN NOT NULL DEFAULT FALSE,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_addresses_user FOREIGN KEY (user_id)
 REFERENCES users(id) ON DELETE CASCADE,
 INDEX idx_addresses_user (user_id)
) ENGINE=InnoDB;

CREATE TABLE categories (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(100) NOT NULL UNIQUE,
 slug VARCHAR(120) NOT NULL UNIQUE,
 description TEXT,
 is_active BOOLEAN NOT NULL DEFAULT TRUE,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE products (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 category_id BIGINT UNSIGNED NOT NULL,
 sku VARCHAR(50) NOT NULL UNIQUE,
 name VARCHAR(150) NOT NULL,
 slug VARCHAR(170) NOT NULL UNIQUE,
 description TEXT,
 price DECIMAL(10,2) NOT NULL,
 stock INT UNSIGNED NOT NULL DEFAULT 0,
 image_path VARCHAR(255),
 is_active BOOLEAN NOT NULL DEFAULT TRUE,
 is_featured BOOLEAN NOT NULL DEFAULT FALSE,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 CONSTRAINT fk_products_category FOREIGN KEY (category_id)
 REFERENCES categories(id) ON DELETE RESTRICT,
 INDEX idx_products_name (name),
 INDEX idx_products_category (category_id),
 INDEX idx_products_active (is_active)
) ENGINE=InnoDB;

CREATE TABLE orders (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id BIGINT UNSIGNED NOT NULL,
 order_number VARCHAR(50) NOT NULL UNIQUE,
 recipient_name VARCHAR(100) NOT NULL,
 phone VARCHAR(20) NOT NULL,
 shipping_address VARCHAR(255) NOT NULL,
 area VARCHAR(100),
 city VARCHAR(100) NOT NULL,
 postal_code VARCHAR(20),
 subtotal DECIMAL(10,2) NOT NULL,
 delivery_fee DECIMAL(10,2) NOT NULL DEFAULT 0.00,
 total_amount DECIMAL(10,2) NOT NULL,
 status ENUM('pending','confirmed','processing','shipped','delivered','cancelled')
 NOT NULL DEFAULT 'pending',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 CONSTRAINT fk_orders_user FOREIGN KEY (user_id)
 REFERENCES users(id) ON DELETE RESTRICT,
 INDEX idx_orders_status (status),
 INDEX idx_orders_created (created_at),
 INDEX idx_orders_user (user_id)
) ENGINE=InnoDB;

CREATE TABLE order_items (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 order_id BIGINT UNSIGNED NOT NULL,
 product_id BIGINT UNSIGNED NULL,
 product_name VARCHAR(150) NOT NULL,
 unit_price DECIMAL(10,2) NOT NULL,
 quantity INT UNSIGNED NOT NULL,
 line_total DECIMAL(10,2) NOT NULL,
 CONSTRAINT fk_order_items_order FOREIGN KEY (order_id)
 REFERENCES orders(id) ON DELETE CASCADE,
 CONSTRAINT fk_order_items_product FOREIGN KEY (product_id)
 REFERENCES products(id) ON DELETE SET NULL,
 INDEX idx_order_items_order (order_id)
) ENGINE=InnoDB;

CREATE TABLE payments (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 order_id BIGINT UNSIGNED NOT NULL,
 payment_method ENUM('bkash','nagad','rocket','cod') NOT NULL,
 transaction_id VARCHAR(100) UNIQUE NULL,
 amount DECIMAL(10,2) NOT NULL,
 status ENUM('pending','success','failed') NOT NULL DEFAULT 'pending',
 paid_at DATETIME NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_payments_order FOREIGN KEY (order_id)
 REFERENCES orders(id) ON DELETE RESTRICT,
 INDEX idx_payments_order (order_id),
 INDEX idx_payments_status (status)
) ENGINE=InnoDB;

CREATE TABLE order_status_history (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 order_id BIGINT UNSIGNED NOT NULL,
 changed_by BIGINT UNSIGNED NULL,
 old_status VARCHAR(30) NULL,
 new_status VARCHAR(30) NOT NULL,
 note TEXT,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_history_order FOREIGN KEY (order_id)
 REFERENCES orders(id) ON DELETE CASCADE,
 CONSTRAINT fk_history_user FOREIGN KEY (changed_by)
 REFERENCES users(id) ON DELETE SET NULL,
 INDEX idx_history_order (order_id)
) ENGINE=InnoDB;

-- DUMMY DATA

INSERT INTO users (name,email,password,phone,role) VALUES
('Bloom Basket Admin','admin@bloombasket.com','CHANGE_ME','01710000001','admin'),
('Rahim Ahmed','rahim@gmail.com','CHANGE_ME','01710000002','customer'),
('Karim Hasan','karim@gmail.com','CHANGE_ME','01710000003','customer'),
('Nusrat Jahan','nusrat@gmail.com','CHANGE_ME','01710000004','customer');

INSERT INTO addresses
(user_id,recipient_name,phone,address_line,area,city,postal_code,is_default) VALUES
(2,'Rahim Ahmed','01710000002','House 12, Road 5','Mirpur','Dhaka','1216',TRUE),
(2,'Rahim Ahmed','01710000002','House 20, Road 2','Dhanmondi','Dhaka','1205',FALSE),
(3,'Karim Hasan','01710000003','House 15, Road 7','Uttara','Dhaka','1230',TRUE),
(4,'Nusrat Jahan','01710000004','House 8, Road 3','Banani','Dhaka','1213',TRUE);

INSERT INTO categories (name,slug,description) VALUES
('Roses','roses','Beautiful fresh roses for every occasion.'),
('Bouquets','bouquets','Specially arranged flower bouquets.'),
('Wedding Flowers','wedding-flowers','Flowers for weddings and ceremonies.'),
('Indoor Plants','indoor-plants','Beautiful plants for home decoration.'),
('Gift Flowers','gift-flowers','Special flower gifts for loved ones.');

INSERT INTO products
(category_id,sku,name,slug,description,price,stock,image_path,is_active,is_featured) VALUES
(1,'ROS-001','Red Rose','red-rose','Beautiful fresh red rose.',150.00,100,'products/red-rose.jpg',TRUE,TRUE),
(1,'ROS-002','White Rose','white-rose','Elegant white rose.',160.00,80,'products/white-rose.jpg',TRUE,FALSE),
(1,'ROS-003','Pink Rose','pink-rose','Lovely pink rose.',170.00,70,'products/pink-rose.jpg',TRUE,TRUE),
(2,'BOU-001','Romantic Rose Bouquet','romantic-rose-bouquet','A romantic bouquet of fresh roses.',1200.00,25,'products/romantic-bouquet.jpg',TRUE,TRUE),
(2,'BOU-002','Mixed Flower Bouquet','mixed-flower-bouquet','Colorful mixed flower bouquet.',1500.00,20,'products/mixed-bouquet.jpg',TRUE,TRUE),
(2,'BOU-003','Premium Flower Bouquet','premium-flower-bouquet','Premium collection of fresh flowers.',2200.00,15,'products/premium-bouquet.jpg',TRUE,FALSE),
(3,'WED-001','Wedding Rose Set','wedding-rose-set','Beautiful roses for wedding decoration.',5000.00,10,'products/wedding-rose.jpg',TRUE,TRUE),
(3,'WED-002','Wedding Flower Garland','wedding-flower-garland','Traditional flower garland.',1800.00,30,'products/wedding-garland.jpg',TRUE,FALSE),
(4,'PLT-001','Peace Lily','peace-lily','Beautiful indoor peace lily plant.',850.00,35,'products/peace-lily.jpg',TRUE,TRUE),
(4,'PLT-002','Snake Plant','snake-plant','Low maintenance indoor plant.',650.00,40,'products/snake-plant.jpg',TRUE,FALSE),
(5,'GFT-001','Birthday Flower Box','birthday-flower-box','Special flower box for birthdays.',1800.00,20,'products/birthday-box.jpg',TRUE,TRUE),
(5,'GFT-002','Anniversary Flower Box','anniversary-flower-box','Beautiful anniversary flower gift.',2500.00,12,'products/anniversary-box.jpg',TRUE,FALSE);

INSERT INTO orders
(user_id,order_number,recipient_name,phone,shipping_address,area,city,postal_code,
 subtotal,delivery_fee,total_amount,status) VALUES
(2,'BB-2026-0001','Rahim Ahmed','01710000002','House 12, Road 5','Mirpur','Dhaka','1216',1200.00,80.00,1280.00,'delivered'),
(3,'BB-2026-0002','Karim Hasan','01710000003','House 15, Road 7','Uttara','Dhaka','1230',1500.00,100.00,1600.00,'processing'),
(4,'BB-2026-0003','Nusrat Jahan','01710000004','House 8, Road 3','Banani','Dhaka','1213',2200.00,80.00,2280.00,'pending');

INSERT INTO order_items (order_id,product_id,product_name,unit_price,quantity,line_total) VALUES
(1,4,'Romantic Rose Bouquet',1200.00,1,1200.00),
(2,5,'Mixed Flower Bouquet',1500.00,1,1500.00),
(3,6,'Premium Flower Bouquet',2200.00,1,2200.00);

INSERT INTO payments (order_id,payment_method,transaction_id,amount,status,paid_at) VALUES
(1,'bkash','BKASH-DEMO-10001',1280.00,'success',NOW()),
(2,'nagad','NAGAD-DEMO-10002',1600.00,'success',NOW()),
(3,'cod',NULL,2280.00,'pending',NULL);

INSERT INTO order_status_history (order_id,changed_by,old_status,new_status,note) VALUES
(1,1,NULL,'pending','Order placed by customer.'),
(1,1,'pending','confirmed','Admin confirmed the order.'),
(1,1,'confirmed','processing','Preparing the flowers.'),
(1,1,'processing','shipped','Order handed to delivery service.'),
(1,1,'shipped','delivered','Order successfully delivered.'),
(2,1,NULL,'pending','Order placed by customer.'),
(2,1,'pending','confirmed','Admin confirmed the order.'),
(2,1,'confirmed','processing','Order is being prepared.'),
(3,1,NULL,'pending','New order received.');

-- Verify inserted sample records
SELECT 'users' AS table_name, COUNT(*) AS total_rows FROM users
UNION ALL SELECT 'addresses', COUNT(*) FROM addresses
UNION ALL SELECT 'categories', COUNT(*) FROM categories
UNION ALL SELECT 'products', COUNT(*) FROM products
UNION ALL SELECT 'orders', COUNT(*) FROM orders
UNION ALL SELECT 'order_items', COUNT(*) FROM order_items
UNION ALL SELECT 'payments', COUNT(*) FROM payments
UNION ALL SELECT 'order_status_history', COUNT(*) FROM order_status_history;
