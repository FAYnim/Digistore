ALTER TABLE orders
  ADD INDEX idx_orders_status_payment_deadline (status, payment_deadline);
