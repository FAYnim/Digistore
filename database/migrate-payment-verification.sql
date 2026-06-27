ALTER TABLE payment_confirmations
  ADD COLUMN IF NOT EXISTS verification_status ENUM('pending', 'accepted', 'rejected', 'retry_requested') DEFAULT 'pending' AFTER proof_path,
  ADD COLUMN IF NOT EXISTS admin_note TEXT DEFAULT NULL AFTER verification_status,
  ADD COLUMN IF NOT EXISTS verified_by INT DEFAULT NULL AFTER admin_note,
  ADD COLUMN IF NOT EXISTS verified_at DATETIME DEFAULT NULL AFTER verified_by;
