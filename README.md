# İş Yönetim Paneli

Küçük/orta ölçekli işletmeler için geliştirilmiş modern bir iş yönetim paneli. Kullanıcılar rollerine göre farklı yetkilere sahip olup günlük işlerini sisteme kaydedebilirler.

## Özellikler

### 🔐 Kullanıcı Yönetimi
- **4 farklı rol**: admin, manager, user, customer
- Güvenli giriş sistemi (PDO ile şifre hashleme)
- Rol bazlı yetkilendirme

### 📊 Dashboard
- Her rol için özelleştirilmiş dashboard
- İstatistik kartları ve grafikler
- Son işler listesi

### 👥 Kullanıcı Yönetimi (Admin)
- Kullanıcı ekleme, düzenleme, silme
- Rol atama
- Şifre yönetimi

### 🏢 Firma Yönetimi (Admin/Manager)
- Firma ekleme, düzenleme, silme
- Firma bilgileri (adres, telefon, e-posta, iletişim kişisi)

### 📝 Günlük İşler
- İş ekleme, düzenleme, silme
- Firma seçimi
- Tarih ve not ekleme
- Rol bazlı görüntüleme (admin tüm işleri, diğerleri sadece kendi işlerini)

### 🎨 Modern Arayüz
- Bootstrap 5 tabanlı responsive tasarım
- Font Awesome ikonları
- SweetAlert2 toast bildirimleri
- DataTables entegrasyonu

## Teknik Detaylar

### Backend
- **PHP 7.4+** (PDO ile güvenli veritabanı bağlantısı)
- **MySQL** veritabanı
- Modüler kod yapısı
- Session tabanlı kimlik doğrulama

### Frontend
- **Bootstrap 5** (responsive tasarım)
- **Font Awesome 6** (ikonlar)
- **SweetAlert2** (toast bildirimleri)
- **DataTables** (tablo yönetimi)
- **Chart.js** (grafikler)

### Veritabanı Yapısı
```sql
-- Kullanıcılar tablosu
users (id, name, email, password, role, created_at, updated_at)

-- Firmalar tablosu
companies (id, name, address, phone, email, contact_person, created_at, updated_at)

-- Günlük işler tablosu
daily_tasks (id, user_id, company_id, task_date, task_description, notes, created_at, updated_at)
```

## Kurulum

### 1. Gereksinimler
- PHP 7.4 veya üzeri
- MySQL 5.7 veya üzeri
- Apache/Nginx web sunucusu

### 2. Kurulum
- Dosyaları sunucunuza kopyaladıktan sonra /setup/index.php adresine gidin
- Gelen kurulum ekranında DB ve admin user bilgilerini girin ve kaydedin.
- Kurulum bittikten sonra sizi dashboard a yönlendirecektir.


## Kullanım

### Admin Paneli
- Tüm kullanıcıları yönetebilir
- Tüm firmaları yönetebilir
- Tüm işleri görüntüleyebilir
- İstatistikleri takip edebilir

### Manager Paneli
- Firmaları yönetebilir
- Kendi işlerini ekleyebilir/düzenleyebilir
- İstatistikleri görüntüleyebilir

### User Paneli
- Kendi işlerini ekleyebilir/düzenleyebilir
- İstatistiklerini görüntüleyebilir

### Customer Paneli
- Sadece kendi işlerini görüntüleyebilir
- İş ekleyemez/düzenleyemez

## Dosya Yapısı

```
├── index.php                 # Ana giriş sayfası
├── logout.php               # Çıkış işlemi
├── config/
│   └── database.php         # Veritabanı yapılandırması
├── includes/
│   ├── auth.php            # Kimlik doğrulama sınıfı
│   └── layout.php          # Ortak layout
├── admin/                  # Admin paneli
│   ├── dashboard.php
│   ├── users.php
│   ├── companies.php
│   └── tasks.php
├── manager/                # Manager paneli
│   ├── dashboard.php
│   ├── companies.php
│   └── tasks.php
├── user/                   # User paneli
│   ├── dashboard.php
│   └── tasks.php
└── customer/               # Customer paneli
    ├── dashboard.php
    └── my-tasks.php
```

## Güvenlik Özellikleri

- **PDO Prepared Statements** ile SQL injection koruması
- **Password hashing** ile güvenli şifre saklama
- **Session tabanlı** kimlik doğrulama
- **Rol bazlı** yetkilendirme
- **XSS koruması** için htmlspecialchars kullanımı

## Özelleştirme

### Yeni Modül Ekleme
1. İlgili rol klasöründe yeni PHP dosyası oluşturun
2. `includes/layout.php` dosyasında menü öğelerini güncelleyin
3. Yetkilendirme kontrolü ekleyin

### Tema Değiştirme
- `includes/layout.php` dosyasındaki CSS stillerini düzenleyin
- Bootstrap tema dosyalarını değiştirin

## Lisans

Bu proje MIT lisansı altında lisanslanmıştır.

## Destek

Herhangi bir sorun veya öneri için lütfen iletişime geçin. 
