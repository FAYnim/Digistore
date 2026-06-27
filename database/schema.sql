-- ============================================================
-- Schema Database: digital_store
-- Versi: 1.0
-- Untuk: Dashboard Admin DigiStore
-- ============================================================

CREATE DATABASE IF NOT EXISTS digital_store
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE digital_store;

-- ------------------------------------------------------------
-- 1. admin_users
-- ------------------------------------------------------------
CREATE TABLE admin_users (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  username    VARCHAR(100) NOT NULL UNIQUE,
  password    VARCHAR(255) NOT NULL,
  name        VARCHAR(100) NOT NULL,
  status        ENUM('active', 'inactive') DEFAULT 'active',
  last_login_at DATETIME DEFAULT NULL,
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- 2. categories
-- ------------------------------------------------------------
CREATE TABLE categories (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(100) NOT NULL,
  slug        VARCHAR(120) NOT NULL UNIQUE,
  icon        VARCHAR(100) DEFAULT NULL,
  status      ENUM('active', 'inactive') DEFAULT 'active',
  sort_order  INT DEFAULT 0,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- 3. products
-- ------------------------------------------------------------
CREATE TABLE products (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  category_id    INT NULL,
  name           VARCHAR(150) NOT NULL,
  slug           VARCHAR(180) NOT NULL UNIQUE,
  description    TEXT,
  price          INT NOT NULL DEFAULT 0,
  original_price INT DEFAULT NULL,
  stock          INT NOT NULL DEFAULT 0,
  image_url      VARCHAR(255) DEFAULT NULL,
  badge          VARCHAR(50) DEFAULT NULL,
  status         ENUM('active', 'draft', 'out_of_stock') DEFAULT 'draft',
  is_featured    TINYINT(1) DEFAULT 0,
  sold_count     INT DEFAULT 0,
  rating         DECIMAL(2,1) DEFAULT 0.0,
  created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  CONSTRAINT fk_products_category
    FOREIGN KEY (category_id) REFERENCES categories(id)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- 4. orders
-- ------------------------------------------------------------
CREATE TABLE orders (
  id               INT AUTO_INCREMENT PRIMARY KEY,
  order_code       VARCHAR(50) NOT NULL UNIQUE,
  customer_name    VARCHAR(100) NOT NULL,
  customer_email   VARCHAR(150) DEFAULT NULL,
  customer_phone   VARCHAR(30) DEFAULT NULL,
  total_amount     INT NOT NULL DEFAULT 0,
  payment_method   VARCHAR(50) DEFAULT NULL,
  payment_deadline DATETIME DEFAULT NULL,
  status           ENUM('pending', 'pending_payment', 'paid', 'processing', 'delivered', 'completed', 'expired', 'cancelled') DEFAULT 'pending_payment',
  note             TEXT DEFAULT NULL,
  delivery_note    TEXT DEFAULT NULL,
  created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- 5. order_items
-- ------------------------------------------------------------
CREATE TABLE order_items (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  order_id     INT NOT NULL,
  product_id   INT NULL,
  product_name VARCHAR(150) NOT NULL,
  quantity     INT NOT NULL DEFAULT 1,
  price        INT NOT NULL DEFAULT 0,
  subtotal     INT NOT NULL DEFAULT 0,
  created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  CONSTRAINT fk_order_items_order
    FOREIGN KEY (order_id) REFERENCES orders(id)
    ON DELETE CASCADE,

  CONSTRAINT fk_order_items_product
    FOREIGN KEY (product_id) REFERENCES products(id)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- 6. testimonials
-- ------------------------------------------------------------
CREATE TABLE testimonials (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(100) NOT NULL,
  role       VARCHAR(100) DEFAULT NULL,
  message    TEXT NOT NULL,
  rating     TINYINT DEFAULT 5,
  status     ENUM('visible', 'hidden') DEFAULT 'visible',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- 7. payment_confirmations
-- ------------------------------------------------------------
CREATE TABLE payment_confirmations (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  order_id       INT NOT NULL,
  sender_name    VARCHAR(100) NOT NULL,
  payment_method VARCHAR(50) NOT NULL,
  note           TEXT DEFAULT NULL,
  proof_path     VARCHAR(255) NOT NULL,
  verification_status ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending',
  admin_note     TEXT DEFAULT NULL,
  verified_by    INT DEFAULT NULL,
  verified_at    DATETIME DEFAULT NULL,
  created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  CONSTRAINT fk_payment_confirmations_order
    FOREIGN KEY (order_id) REFERENCES orders(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- 8. store_settings
-- ------------------------------------------------------------
CREATE TABLE store_settings (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  setting_key   VARCHAR(100) NOT NULL UNIQUE,
  setting_value TEXT DEFAULT NULL,
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
