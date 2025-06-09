<?php
require_once '../config/Database.php';
require_once 'ActivityLogController.php';
require_once __DIR__ . '/../vendor/mfa_autoload.php';

use Otp\Otp;
use Otp\GoogleAuthenticator;
use ParagonIE\ConstantTime\Base32;

class MFAController {
    private $conn;
    private $otp;
    private $activityLogger;
    private $maxAttempts = 3;
    private $lockoutTime = 300; // 5 minutes in seconds

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        $this->otp = new Otp();
        $this->activityLogger = new ActivityLogController();
    }

    public function setupMFA($userId) {
        try {
            // Generate a new secret key
            $secret = $this->generateSecret();
            
            // Store the secret in the database
            $query = "UPDATE users SET mfa_secret = :secret WHERE user_id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":secret", $secret);
            $stmt->bindParam(":user_id", $userId);
            
            if ($stmt->execute()) {
                // Generate backup codes
                $backupCodes = $this->generateBackupCodes();
                $this->storeBackupCodes($userId, $backupCodes);
                
                return [
                    'secret' => $secret,
                    'qr_url' => $this->getQRCodeUrl($secret),
                    'backup_codes' => $backupCodes
                ];
            }
            return false;
        } catch (PDOException $e) {
            error_log("MFA setup error: " . $e->getMessage());
            return false;
        }
    }

    public function verifyMFA($userId, $code) {
        try {
            // Check if user is locked out
            if ($this->isLockedOut($userId)) {
                return ['success' => false, 'message' => 'Account temporarily locked. Please try again later.'];
            }

            // Get user's MFA secret
            $stmt = $this->conn->prepare("SELECT mfa_secret, mfa_backup_codes FROM users WHERE user_id = :user_id");
            $stmt->bindParam(":user_id", $userId);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                return ['success' => false, 'message' => 'User not found'];
            }

            // Check if it's a backup code
            if ($this->verifyBackupCode($userId, $code)) {
                $this->logVerificationAttempt($userId, true);
                return ['success' => true, 'message' => 'Backup code accepted'];
            }

            // Get the secret and verify TOTP code
            $mfaSecret = $user['mfa_secret'];
            $decodedSecret = Base32::decodeUpper($mfaSecret);
            $result = $this->otp->checkTotp($decodedSecret, (string)$code);
            
            // Log the attempt
            $this->logVerificationAttempt($userId, $result);

            if (!$result) {
                // Check if we need to lock the account
                if ($this->shouldLockAccount($userId)) {
                    return ['success' => false, 'message' => 'Account locked due to too many failed attempts'];
                }
                return ['success' => false, 'message' => 'Invalid verification code'];
            }

            return ['success' => true, 'message' => 'Code verified successfully'];
        } catch (PDOException $e) {
            error_log("MFA verification error: " . $e->getMessage());
            return ['success' => false, 'message' => 'System error occurred'];
        }
    }

    public function enableMFA($userId, $verificationCode) {
        try {
            // Verify the code first
            $verifyResult = $this->verifyMFA($userId, $verificationCode);
            if (!$verifyResult['success']) {
                return $verifyResult;
            }

            // Enable MFA
            $stmt = $this->conn->prepare("UPDATE users SET mfa_enabled = TRUE WHERE user_id = :user_id");
            $stmt->bindParam(":user_id", $userId);
            
            if ($stmt->execute()) {
                $this->activityLogger->logActivity($userId, 'security', 'MFA enabled');
                return ['success' => true, 'message' => 'MFA enabled successfully'];
            }
            return ['success' => false, 'message' => 'Failed to enable MFA'];
        } catch (PDOException $e) {
            error_log("Enable MFA error: " . $e->getMessage());
            return ['success' => false, 'message' => 'System error occurred'];
        }
    }

    public function disableMFA($userId, $verificationCode) {
        try {
            // Verify the code first
            $verifyResult = $this->verifyMFA($userId, $verificationCode);
            if (!$verifyResult['success']) {
                return $verifyResult;
            }

            // Disable MFA
            $stmt = $this->conn->prepare("UPDATE users SET mfa_enabled = FALSE, mfa_secret = NULL, mfa_backup_codes = NULL WHERE user_id = :user_id");
            $stmt->bindParam(":user_id", $userId);
            
            if ($stmt->execute()) {
                $this->activityLogger->logActivity($userId, 'security', 'MFA disabled');
                return ['success' => true, 'message' => 'MFA disabled successfully'];
            }
            return ['success' => false, 'message' => 'Failed to disable MFA'];
        } catch (PDOException $e) {
            error_log("Disable MFA error: " . $e->getMessage());
            return ['success' => false, 'message' => 'System error occurred'];
        }
    }

    private function generateSecret() {
        return Base32::encodeUpper(random_bytes(20));
    }

    private function getQRCodeUrl($secret) {
        try {
            // Get the username
            $stmt = $this->conn->prepare("SELECT username FROM users WHERE user_id = :user_id");
            $stmt->bindParam(":user_id", $_SESSION['user_id']);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $username = $user ? $user['username'] : 'user';

            $ga = new GoogleAuthenticator();
            return $ga->getQRCodeUrl('Library Management System', $secret);
        } catch (PDOException $e) {
            error_log("Error getting username: " . $e->getMessage());
            return $ga->getQRCodeUrl('Library Management System', $secret);
        }
    }

    private function generateBackupCodes($count = 8) {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = bin2hex(random_bytes(4));
        }
        return $codes;
    }

    private function storeBackupCodes($userId, $codes) {
        $hashedCodes = array_map(function($code) {
            return password_hash($code, PASSWORD_DEFAULT);
        }, $codes);
        
        $stmt = $this->conn->prepare("UPDATE users SET mfa_backup_codes = :codes WHERE user_id = :user_id");
        $stmt->bindParam(":codes", json_encode($hashedCodes));
        $stmt->bindParam(":user_id", $userId);
        return $stmt->execute();
    }

    private function verifyBackupCode($userId, $code) {
        $stmt = $this->conn->prepare("SELECT mfa_backup_codes FROM users WHERE user_id = :user_id");
        $stmt->bindParam(":user_id", $userId);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$result || !$result['mfa_backup_codes']) {
            return false;
        }

        $hashedCodes = json_decode($result['mfa_backup_codes'], true);
        foreach ($hashedCodes as $index => $hashedCode) {
            if (password_verify($code, $hashedCode)) {
                // Remove used backup code
                unset($hashedCodes[$index]);
                $this->updateBackupCodes($userId, array_values($hashedCodes));
                return true;
            }
        }
        return false;
    }

    private function updateBackupCodes($userId, $codes) {
        $stmt = $this->conn->prepare("UPDATE users SET mfa_backup_codes = :codes WHERE user_id = :user_id");
        $stmt->bindParam(":codes", json_encode($codes));
        $stmt->bindParam(":user_id", $userId);
        return $stmt->execute();
    }

    private function logVerificationAttempt($userId, $success) {
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        
        try {
            // Log to MFA verification logs
            $query = "INSERT INTO mfa_verification_logs (user_id, success, ip_address) VALUES (:user_id, :success, :ip_address)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $userId);
            $stmt->bindParam(":success", $success, PDO::PARAM_BOOL);
            $stmt->bindParam(":ip_address", $ipAddress);
            $stmt->execute();

            // Log to activity logs
            $action = $success ? 'mfa_success' : 'mfa_failed';
            $description = $success ? 
                'MFA verification successful' : 
                'MFA verification failed';
            
            $this->activityLogger->logActivity(
                $userId,
                $action,
                $description,
                [
                    'ip_address' => $ipAddress,
                    'type' => 'security'
                ]
            );

            return true;
        } catch (PDOException $e) {
            error_log("MFA verification log error: " . $e->getMessage());
            return false;
        }
    }

    private function isLockedOut($userId) {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) as attempts FROM mfa_verification_logs 
             WHERE user_id = :user_id 
             AND success = FALSE 
             AND verification_time > DATE_SUB(NOW(), INTERVAL :lockout_time SECOND)"
        );
        $stmt->bindParam(":user_id", $userId);
        $stmt->bindParam(":lockout_time", $this->lockoutTime);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['attempts'] >= $this->maxAttempts;
    }

    private function shouldLockAccount($userId) {
        return $this->isLockedOut($userId);
    }

    public function isMFAEnabled($userId) {
        $stmt = $this->conn->prepare("SELECT mfa_enabled FROM users WHERE user_id = :user_id");
        $stmt->bindParam(":user_id", $userId);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result && $result['mfa_enabled'];
    }
} 