<?php
// db.php - Veritabanı Bağlantı Dosyası

$host = '127.0.0.1';
$db   = 'minihr';
$user = 'root';
$pass = 'sifreniz'; // Laragon'da varsayılan şifre boştur
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Hataları göster
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Veriyi dizi olarak getir
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Güvenlik için (SQL Injection önlemi)
];

try {
    // Bağlantıyı kurmaya çalışıyoruz
    $pdo = new PDO($dsn, $user, $pass, $options);
    // Eğer buraya kadar hata vermediyse bağlantı başarılıdır ama ekrana bir şey yazdırmıyoruz.
    // Çünkü bu dosya diğer sayfaların içine gömülecek.
} catch (\PDOException $e) {
    // Hata olursa ekrana bunu yaz
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>