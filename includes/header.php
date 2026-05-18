<?php
require_once __DIR__ . '/functions.php';
$pageTitle = $pageTitle ?? 'Водить.РФ — обучение вождению речного транспорта';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary-deep sticky-top shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center" href="<?= e(url('index.php')) ?>">
            <img src="<?= e(url('assets/images/boat.png')) ?>" alt="Водить.РФ" class="brand-logo me-2">
            <span>Водить.РФ</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="nav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="<?= e(url('index.php')) ?>"><i class="bi bi-house"></i> Главная</a></li>
                <?php if (is_user()): ?>
                    <li class="nav-item"><a class="nav-link" href="<?= e(url('apply.php')) ?>"><i class="bi bi-file-earmark-plus"></i> Подать заявку</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= e(url('profile.php')) ?>"><i class="bi bi-person-circle"></i> Личный кабинет</a></li>
                <?php endif; ?>
                <?php if (is_admin()): ?>
                    <li class="nav-item"><a class="nav-link" href="<?= e(url('admin/index.php')) ?>"><i class="bi bi-shield-check"></i> Админ-панель</a></li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav">
                <?php if (is_user()): ?>
                    <li class="nav-item"><span class="navbar-text me-2 text-white-50">Здравствуйте, <?= e($_SESSION['user_login'] ?? '') ?></span></li>
                    <li class="nav-item"><a class="btn btn-light btn-sm" href="<?= e(url('logout.php')) ?>"><i class="bi bi-box-arrow-right"></i> Выйти</a></li>
                <?php elseif (is_admin()): ?>
                    <li class="nav-item"><span class="navbar-text me-2 text-white-50"><?= e($_SESSION['admin_login'] ?? 'Admin26') ?></span></li>
                    <li class="nav-item"><a class="btn btn-light btn-sm" href="<?= e(url('admin/logout.php')) ?>"><i class="bi bi-box-arrow-right"></i> Выйти</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="<?= e(url('login.php')) ?>">Войти</a></li>
                    <li class="nav-item"><a class="btn btn-turquoise btn-sm ms-lg-2" href="<?= e(url('register.php')) ?>">Регистрация</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<main class="py-4">
