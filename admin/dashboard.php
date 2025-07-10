<?php
// Türkiye saat dilimi ayarı
date_default_timezone_set('Europe/Istanbul');

session_start();

require_once '../includes/auth.php';
require_once '../config/database.php';

$auth = new Auth($pdo);
$auth->requireRole('admin');

// İstatistikleri al
$stats = [];

// Toplam kullanıcı sayısı
$stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
$stats['total_users'] = $stmt->fetch()['total'];

// Toplam firma sayısı
$stmt = $pdo->query("SELECT COUNT(*) as total FROM companies");
$stats['total_companies'] = $stmt->fetch()['total'];

// Toplam iş sayısı
$stmt = $pdo->query("SELECT COUNT(*) as total FROM daily_tasks");
$stats['total_tasks'] = $stmt->fetch()['total'];

// Bu ayki iş sayısı
$stmt = $pdo->query("SELECT COUNT(*) as total FROM daily_tasks WHERE MONTH(task_date) = MONTH(CURRENT_DATE()) AND YEAR(task_date) = YEAR(CURRENT_DATE())");
$stats['monthly_tasks'] = $stmt->fetch()['total'];

// Son 5 iş
$stmt = $pdo->query("
    SELECT dt.*, u.name as user_name, c.name as company_name 
    FROM daily_tasks dt 
    LEFT JOIN users u ON dt.user_id = u.id 
    LEFT JOIN companies c ON dt.company_id = c.id 
    ORDER BY dt.created_at DESC 
    LIMIT 5
");
$recent_tasks = $stmt->fetchAll();

// Bugün girilen işlerin kullanıcılara göre dağılımı
$stmt = $pdo->query("
    SELECT u.name, COUNT(dt.id) as task_count 
    FROM users u 
    LEFT JOIN daily_tasks dt ON u.id = dt.user_id AND dt.task_date = CURRENT_DATE()
    GROUP BY u.id, u.name 
    HAVING task_count > 0
    ORDER BY task_count DESC
");
$today_user_stats = $stmt->fetchAll();

$pageTitle = 'Admin Dashboard';

// Content'i buffer'a al
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
                            Toplam Kullanıcı</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_users']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
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
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
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
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
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
</div>

<div class="row">
    <!-- Bugünkü İş Dağılımı -->
    <div class="col-xl-6 col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-white">Bugünkü İş Dağılımı</h6>
            </div>
            <div class="card-body">
                <?php if (empty($today_user_stats)): ?>
                    <p class="text-muted">Bugün henüz iş kaydı bulunmuyor.</p>
                <?php else: ?>
                    <div class="chart-pie pt-4 pb-2">
                        <canvas id="todayTaskChart"></canvas>
                    </div>
                    <div class="mt-4 text-center small">
                        <?php foreach ($today_user_stats as $stat): ?>
                            <span class="mr-2">
                                <i class="fas fa-circle text-primary"></i> <?php echo htmlspecialchars($stat['name']); ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Bugünkü iş dağılımı grafiği
const todayTaskData = <?php echo json_encode($today_user_stats); ?>;
const ctx = document.getElementById('todayTaskChart').getContext('2d');
new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: todayTaskData.map(item => item.name),
        datasets: [{
            data: todayTaskData.map(item => item.task_count),
            backgroundColor: [
                '#4e73df',
                '#1cc88a',
                '#36b9cc',
                '#f6c23e',
                '#e74a3b',
                '#858796'
            ],
            hoverBackgroundColor: [
                '#2e59d9',
                '#17a673',
                '#2c9faf',
                '#f4b619',
                '#e02424',
                '#6e707e'
            ],
            hoverBorderColor: "rgba(234, 236, 244, 1)",
        }],
    },
    options: {
        maintainAspectRatio: false,
        tooltips: {
            backgroundColor: "rgb(255,255,255)",
            bodyFontColor: "#858796",
            borderColor: '#dddfeb',
            borderWidth: 1,
            xPadding: 15,
            yPadding: 15,
            displayColors: false,
            caretPadding: 10,
        },
        legend: {
            display: false
        },
        cutoutPercentage: 80,
    },
});
</script>

<?php
$content = ob_get_clean();
require_once '../includes/layout.php';
?> 