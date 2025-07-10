<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
// Türkiye saat dilimi ayarı
date_default_timezone_set('Europe/Istanbul');

session_start();
require_once 'config/database.php';
require_once 'includes/auth.php';

if (!file_exists(__DIR__ . '/config/database.php')) {
    header('Location: setup/index.php');
    exit();
}

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
            header('Location: login.php');
    }
    exit();
}

// Giriş yapmamış kullanıcıları login sayfasına yönlendir
header('Location: login.php');
exit(); 