<?php
session_start();
require 'db.php';

// 1. GÜVENLİK: Sadece Admin silebilir
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 1) {
    die("Yetkisiz işlem!");
}

// 2. ID KONTROLÜ: URL'de id var mı? (delete.php?id=5 gibi)
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // 3. MANTIK KONTROLÜ: Admin kendini siliyor mu?
    if ($id == $_SESSION['user_id']) {
        // Hata ile geri gönder
        header("Location: index.php?status=error&message=Kendini silemezsin!");
        exit;
    }

    // 4. SİLME İŞLEMİ
    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
        $stmt->execute(['id' => $id]);
        
        // Başarılı mesajıyla geri gönder
        header("Location: index.php?status=success&message=Personel silindi");
        exit;

    } catch (PDOException $e) {
        die("Silme hatası: " . $e->getMessage());
    }
} else {
    // ID gönderilmediyse ana sayfaya at
    header("Location: index.php");
    exit;
}
?>