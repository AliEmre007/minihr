<?php
// index.php - FINAL DASHBOARD VERSİYONU
session_start();
require 'db.php';

// 1. GÜVENLİK: Giriş yapılmış mı?
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// --- İSTATİSTİKLER (Dashboard Verileri) ---
// Toplam Personel
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

// Toplam Ödenen Maaş
$total_salary = $pdo->query("SELECT SUM(salary) FROM users")->fetchColumn();

// Ortalama Maaş
$avg_salary = $pdo->query("SELECT AVG(salary) FROM users")->fetchColumn();


// --- PERSONEL LİSTESİ (JOIN İLE DETAYLI ÇEKME) ---
// departments ve roles tablolarını bağlayarak ID yerine İsimleri alıyoruz
$sql = "SELECT users.*, roles.name as role_name, departments.name as dept_name 
        FROM users 
        LEFT JOIN roles ON users.role_id = roles.id 
        LEFT JOIN departments ON users.department_id = departments.id
        ORDER BY users.id ASC";
$users = $pdo->query($sql)->fetchAll();

include 'includes/header.php'; 
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="fa-solid fa-chart-pie me-2"></i>Yönetim Paneli</h1>
    <?php if ($_SESSION['user_role'] == 1): ?>
        <a href="add_user.php" class="btn btn-primary shadow">
            <i class="fa-solid fa-user-plus me-1"></i> Yeni Personel Ekle
        </a>
    <?php endif; ?>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card text-white bg-primary shadow-sm h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="card-title mb-0">Toplam Personel</h6>
                    <h2 class="display-6 fw-bold my-2"><?php echo $total_users; ?></h2>
                    <small class="opacity-75">Aktif çalışan sayısı</small>
                </div>
                <i class="fa-solid fa-users fa-3x opacity-50"></i>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card text-white bg-success shadow-sm h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="card-title mb-0">Aylık Maaş Yükü</h6>
                    <h2 class="display-6 fw-bold my-2"><?php echo number_format($total_salary, 0, ',', '.'); ?> ₺</h2>
                    <small class="opacity-75">Toplam ödenen miktar</small>
                </div>
                <i class="fa-solid fa-wallet fa-3x opacity-50"></i>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card text-white bg-info shadow-sm h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="card-title mb-0">Ortalama Maaş</h6>
                    <h2 class="display-6 fw-bold my-2"><?php echo number_format($avg_salary, 0, ',', '.'); ?> ₺</h2>
                    <small class="opacity-75">Personel başına düşen</small>
                </div>
                <i class="fa-solid fa-chart-line fa-3x opacity-50"></i>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0 text-secondary"><i class="fa-solid fa-list me-2"></i>Personel Listesi</h5>
    </div>
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
            <table class="table table-hover table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Ad Soyad</th>
                        <th>Departman</th>
                        <th>Rol</th>
                        <th>Maaş</th>
                        <?php if ($_SESSION['user_role'] == 1): ?>
                            <th class="text-end">İşlemler</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td>#<?php echo $user['id']; ?></td>
                        
                        <td class="fw-bold">
                            <div class="d-flex align-items-center">
                                <div class="avatar bg-secondary text-white rounded-circle me-2 d-flex justify-content-center align-items-center" style="width: 35px; height: 35px;">
                                    <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                                </div>
                                <?php echo htmlspecialchars($user['full_name']); ?>
                            </div>
                        </td>

                        <td>
                            <span class="badge bg-light text-dark border">
                                <?php echo htmlspecialchars($user['dept_name']); ?>
                            </span>
                        </td>

                        <td>
                            <?php if($user['role_name'] == 'Admin'): ?>
                                <span class="badge bg-danger"><i class="fa-solid fa-shield-halved me-1"></i>Admin</span>
                            <?php else: ?>
                                <span class="badge bg-primary">Personel</span>
                            <?php endif; ?>
                        </td>

                        <td class="font-monospace text-success">
                            <?php echo number_format($user['salary'], 2, ',', '.'); ?> ₺
                        </td>

                        <?php if ($_SESSION['user_role'] == 1): ?>
                            <td class="text-end">
                                <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-warning" title="Düzenle">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                
                                <a href="delete.php?id=<?php echo $user['id']; ?>" 
                                   onclick="return confirm('<?php echo $user['full_name']; ?> isimli personeli silmek istediğine emin misin?')" 
                                   class="btn btn-sm btn-outline-danger" 
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