<?php
// Türkiye saat dilimi ayarı
date_default_timezone_set('Europe/Istanbul');

session_start();

require_once '../includes/auth.php';
require_once '../config/database.php';

$auth = new Auth($pdo);
$auth->requireRole('user');

// İş ekleme/düzenleme işlemi
if ($_POST) {
    $action = $_POST['action'] ?? '';
    $task_date = $_POST['task_date'];
    $task_description = trim($_POST['task_description']);
    $company_id = $_POST['company_id'] ?: null;
    $notes = trim($_POST['notes']);
    $user_id = $_SESSION['user_id'];
    
    if ($action === 'add') {
        try {
            $stmt = $pdo->prepare("INSERT INTO daily_tasks (user_id, company_id, task_date, task_description, notes) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $company_id, $task_date, $task_description, $notes]);
            showToast('İş başarıyla eklendi.', 'success');
            header('Location: tasks.php');
            exit();
        } catch (PDOException $e) {
            showToast('İş eklenirken hata oluştu.', 'error');
        }
    } elseif ($action === 'edit') {
        $id = $_POST['id'];
        
        try {
            $stmt = $pdo->prepare("UPDATE daily_tasks SET company_id = ?, task_date = ?, task_description = ?, notes = ? WHERE id = ? AND user_id = ?");
            $stmt->execute([$company_id, $task_date, $task_description, $notes, $id, $user_id]);
            showToast('İş başarıyla güncellendi.', 'success');
            header('Location: tasks.php');
            exit();
        } catch (PDOException $e) {
            showToast('İş güncellenirken hata oluştu.', 'error');
        }
    }
}

// İş silme işlemi
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $user_id = $_SESSION['user_id'];
    
    try {
        $stmt = $pdo->prepare("DELETE FROM daily_tasks WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $user_id]);
        showToast('İş başarıyla silindi.', 'success');
        header('Location: tasks.php');
        exit();
    } catch (PDOException $e) {
        showToast('İş silinirken hata oluştu.', 'error');
    }
}

// Firmaları al
$stmt = $pdo->query("SELECT * FROM companies ORDER BY name");
$companies = $stmt->fetchAll();

// İşleri listele (user sadece kendi işlerini görebilir)
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT dt.*, u.name as user_name, c.name as company_name 
    FROM daily_tasks dt 
    LEFT JOIN users u ON dt.user_id = u.id 
    LEFT JOIN companies c ON dt.company_id = c.id 
    WHERE dt.user_id = ? 
    ORDER BY dt.task_date DESC, dt.created_at DESC
");
$stmt->execute([$user_id]);
$tasks = $stmt->fetchAll();

$pageTitle = 'Günlük İşler';
ob_start();
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Günlük İşler</h5>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTaskModal">
            <i class="fas fa-plus me-2"></i>Yeni İş Ekle
        </button>
    </div>
    <div class="card-body">
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
                        <th>İşlemler</th>
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
                            <td>
                                <button class="btn btn-sm btn-outline-primary" 
                                        onclick="editTask(<?php echo htmlspecialchars(json_encode($task)); ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" 
                                        onclick="confirmDelete('tasks.php?delete=<?php echo $task['id']; ?>', 'Bu işi silmek istediğinizden emin misiniz?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- İş Ekleme Modal -->
<div class="modal fade" id="addTaskModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Yeni İş Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="mb-3">
                        <label for="task_date" class="form-label">Tarih</label>
                        <input type="date" class="form-control" id="task_date" name="task_date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="task_description" class="form-label">İş Açıklaması</label>
                        <textarea class="form-control" id="task_description" name="task_description" rows="3" required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="company_id" class="form-label">Firma</label>
                        <select class="form-select" id="company_id" name="company_id">
                            <option value="">Firma Seçin</option>
                            <?php foreach ($companies as $company): ?>
                                <option value="<?php echo $company['id']; ?>">
                                    <?php echo htmlspecialchars($company['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notlar</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- İş Düzenleme Modal -->
<div class="modal fade" id="editTaskModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">İş Düzenle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    
                    <div class="mb-3">
                        <label for="edit_task_date" class="form-label">Tarih</label>
                        <input type="date" class="form-control" id="edit_task_date" name="task_date" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_task_description" class="form-label">İş Açıklaması</label>
                        <textarea class="form-control" id="edit_task_description" name="task_description" rows="3" required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_company_id" class="form-label">Firma</label>
                        <select class="form-select" id="edit_company_id" name="company_id">
                            <option value="">Firma Seçin</option>
                            <?php foreach ($companies as $company): ?>
                                <option value="<?php echo $company['id']; ?>">
                                    <?php echo htmlspecialchars($company['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_notes" class="form-label">Notlar</label>
                        <textarea class="form-control" id="edit_notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">Güncelle</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editTask(task) {
    document.getElementById('edit_id').value = task.id;
    document.getElementById('edit_task_date').value = task.task_date;
    document.getElementById('edit_task_description').value = task.task_description;
    document.getElementById('edit_company_id').value = task.company_id || '';
    document.getElementById('edit_notes').value = task.notes || '';
    
    new bootstrap.Modal(document.getElementById('editTaskModal')).show();
}
</script>

<?php
$content = ob_get_clean();
require_once '../includes/layout.php';
?> 