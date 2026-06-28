UPDATE orders
SET status = CASE
  WHEN status IN ('pending', 'pending_payment') THEN 'pending_payment'
  WHEN status IN ('paid', 'processing', 'delivered', 'completed') THEN 'completed'
  WHEN status = 'cancelled' THEN 'cancelled'
  ELSE 'expired'
END;

UPDATE orders o
JOIN payment_confirmations pc ON pc.order_id = o.id AND pc.verification_status = 'pending'
SET o.status = 'pending_verify'
WHERE o.status IN ('pending_payment', 'completed');

ALTER TABLE orders
  MODIFY status ENUM('pending_payment', 'pending_verify', 'completed', 'expired', 'cancelled') DEFAULT 'pending_payment';
