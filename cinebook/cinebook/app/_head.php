<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#141a30">
    <title><?= h($pageTitle ?? SITE_NAME) ?> — <?= SITE_NAME ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= $cssPath ?? '' ?>css/app.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark an-navbar">
    <div class="container">
        <a class="navbar-brand an-brand" href="<?= $rootPath ?? '' ?>index.php">
            <i class="bi bi-basket-fill"></i> <?= SITE_NAME ?>
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                <li class="nav-item"><a class="nav-link" href="<?= $rootPath ?? '' ?>index.php"><i class="bi bi-house-fill me-1"></i>Home</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= $rootPath ?? '' ?>index.php#stalls"><i class="bi bi-shop me-1"></i>Stalls</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= $rootPath ?? '' ?>track.php"><i class="bi bi-broadcast me-1"></i>Track Order</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= $rootPath ?? '' ?>admin/index.php"><i class="bi bi-speedometer2 me-1"></i>Admin</a></li>
            </ul>
        </div>
    </div>
</nav>
