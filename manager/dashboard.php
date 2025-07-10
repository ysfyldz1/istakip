<?php
// Türkiye saat dilimi ayarı
date_default_timezone_set('Europe/Istanbul');

session_start();

require_once '../includes/auth.php';
require_once '../config/database.php';

$auth = new Auth($pdo);
$auth->requireRole('manager');

// İstatistikleri al
$stats = [];

// Toplam firma sayısı
$stmt = $pdo->query("SELECT COUNT(*) as total FROM companies");
$stats['total_companies'] = $stmt->fetch()['total'];

// Toplam iş sayısı (tüm kullanıcıların)
$stmt = $pdo->query("SELECT COUNT(*) as total FROM daily_tasks");
$stats['total_tasks'] = $stmt->fetch()['total'];

// Bu ayki iş sayısı (tüm kullanıcıların)
$stmt = $pdo->query("SELECT COUNT(*) as total FROM daily_tasks WHERE MONTH(task_date) = MONTH(CURRENT_DATE()) AND YEAR(task_date) = YEAR(CURRENT_DATE())");
$stats['monthly_tasks'] = $stmt->fetch()['total'];

// Bu haftaki iş sayısı (tüm kullanıcıların)
$stmt = $pdo->query("SELECT COUNT(*) as total FROM daily_tasks WHERE YEARWEEK(task_date) = YEARWEEK(CURRENT_DATE())");
$stats['weekly_tasks'] = $stmt->fetch()['total'];

// Bugünkü kullanıcı bazlı iş dağılımı
$stmt = $pdo->query("
    SELECT u.name as user_name, COUNT(dt.id) as task_count 
    FROM users u 
    LEFT JOIN daily_tasks dt ON u.id = dt.user_id AND DATE(dt.task_date) = CURRENT_DATE()
    WHERE u.role IN ('user', 'manager')
    GROUP BY u.id, u.name 
    ORDER BY task_count DESC
");
$today_user_stats = $stmt->fetchAll();

// Son 5 iş (tüm kullanıcıların)
$stmt = $pdo->query("
    SELECT dt.*, u.name as user_name, c.name as company_name 
    FROM daily_tasks dt 
    LEFT JOIN users u ON dt.user_id = u.id 
    LEFT JOIN companies c ON dt.company_id = c.id 
    ORDER BY dt.created_at DESC 
    LIMIT 5
");
$recent_tasks = $stmt->fetchAll();

// Firma bazlı iş sayıları (tüm kullanıcıların)
$stmt = $pdo->query("
    SELECT c.name, COUNT(dt.id) as task_count 
    FROM companies c 
    LEFT JOIN daily_tasks dt ON c.id = dt.company_id 
    GROUP BY c.id, c.name 
    ORDER BY task_count DESC 
    LIMIT 5
");
$company_stats = $stmt->fetchAll();

$pageTitle = 'Manager Dashboard';
ob_start();
?>

<!-- Bugünkü Kullanıcı Bazlı İş Dağılımı -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-white">Bugünkü Kullanıcı Bazlı İş Dağılımı</h6>
            </div>
            <div class="card-body">
                <?php if (empty($today_user_stats)): ?>
                    <p class="text-muted">Bugün henüz iş kaydı bulunmuyor.</p>
                <?php else: ?>
                    <?php foreach ($today_user_stats as $user): ?>
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-grow-1">
                                <h6 class="mb-1"><?php echo htmlspecialchars($user['user_name']); ?></h6>
                                <div class="progress" style="height: 10px;">
                                    <div class="progress-bar bg-primary" role="progressbar" 
                                         style="width: <?php echo $user['task_count'] > 0 ? ($user['task_count'] / max(array_column($today_user_stats, 'task_count')) * 100) : 0; ?>%">
                                    </div>
                                </div>
                            </div>
                            <div class="ms-3">
                                <span class="badge bg-primary fs-6"><?php echo $user['task_count']; ?> iş</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- İstatistik Kartları -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Toplam Firma</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_companies']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-building fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Toplam İş</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_tasks']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-tasks fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Bu Ay İşler</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['monthly_tasks']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-calendar fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Bu Hafta İşler</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['weekly_tasks']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-calendar-week fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Firma Bazlı İş Dağılımı -->
    <div class="col-xl-6 col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-white">Firma Bazlı İş Dağılımı</h6>
            </div>
            <div class="card-body">
                <?php if (empty($company_stats)): ?>
                    <p class="text-muted">Henüz iş kaydı bulunmuyor.</p>
                <?php else: ?>
                    <?php foreach ($company_stats as $company): ?>
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-grow-1">
                                <h6 class="mb-1"><?php echo htmlspecialchars($company['name']); ?></h6>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar" role="progressbar" 
                                         style="width: <?php echo $company['task_count'] > 0 ? ($company['task_count'] / max(array_column($company_stats, 'task_count')) * 100) : 0; ?>%">
                                    </div>
                                </div>
                            </div>
                            <div class="ms-3">
                                <span class="badge bg-primary"><?php echo $company['task_count']; ?> iş</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Son İşler -->
    <div class="col-xl-6 col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-white">Son Eklenen İşler</h6>
            </div>
            <div class="card-body">
                <?php if (empty($recent_tasks)): ?>
                    <p class="text-muted">Henüz iş kaydı bulunmuyor.</p>
                <?php else: ?>
                    <?php foreach ($recent_tasks as $task): ?>
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                <i class="fas fa-tasks text-primary"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-1"><?php echo htmlspecialchars($task['task_description']); ?></h6>
                                <small class="text-muted">
                                    <?php echo htmlspecialchars($task['user_name']); ?> - 
                                    <?php echo htmlspecialchars($task['company_name'] ?? 'Firma Yok'); ?> - 
                                    <?php echo date('d.m.Y', strtotime($task['task_date'])); ?>
                                </small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once '../includes/layout.php';
?> 