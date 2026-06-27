CREATE TABLE IF NOT EXISTS payment_confirmations (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  order_id       INT NOT NULL,
  sender_name    VARCHAR(100) NOT NULL,
  payment_method VARCHAR(50) NOT NULL,
  paid_at        DATETIME NOT NULL,
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
