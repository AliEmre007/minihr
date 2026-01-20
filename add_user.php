<?php
session_start();
require 'db.php';
require 'includes/functions.php'; // Alet çantamızı dahil ettik

// GÜVENLİK 1: Sadece Admin girebilir!
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 1) {
    die("Erişim Reddedildi: Bu sayfayı görüntüleme yetkiniz yok.");
}

$message = "";
$error = "";

// --- FORM GÖNDERİLDİ Mİ? (BACKEND) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // GÜVENLİK 2: CSRF Kontrolü
    validate_csrf_token($_POST['csrf_token']);

    // Formdan gelen verileri al
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password']; // Şifreleme yapacağız
    $salary = $_POST['salary'];
    $department_id = $_POST['department_id'];
    $role_id = $_POST['role_id'];

    // Basit validasyon (Boş alan var mı?)
    if (empty($full_name) || empty($email) || empty($password)) {
        $error = "Lütfen tüm zorunlu alanları doldurun.";
    } else {
        // GÜVENLİK 3: Şifreyi Hashle (Asla düz metin kaydetme!)
        // '1234' yerine '$2y$10$...' gibi karmaşık bir string olacak.
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        try {
            // Veritabanına Ekleme Sorgusu
            $sql = "INSERT INTO users (full_name, email, password, salary, department_id, role_id) 
                    VALUES (:im, :em, :pw, :sl, :dp, :rl)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'im' => $full_name,
                'em' => $email,
                'pw' => $hashed_password,
                'sl' => $salary,
                'dp' => $department_id,
                'rl' => $role_id
            ]);

            $message = "Personel başarıyla eklendi!";
            
            // Formun tekrar gönderilmesini önlemek için yönlendirme yapabiliriz
            // header("Location: index.php"); exit; 
            // (Şimdilik mesajı görmek için yönlendirme yapmıyoruz)

        } catch (PDOException $e) {
            // Email unique olduğu için aynı maille kayıt olursa hata verir
            if ($e->getCode() == 23000) {
                $error = "Bu e-posta adresi zaten kayıtlı!";
            } else {
                $error = "Veritabanı hatası: " . $e->getMessage();
            }
        }
    }
}

// --- SELECT KUTULARI İÇİN VERİ ÇEKME ---
$roles = $pdo->query("SELECT * FROM roles")->fetchAll();
$departments = $pdo->query("SELECT * FROM departments")->fetchAll();

include 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fa-solid fa-user-plus me-2"></i>Yeni Personel Ekle</h5>
            </div>
            <div class="card-body">

                <?php if($message): ?>
                    <div class="alert alert-success"><?php echo $message; ?></div>
                <?php endif; ?>
                <?php if($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo create_csrf_token(); ?>">

                    <div class="mb-3">
                        <label>Ad Soyad <span class="text-danger">*</span></label>
                        <input type="text" name="full_name" class="form-control" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>E-Posta <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Şifre <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label>Maaş (TL)</label>
                            <input type="number" step="0.01" name="salary" class="form-control">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Departman</label>
                            <select name="department_id" class="form-select">
                                <?php foreach($departments as $dept): ?>
                                    <option value="<?php echo $dept['id']; ?>"><?php echo $dept['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Yetki Rolü</label>
                            <select name="role_id" class="form-select">
                                <?php foreach($roles as $role): ?>
                                    <option value="<?php echo $role['id']; ?>"><?php echo $role['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Kaydet</button>
                        <a href="index.php" class="btn btn-secondary">İptal / Geri Dön</a>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>