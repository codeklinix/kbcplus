<?php
require_once 'config.php';
session_start();

class Auth {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    // Login user
    public function login($username, $password) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT id, username, email, password_hash, role, is_active 
                FROM users 
                WHERE (username = ? OR email = ?) AND is_active = 1
            ");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password_hash'])) {
                // Update last login
                $updateLogin = $this->pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $updateLogin->execute([$user['id']]);
                
                // Create session
                $this->createSession($user);
                
                // Log the login
                $this->logAction($user['id'], 'login', null, null, 'User logged in successfully');
                
                return [
                    'success' => true,
                    'user' => [
                        'id' => $user['id'],
                        'username' => $user['username'],
                        'email' => $user['email'],
                        'role' => $user['role']
                    ],
                    'message' => 'Login successful'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Invalid username/email or password'
                ];
            }
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }
    
    // Create user session
    private function createSession($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();
        
        // Create session token for API access
        $sessionToken = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        $stmt = $this->pdo->prepare("
            INSERT INTO user_sessions (user_id, session_token, expires_at, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $user['id'],
            $sessionToken,
            $expiresAt,
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
        
        $_SESSION['session_token'] = $sessionToken;
    }
    
    // Check if user is logged in
    public function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    // Check if user has specific role
    public function hasRole($role) {
        return $this->isLoggedIn() && $_SESSION['role'] === $role;
    }
    
    // Check if user is admin
    public function isAdmin() {
        return $this->hasRole('admin');
    }
    
    // Logout user
    public function logout() {
        if (isset($_SESSION['session_token'])) {
            // Delete session from database
            $stmt = $this->pdo->prepare("DELETE FROM user_sessions WHERE session_token = ?");
            $stmt->execute([$_SESSION['session_token']]);
        }
        
        if (isset($_SESSION['user_id'])) {
            $this->logAction($_SESSION['user_id'], 'logout', null, null, 'User logged out');
        }
        
        // Destroy session
        session_destroy();
        session_start();
        
        return ['success' => true, 'message' => 'Logged out successfully'];
    }
    
    // Get current user info
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'email' => $_SESSION['email'],
            'role' => $_SESSION['role']
        ];
    }
    
    // Register new user
    public function register($username, $email, $password, $role = 'user') {
        try {
            // Check if username or email already exists
            $checkStmt = $this->pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $checkStmt->execute([$username, $email]);
            
            if ($checkStmt->fetch()) {
                return [
                    'success' => false,
                    'message' => 'Username or email already exists'
                ];
            }
            
            // Hash password
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new user
            $stmt = $this->pdo->prepare("
                INSERT INTO users (username, email, password_hash, role) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$username, $email, $passwordHash, $role]);
            
            $userId = $this->pdo->lastInsertId();
            
            // Create default preferences
            $prefsStmt = $this->pdo->prepare("
                INSERT INTO user_preferences (user_id, theme, language) 
                VALUES (?, 'glassmorphic', 'en')
            ");
            $prefsStmt->execute([$userId]);
            
            return [
                'success' => true,
                'message' => 'User registered successfully',
                'user_id' => $userId
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }
    
    // Change password
    public function changePassword($userId, $currentPassword, $newPassword) {
        try {
            // Verify current password
            $stmt = $this->pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            
            if (!$user || !password_verify($currentPassword, $user['password_hash'])) {
                return [
                    'success' => false,
                    'message' => 'Current password is incorrect'
                ];
            }
            
            // Update password
            $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $updateStmt = $this->pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $updateStmt->execute([$newPasswordHash, $userId]);
            
            $this->logAction($userId, 'password_change', 'user', $userId, 'Password changed successfully');
            
            return [
                'success' => true,
                'message' => 'Password changed successfully'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }
    
    // Log admin actions
    public function logAction($userId, $action, $targetType = null, $targetId = null, $details = null) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO admin_logs (user_id, action, target_type, target_id, details, ip_address) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $userId,
                $action,
                $targetType,
                $targetId,
                $details,
                $_SERVER['REMOTE_ADDR'] ?? ''
            ]);
        } catch (PDOException $e) {
            // Log errors silently to avoid breaking the main functionality
            error_log("Failed to log action: " . $e->getMessage());
        }
    }
    
    // Clean expired sessions
    public function cleanExpiredSessions() {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM user_sessions WHERE expires_at < NOW()");
            $stmt->execute();
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("Failed to clean expired sessions: " . $e->getMessage());
            return 0;
        }
    }
    
    // Require admin access (for protected pages)
    public function requireAdmin() {
        if (!$this->isAdmin()) {
            header('HTTP/1.1 403 Forbidden');
            header('Location: /streaming/login.html?error=access_denied');
            exit;
        }
    }
    
    // Require login (for protected pages)
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('HTTP/1.1 401 Unauthorized');
            header('Location: /streaming/login.html?error=login_required');
            exit;
        }
    }
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $auth = new Auth($pdo);
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'login':
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            echo json_encode($auth->login($username, $password));
            break;
            
        case 'logout':
            echo json_encode($auth->logout());
            break;
            
        case 'register':
            if (!$auth->isAdmin()) {
                echo json_encode(['success' => false, 'message' => 'Admin access required']);
                break;
            }
            $username = $_POST['username'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? 'user';
            echo json_encode($auth->register($username, $email, $password, $role));
            break;
            
        case 'change_password':
            if (!$auth->isLoggedIn()) {
                echo json_encode(['success' => false, 'message' => 'Login required']);
                break;
            }
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            echo json_encode($auth->changePassword($_SESSION['user_id'], $currentPassword, $newPassword));
            break;
            
        case 'get_current_user':
            $user = $auth->getCurrentUser();
            if ($user) {
                echo json_encode(['success' => true, 'user' => $user]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Not logged in']);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    exit;
}

// Create global auth instance
$auth = new Auth($pdo);

// Clean expired sessions periodically (1% chance per request)
if (rand(1, 100) === 1) {
    $auth->cleanExpiredSessions();
}
?>
