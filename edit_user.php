<?php
session_start();
require 'db.php';
require 'includes/functions.php';

// 1. GÜVENLİK KONTROLÜ
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 1) {
    die("Erişim Reddedildi!");
}

// 2. ID KONTROLÜ VE VERİYİ ÇEKME
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute(['id' => $id]);
$user = $stmt->fetch();

if (!$user) {
    die("Kullanıcı bulunamadı!");
}

$message = "";
$error = "";

// 3. FORM GÖNDERİLDİ Mİ? (GÜNCELLEME İŞLEMİ)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    validate_csrf_token($_POST['csrf_token']);

    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $salary = $_POST['salary'];
    $department_id = $_POST['department_id'];
    $role_id = $_POST['role_id'];
    $password = $_POST['password']; // Yeni şifre (varsa)

    if (empty($full_name) || empty($email)) {
        $error = "Ad ve E-posta alanları zorunludur.";
    } else {
        try {
            // ŞİFRE MANTIĞI (En Kritik Yer!)
            if (!empty($password)) {
                // Şifre kutusu dolu -> Hem bilgileri hem şifreyi güncelle
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $sql = "UPDATE users SET full_name=:im, email=:em, salary=:sl, department_id=:dp, role_id=:rl, password=:pw WHERE id=:id";
                $params = [
                    'im' => $full_name, 'em' => $email, 'sl' => $salary, 
                    'dp' => $department_id, 'rl' => $role_id, 
                    'pw' => $hashed_password, 'id' => $id
                ];
            } else {
                // Şifre kutusu boş -> Şifreye dokunma, sadece diğerlerini güncelle
                $sql = "UPDATE users SET full_name=:im, email=:em, salary=:sl, department_id=:dp, role_id=:rl WHERE id=:id";
                $params = [
                    'im' => $full_name, 'em' => $email, 'sl' => $salary, 
                    'dp' => $department_id, 'rl' => $role_id, 'id' => $id
                ];
            }

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            // Güncelleme bitince sayfayı yenile ki yeni verileri görelim
            $message = "Bilgiler başarıyla güncellendi!";
            // Güncel veriyi tekrar çekelim
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $user = $stmt->fetch();

        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = "Bu e-posta adresi başka bir kullanıcı tarafından kullanılıyor.";
            } else {
                $error = "Hata: " . $e->getMessage();
            }
        }
    }
}

// Select kutuları için listeleri çek
$roles = $pdo->query("SELECT * FROM roles")->fetchAll();
$departments = $pdo->query("SELECT * FROM departments")->fetchAll();

include 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="fa-solid fa-pen-to-square me-2"></i>Personel Düzenle</h5>
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
                        <label>Ad Soyad</label>
                        <input type="text" name="full_name" class="form-control" required value="<?php echo htmlspecialchars($user['full_name']); ?>">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>E-Posta</label>
                            <input type="email" name="email" class="form-control" required value="<?php echo htmlspecialchars($user['email']); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Şifre <small class="text-muted">(Değiştirmek istemiyorsanız boş bırakın)</small></label>
                            <input type="password" name="password" class="form-control" placeholder="******">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label>Maaş</label>
                            <input type="number" step="0.01" name="salary" class="form-control" value="<?php echo $user['salary']; ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Departman</label>
                            <select name="department_id" class="form-select">
                                <?php foreach($departments as $dept): ?>
                                    <option value="<?php echo $dept['id']; ?>" <?php echo ($dept['id'] == $user['department_id']) ? 'selected' : ''; ?>>
                                        <?php echo $dept['name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Yetki Rolü</label>
                            <select name="role_id" class="form-select">
                                <?php foreach($roles as $role): ?>
                                    <option value="<?php echo $role['id']; ?>" <?php echo ($role['id'] == $user['role_id']) ? 'selected' : ''; ?>>
                                        <?php echo $role['name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-warning">Güncelle</button>
                        <a href="index.php" class="btn btn-secondary">Geri Dön</a>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>