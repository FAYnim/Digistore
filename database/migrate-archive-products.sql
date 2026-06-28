ALTER TABLE products
  ADD COLUMN archived_at TIMESTAMP NULL DEFAULT NULL AFTER rating,
  ADD INDEX idx_products_archived_status (archived_at, status);
