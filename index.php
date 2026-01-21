<?php
// index.php - TAMAMLANMIŞ ARAMA VE FİLTRELEME VERSİYONU
session_start();
require 'db.php';

// 1. GÜVENLİK: Giriş yapılmış mı?
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// --- İSTATİSTİKLER (Dashboard Verileri) ---
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_salary = $pdo->query("SELECT SUM(salary) FROM users")->fetchColumn();
$avg_salary = $pdo->query("SELECT AVG(salary) FROM users")->fetchColumn();


// --- ARAMA VE FİLTRELEME MANTIĞI (PHP KISMI) ---
// 1. URL'den gelen verileri al (yoksa boş kabul et)
$search_keyword = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_dept = isset($_GET['department']) ? $_GET['department'] : '';

// 2. Dinamik SQL Sorgusunu Hazırla
// "WHERE 1=1" taktiği: Sorguya dinamik olarak "AND" eklemeyi kolaylaştırır.
$sql = "SELECT users.*, roles.name as role_name, departments.name as dept_name 
        FROM users 
        LEFT JOIN roles ON users.role_id = roles.id 
        LEFT JOIN departments ON users.department_id = departments.id
        WHERE 1=1"; 

$params = [];

// 3. Eğer isim aranıyorsa sorguya ekle
if (!empty($search_keyword)) {
    $sql .= " AND users.full_name LIKE :keyword";
    $params[':keyword'] = "%$search_keyword%";
}

// 4. Eğer departman seçildiyse sorguya ekle
if (!empty($filter_dept)) {
    $sql .= " AND users.department_id = :dept_id";
    $params[':dept_id'] = $filter_dept;
}

$sql .= " ORDER BY users.id ASC";

// 5. Sorguyu Güvenli Şekilde Çalıştır (Prepare/Execute)
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

// 6. Filtre menüsü (Select kutusu) için tüm departmanları çek
$departments = $pdo->query("SELECT * FROM departments")->fetchAll();

include 'includes/header.php'; 
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="fa-solid fa-chart-pie me-2"></i>Yönetim Paneli</h1>
    
    <div class="d-flex gap-2">
        <a href="export.php" class="btn btn-success shadow">
            <i class="fa-solid fa-file-excel me-1"></i> Excel'e Aktar
        </a>

        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 1): ?>
            <a href="add_user.php" class="btn btn-primary shadow">
                <i class="fa-solid fa-user-plus me-1"></i> Yeni Personel Ekle
            </a>
        <?php endif; ?>
    </div>
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

<div class="card shadow-sm mb-4 border-0 bg-light">
    <div class="card-body py-3">
        <form method="GET" action="index.php" class="row g-2 align-items-center">
            
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" 
                           placeholder="Personel adı veya soyadı ara..." 
                           value="<?php echo htmlspecialchars($search_keyword); ?>">
                </div>
            </div>

            <div class="col-md-3">
                <select name="department" class="form-select">
                    <option value="">Tüm Departmanlar</option>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?php echo $dept['id']; ?>" 
                            <?php echo ($filter_dept == $dept['id']) ? 'selected' : ''; ?>>
                            <?php echo $dept['name']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100">Filtrele</button>
                
                <?php if(!empty($search_keyword) || !empty($filter_dept)): ?>
                    <a href="index.php" class="btn btn-secondary" title="Filtreleri Temizle">
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>
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
                    <?php if (count($users) > 0): // Eğer kayıt varsa listele ?>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td>#<?php echo $user['id']; ?></td>
                            
                            <td class="fw-bold">
                                <div class="d-flex align-items-center">
                                    
                                    <?php if (!empty($user['profile_pic']) && file_exists('uploads/' . $user['profile_pic'])): ?>
                                        <img src="uploads/<?php echo $user['profile_pic']; ?>" 
                                            alt="Profil" 
                                            class="rounded-circle me-2 border" 
                                            style="width: 40px; height: 40px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="avatar bg-secondary text-white rounded-circle me-2 d-flex justify-content-center align-items-center" 
                                            style="width: 40px; height: 40px; font-size: 14px;">
                                            <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                                        </div>
                                    <?php endif; ?>
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
                    
                    <?php else: // Eğer kayıt YOKSA bunu göster ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-magnifying-glass fa-2x mb-3"></i><br>
                                Aradığınız kriterlere uygun personel bulunamadı.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>