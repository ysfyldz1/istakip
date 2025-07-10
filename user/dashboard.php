<?php
// Türkiye saat dilimi ayarı
date_default_timezone_set('Europe/Istanbul');

session_start();

require_once '../includes/auth.php';
require_once '../config/database.php';

$auth = new Auth($pdo);
$auth->requireRole('user');

$user_id = $_SESSION['user_id'];

// Türkçe ay isimleri fonksiyonu
function getTurkishMonth($date) {
    $months = [
        'January' => 'Ocak',
        'February' => 'Şubat',
        'March' => 'Mart',
        'April' => 'Nisan',
        'May' => 'Mayıs',
        'June' => 'Haziran',
        'July' => 'Temmuz',
        'August' => 'Ağustos',
        'September' => 'Eylül',
        'October' => 'Ekim',
        'November' => 'Kasım',
        'December' => 'Aralık'
    ];
    
    $englishMonth = date('F', strtotime($date));
    $year = date('Y', strtotime($date));
    
    return $months[$englishMonth] . ' ' . $year;
}

// İstatistikleri al
$stats = [];

// Toplam iş sayısı
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM daily_tasks WHERE user_id = ?");
$stmt->execute([$user_id]);
$stats['total_tasks'] = $stmt->fetch()['total'];

// Bu ayki iş sayısı
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM daily_tasks WHERE user_id = ? AND MONTH(task_date) = MONTH(CURRENT_DATE()) AND YEAR(task_date) = YEAR(CURRENT_DATE())");
$stmt->execute([$user_id]);
$stats['monthly_tasks'] = $stmt->fetch()['total'];

// Bu haftaki iş sayısı
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM daily_tasks WHERE user_id = ? AND YEARWEEK(task_date) = YEARWEEK(CURRENT_DATE())");
$stmt->execute([$user_id]);
$stats['weekly_tasks'] = $stmt->fetch()['total'];

// Bugünkü iş sayısı
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM daily_tasks WHERE user_id = ? AND task_date = CURRENT_DATE()");
$stmt->execute([$user_id]);
$stats['today_tasks'] = $stmt->fetch()['total'];

// Son 5 iş
$stmt = $pdo->prepare("
    SELECT dt.*, c.name as company_name 
    FROM daily_tasks dt 
    LEFT JOIN companies c ON dt.company_id = c.id 
    WHERE dt.user_id = ? 
    ORDER BY dt.created_at DESC 
    LIMIT 5
");
$stmt->execute([$user_id]);
$recent_tasks = $stmt->fetchAll();

// Aylık iş dağılımı (son 6 ay)
$stmt = $pdo->prepare("
    SELECT DATE_FORMAT(task_date, '%Y-%m') as month, COUNT(*) as count 
    FROM daily_tasks 
    WHERE user_id = ? AND task_date >= DATE_SUB(CURRENT_DATE(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(task_date, '%Y-%m') 
    ORDER BY month DESC
");
$stmt->execute([$user_id]);
$monthly_stats = $stmt->fetchAll();

$pageTitle = 'User Dashboard';
ob_start();
?>

<div class="row">
    <!-- İstatistik Kartları -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
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
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
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
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
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

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Bugün İşler</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['today_tasks']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Aylık İş Dağılımı -->
    <div class="col-xl-6 col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-white">Son 6 Ay İş Dağılımı</h6>
            </div>
            <div class="card-body">
                <?php if (empty($monthly_stats)): ?>
                    <p class="text-muted">Henüz iş kaydı bulunmuyor.</p>
                <?php else: ?>
                    <?php foreach ($monthly_stats as $stat): ?>
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-grow-1">
                                <h6 class="mb-1"><?php echo getTurkishMonth($stat['month'] . '-01'); ?></h6>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar" role="progressbar" 
                                         style="width: <?php echo $stat['count'] > 0 ? ($stat['count'] / max(array_column($monthly_stats, 'count')) * 100) : 0; ?>%">
                                    </div>
                                </div>
                            </div>
                            <div class="ms-3">
                                <span class="badge bg-primary"><?php echo $stat['count']; ?> iş</span>
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