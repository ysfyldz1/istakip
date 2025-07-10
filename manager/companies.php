<?php
// Türkiye saat dilimi ayarı
date_default_timezone_set('Europe/Istanbul');

session_start();

require_once '../includes/auth.php';
require_once '../config/database.php';

$auth = new Auth($pdo);
$auth->requireRole('manager');

// Firma ekleme/düzenleme işlemi
if ($_POST) {
    $action = $_POST['action'] ?? '';
    $name = trim($_POST['name']);
    $address = trim($_POST['address']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $contact_person = trim($_POST['contact_person']);
    
    if ($action === 'add') {
        try {
            $stmt = $pdo->prepare("INSERT INTO companies (name, address, phone, email, contact_person) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $address, $phone, $email, $contact_person]);
            showToast('Firma başarıyla eklendi.', 'success');
            header('Location: companies.php');
            exit();
        } catch (PDOException $e) {
            showToast('Firma eklenirken hata oluştu.', 'error');
        }
    } elseif ($action === 'edit') {
        $id = $_POST['id'];
        
        try {
            $stmt = $pdo->prepare("UPDATE companies SET name = ?, address = ?, phone = ?, email = ?, contact_person = ? WHERE id = ?");
            $stmt->execute([$name, $address, $phone, $email, $contact_person, $id]);
            showToast('Firma başarıyla güncellendi.', 'success');
            header('Location: companies.php');
            exit();
        } catch (PDOException $e) {
            showToast('Firma güncellenirken hata oluştu.', 'error');
        }
    }
}

// Firma silme işlemi
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    try {
        $stmt = $pdo->prepare("DELETE FROM companies WHERE id = ?");
        $stmt->execute([$id]);
        showToast('Firma başarıyla silindi.', 'success');
        header('Location: companies.php');
        exit();
    } catch (PDOException $e) {
        showToast('Firma silinirken hata oluştu.', 'error');
    }
}

// Firmaları listele
$stmt = $pdo->query("SELECT * FROM companies ORDER BY created_at DESC");
$companies = $stmt->fetchAll();

$pageTitle = 'Firma Yönetimi';
ob_start();
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Firma Listesi</h5>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCompanyModal">
            <i class="fas fa-plus me-2"></i>Yeni Firma
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped datatable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Firma Adı</th>
                        <th>Adres</th>
                        <th>Telefon</th>
                        <th>E-posta</th>
                        <th>İletişim Kişisi</th>
                        <th>Kayıt Tarihi</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($companies as $company): ?>
                        <tr>
                            <td><?php echo $company['id']; ?></td>
                            <td><?php echo htmlspecialchars($company['name']); ?></td>
                            <td><?php echo htmlspecialchars($company['address']); ?></td>
                            <td><?php echo htmlspecialchars($company['phone']); ?></td>
                            <td><?php echo htmlspecialchars($company['email']); ?></td>
                            <td><?php echo htmlspecialchars($company['contact_person']); ?></td>
                            <td><?php echo date('d.m.Y H:i', strtotime($company['created_at'])); ?></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" 
                                        onclick="editCompany(<?php echo htmlspecialchars(json_encode($company)); ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" 
                                        onclick="confirmDelete('companies.php?delete=<?php echo $company['id']; ?>', 'Bu firmayı silmek istediğinizden emin misiniz?')">
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

<!-- Firma Ekleme Modal -->
<div class="modal fade" id="addCompanyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Yeni Firma Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Firma Adı</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="address" class="form-label">Adres</label>
                        <textarea class="form-control" id="address" name="address" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="phone" class="form-label">Telefon</label>
                        <input type="text" class="form-control" id="phone" name="phone">
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">E-posta</label>
                        <input type="email" class="form-control" id="email" name="email">
                    </div>
                    
                    <div class="mb-3">
                        <label for="contact_person" class="form-label">İletişim Kişisi</label>
                        <input type="text" class="form-control" id="contact_person" name="contact_person">
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

<!-- Firma Düzenleme Modal -->
<div class="modal fade" id="editCompanyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Firma Düzenle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Firma Adı</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_address" class="form-label">Adres</label>
                        <textarea class="form-control" id="edit_address" name="address" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_phone" class="form-label">Telefon</label>
                        <input type="text" class="form-control" id="edit_phone" name="phone">
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_email" class="form-label">E-posta</label>
                        <input type="email" class="form-control" id="edit_email" name="email">
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_contact_person" class="form-label">İletişim Kişisi</label>
                        <input type="text" class="form-control" id="edit_contact_person" name="contact_person">
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
function editCompany(company) {
    document.getElementById('edit_id').value = company.id;
    document.getElementById('edit_name').value = company.name;
    document.getElementById('edit_address').value = company.address;
    document.getElementById('edit_phone').value = company.phone;
    document.getElementById('edit_email').value = company.email;
    document.getElementById('edit_contact_person').value = company.contact_person;
    
    new bootstrap.Modal(document.getElementById('editCompanyModal')).show();
}
</script>

<?php
$content = ob_get_clean();
require_once '../includes/layout.php';
?> 