<?php
require_once __DIR__ . '/../includes/functions.php';

if (is_admin()) { header('Location: /exam/admin/index.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($login === 'Admin26' && $password === 'Demo20') {
        $_SESSION['is_admin'] = true;
        header('Location: /exam/admin/index.php'); exit;
    }
    $error = 'Неверный логин или пароль администратора.';
}

$pageTitle = 'Вход администратора';
include __DIR__ . '/../includes/header.php';
?>
<div class="container">
    <div class="row justify-content-center fade-in-up">
        <div class="col-md-6 col-lg-5">
            <div class="form-card">
                <h1 class="mb-1 text-primary-deep"><i class="bi bi-shield-lock"></i> Админ-панель</h1>
                <p class="text-muted mb-4">Введите учетные данные администратора</p>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-1"></i><?= e($error) ?></div>
                <?php endif; ?>

                <form method="post" novalidate>
                    <div class="mb-3">
                        <label class="form-label">Логин</label>
                        <input type="text" name="login" class="form-control" autofocus required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Пароль</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button class="btn btn-primary-deep w-100"><i class="bi bi-box-arrow-in-right"></i> Войти</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
