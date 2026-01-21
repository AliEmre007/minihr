<?php
// add_user.php - Resim Yükleme Özellikli
session_start();
require 'db.php';

// Güvenlik: Sadece Admin girebilir
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 1) {
    header("Location: login.php");
    exit;
}

// Departmanları ve Rolleri Çek (Select kutuları için)
$departments = $pdo->query("SELECT * FROM departments")->fetchAll();
$roles = $pdo->query("SELECT * FROM roles")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $salary = $_POST['salary'];
    $department_id = $_POST['department_id'];
    $role_id = $_POST['role_id'];
    
    // Varsayılan şifre: 1234
    $password = password_hash("1234", PASSWORD_DEFAULT);

    // --- RESİM YÜKLEME İŞLEMİ ---
    $profile_pic = null; // Varsayılan olarak resim yok

    // 1. Dosya seçilmiş mi kontrol et
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
        
        $allowed = ['jpg', 'jpeg', 'png', 'webp']; // İzin verilen uzantılar
        $filename = $_FILES['avatar']['name'];
        $filetype = $_FILES['avatar']['type'];
        $filesize = $_FILES['avatar']['size'];
        
        // Dosya uzantısını al (örn: jpg)
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        // 2. Uzantı kontrolü (Güvenlik)
        if (in_array($ext, $allowed)) {
            // 3. Dosya boyut kontrolü (Örn: 5MB'dan büyük olmasın)
            if ($filesize < 5 * 1024 * 1024) {
                // 4. Benzersiz isim oluştur (zaman damgası + rastgele sayı)
                $new_filename = time() . "_" . rand(1000, 9999) . "." . $ext;
                $upload_path = "uploads/" . $new_filename;

                // 5. Dosyayı geçici klasörden bizim klasöre taşı
                if (move_uploaded_file($_FILES['avatar']['tmp_name'], $upload_path)) {
                    $profile_pic = $new_filename; // Veritabanına kaydedilecek isim
                }
            } else {
                echo "<script>alert('Dosya boyutu çok yüksek! (Max 5MB)');</script>";
            }
        } else {
            echo "<script>alert('Sadece JPG, PNG ve WEBP formatları yüklenebilir!');</script>";
        }
    }
    // --- RESİM İŞLEMİ BİTİŞ ---


    // Veritabanına Kayıt
    try {
        $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, role_id, department_id, salary, profile_pic) 
                               VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$full_name, $email, $password, $role_id, $department_id, $salary, $profile_pic]);

        header("Location: index.php?status=success&message=Personel ve resim başarıyla eklendi.");
        exit;
    } catch (PDOException $e) {
        $error = "Hata: " . $e->getMessage();
    }
}

include 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fa-solid fa-user-plus me-2"></i>Yeni Personel Ekle</h5>
            </div>
            <div class="card-body">
                
                <?php if(isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    
                    <div class="mb-3">
                        <label>Ad Soyad</label>
                        <input type="text" name="full_name" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>E-posta</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Departman</label>
                            <select name="department_id" class="form-select">
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?php echo $dept['id']; ?>"><?php echo $dept['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Rol</label>
                            <select name="role_id" class="form-select">
                                <?php foreach ($roles as $role): ?>
                                    <option value="<?php echo $role['id']; ?>"><?php echo $role['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label>Maaş</label>
                        <input type="number" name="salary" class="form-control" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Profil Fotoğrafı</label>
                        <input type="file" name="avatar" class="form-control" accept="image/*">
                        <div class="form-text">Desteklenen formatlar: .jpg, .png, .jpeg</div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Kaydet</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>