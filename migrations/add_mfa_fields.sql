-- Add MFA fields to users table
ALTER TABLE users
ADD COLUMN mfa_secret VARCHAR(32) DEFAULT NULL,
ADD COLUMN mfa_enabled BOOLEAN DEFAULT FALSE,
ADD COLUMN mfa_backup_codes TEXT DEFAULT NULL;

-- Add index for faster MFA lookups
CREATE INDEX idx_mfa_enabled ON users(mfa_enabled);

-- Add MFA verification attempts table for security logging
CREATE TABLE mfa_verification_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    verification_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    success BOOLEAN NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    attempt_count INT DEFAULT 1,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
); 