<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/database.php';
require_once '../includes/auth.php';

$auth = new Auth($pdo);
$currentUser = $auth->getCurrentUser();
$userRole = $_SESSION['user_role'] ?? '';

// Rol bazlı menü öğeleri
function getMenuItems($role) {
    $menuItems = [];
    
    switch ($role) {
        case 'admin':
            $menuItems = [
                ['icon' => 'fas fa-tachometer-alt', 'text' => 'Dashboard', 'url' => 'dashboard.php'],
                ['icon' => 'fas fa-users', 'text' => 'Kullanıcı Yönetimi', 'url' => 'users.php'],
                ['icon' => 'fas fa-building', 'text' => 'Firma Yönetimi', 'url' => 'companies.php'],
                ['icon' => 'fas fa-tasks', 'text' => 'Günlük İşler', 'url' => 'tasks.php']
            ];
            break;
        case 'manager':
            $menuItems = [
                ['icon' => 'fas fa-tachometer-alt', 'text' => 'Dashboard', 'url' => 'dashboard.php'],
                ['icon' => 'fas fa-building', 'text' => 'Firma Yönetimi', 'url' => 'companies.php'],
                ['icon' => 'fas fa-tasks', 'text' => 'Günlük İşler', 'url' => 'tasks.php']
            ];
            break;
        case 'user':
            $menuItems = [
                ['icon' => 'fas fa-tachometer-alt', 'text' => 'Dashboard', 'url' => 'dashboard.php'],
                ['icon' => 'fas fa-tasks', 'text' => 'Günlük İşler', 'url' => 'tasks.php']
            ];
            break;
        case 'customer':
            $menuItems = [
                ['icon' => 'fas fa-tachometer-alt', 'text' => 'Dashboard', 'url' => 'dashboard.php'],
                ['icon' => 'fas fa-tasks', 'text' => 'İşlerim', 'url' => 'my-tasks.php']
            ];
            break;
    }
    
    return $menuItems;
}

$menuItems = getMenuItems($userRole);
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'İş Yönetim Paneli'; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <!-- DataTables -->
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    
    <style>
        :root {
            --sidebar-width: 250px;
            --header-height: 60px;
        }
        
        body {
            background-color: #f8f9fa;
        }
        
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            z-index: 1000;
            transition: transform 0.3s ease;
        }
        
        .sidebar.collapsed {
            transform: translateX(-100%);
        }
        
        .sidebar-header {
            padding: 20px;
            text-align: center;
            color: white;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-menu {
            padding: 20px 0;
        }
        
        .menu-item {
            display: block;
            padding: 12px 20px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }
        
        .menu-item:hover {
            background-color: rgba(255,255,255,0.1);
            color: white;
            border-left-color: #fff;
        }
        
        .menu-item.active {
            background-color: rgba(255,255,255,0.2);
            color: white;
            border-left-color: #fff;
        }
        
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }
        
        .main-content.expanded {
            margin-left: 0;
        }
        
        .header {
            background: white;
            border-bottom: 1px solid #dee2e6;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 999;
        }
        
        .content {
            padding: 30px;
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 20px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 10px 20px;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .table {
            border-radius: 10px;
            overflow: hidden;
        }
        
        .table thead th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 15px;
        }
        
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h5><i class="fas fa-briefcase me-2"></i>PRM Portal</h5>
            <small><?php echo ucfirst($userRole); ?> Paneli</small>
        </div>
        
        <div class="sidebar-menu">
            <?php foreach ($menuItems as $item): ?>
                <a href="<?php echo $item['url']; ?>" 
                   class="menu-item <?php echo $currentPage === $item['url'] ? 'active' : ''; ?>">
                    <i class="<?php echo $item['icon']; ?> me-2"></i>
                    <?php echo $item['text']; ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <!-- Header -->
        <div class="header">
            <div class="d-flex align-items-center">
                <button class="btn btn-link d-md-none" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <h4 class="mb-0 ms-2"><?php echo $pageTitle ?? 'Dashboard'; ?></h4>
            </div>
            
            <div class="d-flex align-items-center">
                <div class="dropdown">
                    <button class="btn btn-link dropdown-toggle text-dark" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-2"></i>
                        <?php echo htmlspecialchars($currentUser['name']); ?>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i>Çıkış</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="content">
            <!-- Toast Container -->
            <div class="toast-container" id="toastContainer"></div>
            
            <?php
            $toast = getToast();
            if ($toast): ?>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            title: '<?php echo $toast['type'] === 'success' ? 'Başarılı!' : 'Hata!'; ?>',
                            text: '<?php echo addslashes($toast['message']); ?>',
                            icon: '<?php echo $toast['type']; ?>',
                            timer: 3000,
                            timerProgressBar: true,
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false
                        });
                    });
                </script>
            <?php endif; ?>
            
            <!-- Page Content -->
            <?php echo $content ?? ''; ?>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            
            sidebar.classList.toggle('show');
            mainContent.classList.toggle('expanded');
        }
        
        // DataTables initialization
        $(document).ready(function() {
            $('.datatable').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/tr.json'
                },
                responsive: true
            });
        });
        
        // Confirm delete function
        function confirmDelete(url, message = 'Bu öğeyi silmek istediğinizden emin misiniz?') {
            Swal.fire({
                title: 'Emin misiniz?',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Evet, sil!',
                cancelButtonText: 'İptal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
        }
    </script>
</body>
</html> 