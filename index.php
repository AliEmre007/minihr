<?php
// 1. ADIM: Oturumu en tepede başlat
session_start();

// 2. ADIM: Güvenlik Kontrolü (Kapı Bekçisi)
// Eğer kullanıcının kimliği (user_id) yoksa, içeri sokma!
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit; // Kodun geri kalanını okumayı derhal kes
}

// --- BURADAN AŞAĞISI SADECE GİRİŞ YAPANLARA GÖRÜNÜR ---

require 'db.php'; // Veritabanı bağlantısı

// Kullanıcı verilerini çek
$stmt = $pdo->query("SELECT users.*, roles.name as role_name, departments.name as dept_name 
                     FROM users 
                     LEFT JOIN roles ON users.role_id = roles.id 
                     LEFT JOIN departments ON users.department_id = departments.id");
$users = $stmt->fetchAll();

include 'includes/header.php'; // Üst menü
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="fa-solid fa-list me-2"></i>Personel Listesi</h1>
    <?php if ($_SESSION['user_role'] == 1): // Sadece Admin ise göster ?>
    <a href="add_user.php" class="btn btn-primary"><i class="fa-solid fa-user-plus me-1"></i> Yeni Personel Ekle</a>
<?php endif; ?>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        
        <?php if (isset($_GET['status']) && isset($_GET['message'])): ?>
            <?php 
                $alertType = $_GET['status'] == 'success' ? 'alert-success' : 'alert-danger';
                $icon = $_GET['status'] == 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
            ?>
            <div class="alert <?php echo $alertType; ?> alert-dismissible fade show" role="alert">
                <i class="fa-solid <?php echo $icon; ?> me-2"></i> 
                <?php echo htmlspecialchars($_GET['message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-hover table-striped">
            ```

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Ad Soyad</th>
                        <th>Departman</th>
                        <th>Rol</th>
                        <th>Maaş</th>
                        <?php if ($_SESSION['user_role'] == 1): ?>
    <th>İşlemler</th>
<?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td>#<?php echo $user['id']; ?></td>
                        <td class="fw-bold"><?php echo htmlspecialchars($user['full_name']); ?></td>
                        <td><span class="badge bg-info text-dark"><?php echo $user['dept_name']; ?></span></td>
                        <td>
                            <?php if($user['role_name'] == 'Admin'): ?>
                                <span class="badge bg-danger"><?php echo $user['role_name']; ?></span>
                            <?php else: ?>
                                <span class="badge bg-secondary"><?php echo $user['role_name']; ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo number_format($user['salary'], 2, ',', '.'); ?> ₺</td>
                        <?php if ($_SESSION['user_role'] == 1): ?>
                            <td>
                                <a href="#" class="btn btn-sm btn-warning" title="Düzenle"><i class="fa-solid fa-pen"></i></a>
                                <a href="delete.php?id=<?php echo $user['id']; ?>" 
   onclick="return confirm('Bu personeli silmek istediğine emin misin?')" 
   class="btn btn-sm btn-danger" 
   title="Sil">
   <i class="fa-solid fa-trash"></i>
</a>
                            </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>