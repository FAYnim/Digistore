ALTER TABLE orders
  MODIFY status ENUM('pending', 'pending_payment', 'paid', 'processing', 'delivered', 'completed', 'expired', 'cancelled') DEFAULT 'pending_payment';

UPDATE orders
SET status = 'pending_payment'
WHERE status = 'pending';
