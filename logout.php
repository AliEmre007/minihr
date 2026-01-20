<?php
session_start();
session_destroy(); // Tüm oturum verisini sil (Bilekliği kes)
header("Location: login.php"); // Giriş sayfasına geri at
exit;
?>