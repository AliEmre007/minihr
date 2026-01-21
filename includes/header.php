<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mini İK Paneli</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8f9fa; }
        .avatar-small { width: 32px; height: 32px; object-fit: cover; border-radius: 50%; }
        .navbar-brand { font-weight: 600; letter-spacing: -0.5px; }
    </style>
</head>
<body>

<?php
// Giriş yapan kullanıcının güncel bilgilerini (özellikle resmini) çekelim
// Not: session_start() ve db.php zaten ana dosyalarda çağrılıyor.
if (isset($_SESSION['user_id'])) {
    $current_user_stmt = $pdo->prepare("SELECT full_name, profile_pic, role_id FROM users WHERE id = ?");
    $current_user_stmt->execute([$_SESSION['user_id']]);
    $current_user = $current_user_stmt->fetch();
}
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4 shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <i class="fa-solid fa-user-group me-2"></i>Mini İK
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link active" href="index.php">Ana Sayfa</a>
                </li>
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 1): ?>
                <li class="nav-item">
                    <a class="nav-link" href="add_user.php">Personel Ekle</a>
                </li>
                <?php endif; ?>
            </ul>

            <?php if (isset($_SESSION['user_id'])): ?>
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown">
                        
                        <?php if (!empty($current_user['profile_pic']) && file_exists('uploads/' . $current_user['profile_pic'])): ?>
                            <img src="uploads/<?php echo $current_user['profile_pic']; ?>" class="avatar-small border border-light">
                        <?php else: ?>
                            <div class="avatar-small bg-secondary text-white d-flex justify-content-center align-items-center small">
                                <?php echo strtoupper(substr($current_user['full_name'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>

                        <span><?php echo htmlspecialchars($current_user['full_name']); ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow">
                        <li><span class="dropdown-header">Hesabım</span></li>
                        <li><a class="dropdown-item" href="edit_user.php?id=<?php echo $_SESSION['user_id']; ?>">
                            <i class="fa-solid fa-user-gear me-2"></i>Profilimi Düzenle
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php">
                            <i class="fa-solid fa-right-from-bracket me-2"></i>Çıkış Yap
                        </a></li>
                    </ul>
                </li>
            </ul>
            <?php endif; ?>
            
        </div>
    </div>
</nav>

<div class="container">