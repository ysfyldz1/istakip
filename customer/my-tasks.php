<?php
session_start();

require_once '../includes/auth.php';
require_once '../config/database.php';

$auth = new Auth($pdo);
$auth->requireRole('customer');

$user_id = $_SESSION['user_id'];

// İşleri listele
$stmt = $pdo->prepare("
    SELECT dt.*, c.name as company_name 
    FROM daily_tasks dt 
    LEFT JOIN companies c ON dt.company_id = c.id 
    WHERE dt.user_id = ? 
    ORDER BY dt.task_date DESC, dt.created_at DESC
");
$stmt->execute([$user_id]);
$tasks = $stmt->fetchAll();

$pageTitle = 'İşlerim';
ob_start();
?>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">İşlerim</h5>
    </div>
    <div class="card-body">
        <?php if (empty($tasks)): ?>
            <div class="text-center py-5">
                <i class="fas fa-tasks fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Henüz iş kaydı bulunmuyor</h5>
                <p class="text-muted">Sistemde henüz kayıtlı iş bulunmuyor.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped datatable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tarih</th>
                            <th>İş Açıklaması</th>
                            <th>Firma</th>
                            <th>Notlar</th>
                            <th>Kayıt Tarihi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tasks as $task): ?>
                            <tr>
                                <td><?php echo $task['id']; ?></td>
                                <td><?php echo date('d.m.Y', strtotime($task['task_date'])); ?></td>
                                <td><?php echo htmlspecialchars($task['task_description']); ?></td>
                                <td><?php echo htmlspecialchars($task['company_name'] ?? 'Firma Yok'); ?></td>
                                <td><?php echo htmlspecialchars($task['notes'] ?? ''); ?></td>
                                <td><?php echo date('d.m.Y H:i', strtotime($task['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once '../includes/layout.php';
?> 