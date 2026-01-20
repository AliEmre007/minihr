<?php
// login.php - GÜNCELLENMİŞ VERSİYON
session_start();
require 'db.php';

// Zaten giriş yapmışsa içeri al
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Kullanıcıyı e-postaya göre bul
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    // DÜZELTME BURADA:
    // password_verify fonksiyonu, girilen "1234" şifresini alır,
    // veritabanındaki hash ile matematiksel olarak eşleşip eşleşmediğine bakar.
    if ($user && password_verify($password, $user['password'])) {
        
        // Giriş Başarılı
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['user_role'] = $user['role_id'];

        header("Location: index.php");
        exit;
    } else {
        // Güvenlik için detay vermiyoruz (Email mi yanlış şifre mi söyleme)
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
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Şifre</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Giriş Yap</button>
        </form>
    </div>
</div>

</body>
</html>