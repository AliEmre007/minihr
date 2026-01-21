<?php
// export.php - Excel (CSV) Çıktısı Alma
session_start();
require 'db.php';

// Güvenlik: Giriş yapmayan indiremez
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Dosya adını belirle (örn: personel_listesi_2023-10-25.csv)
$filename = "personel_listesi_" . date('Y-m-d') . ".csv";

// Verileri Çek (Join ile departman ve rol isimlerini de alıyoruz)
$sql = "SELECT users.id, users.full_name, users.email, departments.name as dept_name, roles.name as role_name, users.salary, users.created_at 
        FROM users 
        LEFT JOIN roles ON users.role_id = roles.id 
        LEFT JOIN departments ON users.department_id = departments.id
        ORDER BY users.id ASC";
$stmt = $pdo->query($sql);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- İNDİRME AYARLARI (HEADER) ---
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Dosya yazma modunu aç (php://output doğrudan tarayıcıya gönderir)
$output = fopen('php://output', 'w');

// Türkçe karakter sorunu olmaması için BOM (Byte Order Mark) ekle
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// 1. Satır: Başlıklar
fputcsv($output, ['ID', 'Ad Soyad', 'E-posta', 'Departman', 'Rol', 'Maaş', 'Kayıt Tarihi'], ";");

// 2. Satır ve sonrası: Veriler
foreach ($users as $user) {
    fputcsv($output, [
        $user['id'],
        $user['full_name'],
        $user['email'],
        $user['dept_name'],
        $user['role_name'],
        $user['salary'],
        $user['created_at']
    ], ";");
}

fclose($output);
exit;
?>