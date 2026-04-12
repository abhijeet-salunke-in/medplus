CREATE DATABASE IF NOT EXISTS medpro_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE medpro_db;

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  email VARCHAR(255) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role VARCHAR(20) NOT NULL,
  phone VARCHAR(30) NOT NULL,
  license_no VARCHAR(100) NULL,
  address VARCHAR(255) NULL,
  status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_users_email (email),
  KEY idx_users_role_status (role, status)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS products (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  store_id INT UNSIGNED NOT NULL,
  product_name VARCHAR(200) NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  image_path VARCHAR(255) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_products_store
    FOREIGN KEY (store_id) REFERENCES users(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  KEY idx_products_store (store_id)
) ENGINE=InnoDB;

