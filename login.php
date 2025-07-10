<?php
// Türkiye saat dilimi ayarı
date_default_timezone_set('Europe/Istanbul');
ini_set('display_errors', 1);
error_reporting(E_ALL);
// Session ayarlarını güncelle
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
session_start();
// Eski session'ı temizle
if (isset($_GET['clear'])) {
    session_destroy();
    header('Location: login.php');
    exit();
}
if (!file_exists(__DIR__ . '/config/database.php')) {
    header('Location: setup/index.php');
    exit();
}
require_once 'config/database.php';
require_once 'includes/auth.php';
// Kullanıcı zaten giriş yapmışsa rolüne göre yönlendir
if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['user_role'];
    switch ($role) {
        case 'admin':
            header('Location: admin/dashboard.php');
            break;
        case 'manager':
            header('Location: manager/dashboard.php');
            break;
        case 'user':
            header('Location: user/dashboard.php');
            break;
        case 'customer':
            header('Location: customer/dashboard.php');
            break;
        default:
            header('Location: index.php');
    }
    exit();
}
$error = '';
if ($_POST) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    if (empty($email) || empty($password)) {
        $error = 'Lütfen tüm alanları doldurun.';
    } else {
        $auth = new Auth($pdo);
        $result = $auth->login($email, $password);
        if ($result['success']) {
            // Session'ı set et
            $_SESSION['user_id'] = $result['user']['id'];
            $_SESSION['user_name'] = $result['user']['name'];
            $_SESSION['user_role'] = $result['user']['role'];
            // Rol bazlı yönlendirme
            switch ($result['user']['role']) {
                case 'admin':
                    header('Location: admin/dashboard.php');
                    break;
                case 'manager':
                    header('Location: manager/dashboard.php');
                    break;
                case 'user':
                    header('Location: user/dashboard.php');
                    break;
                case 'customer':
                    header('Location: customer/dashboard.php');
                    break;
            }
            exit();
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>İş Yönetim Paneli - Giriş</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            overflow: hidden;
            max-width: 400px;
            width: 100%;
        }
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .login-body {
            padding: 40px;
        }
        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            width: 100%;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <h3><i class="fas fa-briefcase me-2"></i>İş Yönetim Paneli</h3>
            <p class="mb-0">Hesabınıza giriş yapın</p>
        </div>
        <div class="login-body">
            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <label for="email" class="form-label">E-posta</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label">Şifre</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-login">
                    <i class="fas fa-sign-in-alt me-2"></i>Giriş Yap
                </button>
            </form>
            <div class="text-center mt-4">
                <small class="text-muted">
                    Demo hesap: admin@demo.com / 123456
                </small>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 