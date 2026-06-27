ALTER TABLE payment_confirmations
  ADD COLUMN IF NOT EXISTS verification_status ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending' AFTER proof_path,
  ADD COLUMN IF NOT EXISTS admin_note TEXT DEFAULT NULL AFTER verification_status,
  ADD COLUMN IF NOT EXISTS verified_by INT DEFAULT NULL AFTER admin_note,
  ADD COLUMN IF NOT EXISTS verified_at DATETIME DEFAULT NULL AFTER verified_by;

UPDATE payment_confirmations SET verification_status = 'rejected' WHERE verification_status = 'retry_requested';

ALTER TABLE payment_confirmations
  MODIFY verification_status ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending';
