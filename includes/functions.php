<?php
// includes/functions.php

// 1. CSRF Token Üretici (Formu oluştururken çağıracağız)
function create_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        // 32 karakterlik rastgele, tahmin edilemez bir şifre üret
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// 2. CSRF Token Kontrolcüsü (Form gönderildiğinde çağıracağız)
function validate_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        // Token yoksa veya eşleşmiyorsa işlemi durdur!
        die("Güvenlik Hatası: CSRF Token Geçersiz! (Sayfayı yenileyip tekrar deneyin)");
    }
    return true;
}

// 3. XSS Temizleyici (Ekrana basarken kullanacağız)
function clean($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}
?>