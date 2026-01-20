<?php
// Oturumu başlat
session_start();
require 'db.php';

// KURAL 1: Eğer kullanıcı ZATEN giriş yapmışsa, Login sayfasında ne işi var?
// Onu hemen ana sayfaya (index.php) gönder.
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// NOT: Buraya "Giriş yapmamışsa login.php'ye git" kodu ASLA KONMAZ.
// Çünkü zaten şu an login.php'deyiz! Kendi kendine yönlendirme döngüye sokar.

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if ($user && $password === $user['password']) {
        // Giriş Başarılı -> Bilekliği Tak
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['user_role'] = $user['role_id'];

        header("Location: index.php");
        exit;
    } else {
        $error = "Hatalı e-posta veya şifre!";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Giriş Yap - MiniHR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f5f5f5; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .login-card { width: 100%; max-width: 400px; padding: 20px; border-radius: 10px; }
    </style>
</head>
<body>

<div class="card login-card shadow">
    <div class="card-body">
        <h3 class="text-center mb-4">MiniHR Giriş</h3>
        
        <?php if($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label>E-posta Adresi</label>
                <input type="email" name="email" class="form-control" required placeholder="admin@sirket.com">
            </div>
            <div class="mb-3">
                <label>Şifre</label>
                <input type="password" name="password" class="form-control" required placeholder="1234">
            </div>
            <button type="submit" class="btn btn-primary w-100">Giriş Yap</button>
        </form>
    </div>
</div>

</body>
</html>