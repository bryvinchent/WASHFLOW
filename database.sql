CREATE DATABASE IF NOT EXISTS washflow CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE washflow;

CREATE TABLE outlets (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  city VARCHAR(100) NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE users (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(60) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','outlet') NOT NULL,
  outlet_id BIGINT UNSIGNED NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_users_outlet FOREIGN KEY (outlet_id) REFERENCES outlets(id),
  INDEX idx_users_outlet (outlet_id)
) ENGINE=InnoDB;

CREATE TABLE transactions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  outlet_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NOT NULL,
  type ENUM('income','expense') NOT NULL,
  amount DECIMAL(14,2) NOT NULL,
  note VARCHAR(250) NULL,
  transaction_date DATE NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_transactions_outlet FOREIGN KEY (outlet_id) REFERENCES outlets(id),
  CONSTRAINT fk_transactions_user FOREIGN KEY (user_id) REFERENCES users(id),
  INDEX idx_transaction_date (transaction_date),
  INDEX idx_transaction_outlet_date (outlet_id, transaction_date),
  INDEX idx_transaction_type_date (type, transaction_date)
) ENGINE=InnoDB;
