<?php
// edit_user.php - Resim Güncelleme Özellikli
session_start();
require 'db.php';

// 1. GÜVENLİK: Admin değilse giremez
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 1) {
    header("Location: login.php");
    exit;
}

// 2. ID KONTROLÜ: URL'de ID var mı?
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_GET['id'];

// 3. MEVCUT VERİYİ ÇEK
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    die("Kullanıcı bulunamadı!");
}

// Dropdownlar için verileri çek
$departments = $pdo->query("SELECT * FROM departments")->fetchAll();
$roles = $pdo->query("SELECT * FROM roles")->fetchAll();

// 4. FORMU KAYDETME İŞLEMİ
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $salary = $_POST['salary'];
    $department_id = $_POST['department_id'];
    $role_id = $_POST['role_id'];
    
    // Şifre mantığı: Boş bırakırsa eskisi kalsın
    $password_sql = ""; 
    $params = [$full_name, $email, $role_id, $department_id, $salary];

    if (!empty($_POST['password'])) {
        $password_sql = ", password = ?";
        $params[] = password_hash($_POST['password'], PASSWORD_DEFAULT);
    }

    // --- RESİM GÜNCELLEME MANTIĞI ---
    $profile_pic_sql = ""; // Varsayılan: Resim SQL'i boş
    
    // Eğer yeni dosya seçildiyse:
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'jfif'];
        $filename = $_FILES['avatar']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            $new_filename = time() . "_" . rand(1000, 9999) . "." . $ext;
            $upload_path = "uploads/" . $new_filename;

            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $upload_path)) {
                
                // ESKİ RESMİ SİL (Çöp birikmesin)
                if (!empty($user['profile_pic']) && file_exists("uploads/" . $user['profile_pic'])) {
                    unlink("uploads/" . $user['profile_pic']);
                }

                // SQL'e ekle
                $profile_pic_sql = ", profile_pic = ?";
                $params[] = $new_filename;
            }
        }
    }
    // --- RESİM MANTIĞI BİTİŞ ---

    // Güncelleme Sorgusu
    // ID'yi parametrelerin en sonuna ekle
    $params[] = $user_id; 

    $sql = "UPDATE users SET full_name = ?, email = ?, role_id = ?, department_id = ?, salary = ? $password_sql $profile_pic_sql WHERE id = ?";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        header("Location: index.php?status=success&message=Personel başarıyla güncellendi.");
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
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="fa-solid fa-pen-to-square me-2"></i>Personel Düzenle</h5>
            </div>
            <div class="card-body">
                
                <?php if(isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    
                    <div class="text-center mb-4">
                        <?php if (!empty($user['profile_pic']) && file_exists('uploads/' . $user['profile_pic'])): ?>
                            <img src="uploads/<?php echo $user['profile_pic']; ?>" class="rounded-circle border shadow-sm" style="width: 100px; height: 100px; object-fit: cover;">
                            <div class="mt-2 text-muted small">Mevcut Fotoğraf</div>
                        <?php else: ?>
                            <div class="avatar bg-secondary text-white rounded-circle mx-auto d-flex justify-content-center align-items-center" style="width: 100px; height: 100px; font-size: 40px;">
                                <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                            </div>
                            <div class="mt-2 text-muted small">Fotoğraf Yok</div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label>Ad Soyad</label>
                        <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label>E-posta</label>
                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Departman</label>
                            <select name="department_id" class="form-select">
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?php echo $dept['id']; ?>" <?php echo $user['department_id'] == $dept['id'] ? 'selected' : ''; ?>>
                                        <?php echo $dept['name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Rol</label>
                            <select name="role_id" class="form-select">
                                <?php foreach ($roles as $role): ?>
                                    <option value="<?php echo $role['id']; ?>" <?php echo $user['role_id'] == $role['id'] ? 'selected' : ''; ?>>
                                        <?php echo $role['name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label>Maaş</label>
                        <input type="number" name="salary" class="form-control" value="<?php echo $user['salary']; ?>" required>
                    </div>

                    <div class="mb-3">
                        <label>Yeni Şifre (Değiştirmek istemiyorsan boş bırak)</label>
                        <input type="password" name="password" class="form-control" placeholder="******">
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Profil Fotoğrafını Değiştir</label>
                        <input type="file" name="avatar" class="form-control" accept="image/*">
                        <div class="form-text">Boş bırakırsan mevcut resim korunur.</div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-warning">Güncelle</button>
                        <a href="index.php" class="btn btn-light border">İptal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>