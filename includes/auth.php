<?php
class Auth {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function login($email, $password) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                return [
                    'success' => true,
                    'user' => [
                        'id' => $user['id'],
                        'name' => $user['name'],
                        'email' => $user['email'],
                        'role' => $user['role']
                    ]
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'E-posta veya şifre hatalı.'
                ];
            }
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Giriş sırasında bir hata oluştu.'
            ];
        }
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return null;
        }
    }
    
    public function hasRole($role) {
        if (!$this->isLoggedIn()) {
            return false;
        }
        
        return $_SESSION['user_role'] === $role;
    }
    
    public function hasAnyRole($roles) {
        if (!$this->isLoggedIn()) {
            return false;
        }
        
        return in_array($_SESSION['user_role'], $roles);
    }
    
    public function requireRole($role) {
        if (!$this->hasRole($role)) {
            // Mevcut dizini kontrol et
            $currentPath = $_SERVER['PHP_SELF'];
            if (strpos($currentPath, '/admin/') !== false) {
                header('Location: ../login.php');
            } elseif (strpos($currentPath, '/manager/') !== false) {
                header('Location: ../login.php');
            } elseif (strpos($currentPath, '/user/') !== false) {
                header('Location: ../login.php');
            } elseif (strpos($currentPath, '/customer/') !== false) {
                header('Location: ../login.php');
            } else {
                header('Location: login.php');
            }
            exit();
        }
    }
    
    public function requireAnyRole($roles) {
        if (!$this->hasAnyRole($roles)) {
            // Mevcut dizini kontrol et
            $currentPath = $_SERVER['PHP_SELF'];
            if (strpos($currentPath, '/admin/') !== false) {
                header('Location: ../login.php');
            } elseif (strpos($currentPath, '/manager/') !== false) {
                header('Location: ../login.php');
            } elseif (strpos($currentPath, '/user/') !== false) {
                header('Location: ../login.php');
            } elseif (strpos($currentPath, '/customer/') !== false) {
                header('Location: ../login.php');
            } else {
                header('Location: login.php');
            }
            exit();
        }
    }
    
    public function logout() {
        session_destroy();
        header('Location: ../index.php');
        exit();
    }
}

// Yardımcı fonksiyonlar
function showToast($message, $type = 'success') {
    $_SESSION['toast'] = [
        'message' => $message,
        'type' => $type
    ];
}

function getToast() {
    if (isset($_SESSION['toast'])) {
        $toast = $_SESSION['toast'];
        unset($_SESSION['toast']);
        return $toast;
    }
    return null;
} 