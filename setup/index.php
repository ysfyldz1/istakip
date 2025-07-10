<?php
// Kurulum tamamlandıysa ana sayfaya yönlendir
if (file_exists(__DIR__ . '/../config/database.php')) {
    header('Location: ../login.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db_host = trim($_POST['db_host'] ?? '');
    $db_name = trim($_POST['db_name'] ?? '');
    $db_user = trim($_POST['db_user'] ?? '');
    $db_pass = $_POST['db_pass'] ?? '';
    $admin_name = trim($_POST['admin_name'] ?? '');
    $admin_email = trim($_POST['admin_email'] ?? '');
    $admin_pass = $_POST['admin_pass'] ?? '';

    // Basit doğrulama
    if (!$db_host || !$db_name || !$db_user || !$admin_name || !$admin_email || !$admin_pass) {
        $error = 'Lütfen tüm alanları doldurun.';
    } else {
        // Veritabanı bağlantısını test et
        try {
            $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Tabloları oluştur
            $pdo->exec("CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                role ENUM('admin', 'manager', 'user', 'customer') NOT NULL DEFAULT 'user',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

            $pdo->exec("CREATE TABLE IF NOT EXISTS companies (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                address TEXT,
                phone VARCHAR(20),
                email VARCHAR(100),
                contact_person VARCHAR(100),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

            $pdo->exec("CREATE TABLE IF NOT EXISTS daily_tasks (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                company_id INT,
                task_date DATE NOT NULL,
                task_description TEXT NOT NULL,
                notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

            // Admin hesabı var mı kontrol et
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
            $stmt->execute([$admin_email]);
            if (!$stmt->fetch()) {
                $hash = password_hash($admin_pass, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?);');
                $stmt->execute([$admin_name, $admin_email, $hash, 'admin']);
            }

            // config/database.php dosyasını oluştur
            $config = "<?php\n";
            $config .= "// Veritabanı yapılandırması\n";
            $config .= "\$host = '" . addslashes($db_host) . "';\n";
            $config .= "\$dbname = '" . addslashes($db_name) . "';\n";
            $config .= "\$username = '" . addslashes($db_user) . "';\n";
            $config .= "\$password = '" . addslashes($db_pass) . "';\n";
            $config .= "\ntry {\n";
            $config .= "    \$pdo = new PDO(\"mysql:host=\$host;dbname=\$dbname;charset=utf8\", \$username, \$password);\n";
            $config .= "    \$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);\n";
            $config .= "    \$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);\n";
            $config .= "    // Türkiye saat dilimi ayarı\n";
            $config .= "    \$pdo->exec(\"SET time_zone = '+03:00'\");\n";
            $config .= "} catch(PDOException \$e) {\n";
            $config .= "    die(\"Veritabanı bağlantı hatası: \" . \$e->getMessage());\n";
            $config .= "}\n";
            $config .= "// PHP saat dilimi ayarı\n";
            $config .= "date_default_timezone_set('Europe/Istanbul');\n";

            file_put_contents(__DIR__ . '/../config/database.php', $config);

            $success = 'Kurulum başarıyla tamamlandı! Giriş ekranına yönlendiriliyorsunuz...';
            echo '<meta http-equiv="refresh" content="3;url=../login.php">';
        } catch (PDOException $e) {
            $error = 'Veritabanı bağlantı hatası: ' . htmlspecialchars($e->getMessage());
        } catch (Exception $e) {
            $error = 'Bir hata oluştu: ' . htmlspecialchars($e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kurulum Sihirbazı</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f5f6fa; }
        .setup-container { max-width: 500px; margin: 60px auto; background: #fff; border-radius: 12px; box-shadow: 0 8px 32px rgba(0,0,0,0.08); padding: 40px; }
        .setup-title { font-size: 1.6rem; font-weight: 700; margin-bottom: 24px; text-align: center; }
    </style>
</head>
<body>
    <div class="setup-container">
        <div class="setup-title">İş Yönetim Paneli Kurulum Sihirbazı</div>
        <?php if ($error): ?>
            <div class="alert alert-danger"> <?php echo $error; ?> </div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"> <?php echo $success; ?> </div>
        <?php endif; ?>
        <?php if (!$success): ?>
        <form method="POST" autocomplete="off">
            <h5>Veritabanı Bilgileri</h5>
            <div class="mb-3">
                <label>Sunucu (host)</label>
                <input type="text" name="db_host" class="form-control" value="localhost" required>
            </div>
            <div class="mb-3">
                <label>Veritabanı Adı</label>
                <input type="text" name="db_name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Kullanıcı Adı</label>
                <input type="text" name="db_user" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Şifre</label>
                <input type="password" name="db_pass" class="form-control">
            </div>
            <hr>
            <h5>Admin Hesabı</h5>
            <div class="mb-3">
                <label>Ad Soyad</label>
                <input type="text" name="admin_name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>E-posta</label>
                <input type="email" name="admin_email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Şifre</label>
                <input type="password" name="admin_pass" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Kurulumu Başlat</button>
        </form>
        <div class="text-center mt-3">
            <small class="text-muted">Kurulum tamamlandıktan sonra <b>setup</b> klasörünü silmeniz önerilir.</small>
        </div>
        <?php endif; ?>
    </div>
</body>
</html> 