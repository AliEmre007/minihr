<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MiniHR - Personel Yönetimi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
  <div class="container">
    <a class="navbar-brand" href="index.php"><i class="fa-solid fa-users-gear me-2"></i>MiniHR</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link active" href="index.php">Ana Sayfa</a></li>
        <li class="nav-item"><a class="nav-link" href="#">İzin Talepleri</a></li>
        <li class="nav-item"><a class="nav-link btn btn-danger text-white ms-2" href="logout.php">Çıkış Yap</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container"> ```

**2. Dosya:** `includes/footer.php` oluştur ve içine şunu yapıştır:

```php
</div> <footer class="text-center mt-5 py-4 text-muted">
    <p>&copy; <?php echo date('Y'); ?> MiniHR Yazılım Sistemleri. Tüm hakları saklıdır.</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>