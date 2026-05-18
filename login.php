<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';

if (is_admin()) { header('Location: ' . url('admin/index.php')); exit; }
if (is_user())  { header('Location: ' . url('profile.php'));     exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($login === '' || $password === '') {
        $error = 'Введите логин и пароль.';
    } else {
        // Fallback на жёстко прописанные креды администратора из ТЗ (Admin26 / Demo20)
        if ($login === 'Admin26' && $password === 'Demo20') {
            $_SESSION['is_admin']    = true;
            $_SESSION['admin_login'] = 'Admin26';
            header('Location: ' . url('admin/index.php')); exit;
        }

        $stmt = $pdo->prepare('SELECT * FROM users WHERE login = ?');
        $stmt->execute([$login]);
        $u = $stmt->fetch();
        if ($u && password_verify($password, $u['password'])) {
            // Если это администратор — открываем сессию админа и ведём в админ-панель
            if (!empty($u['is_admin'])) {
                $_SESSION['is_admin']    = true;
                $_SESSION['admin_login'] = $u['login'];
                header('Location: ' . url('admin/index.php')); exit;
            }
            $_SESSION['user_id']    = (int)$u['id'];
            $_SESSION['user_login'] = $u['login'];
            header('Location: ' . url('profile.php')); exit;
        } else {
            $error = 'Неверный логин или пароль.';
        }
    }
}

$pageTitle = 'Вход — Водить.РФ';
include __DIR__ . '/includes/header.php';
?>
<div class="container">
    <div class="row justify-content-center fade-in-up">
        <div class="col-md-6 col-lg-5">
            <div class="form-card">
                <h1 class="mb-1 text-primary-deep"><i class="bi bi-box-arrow-in-right"></i> Вход</h1>
                <p class="text-muted mb-4">Войдите, чтобы подать заявку</p>

                <?php if ($error): ?>
                    <div class="alert alert-danger d-flex align-items-center" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <div><?= e($error) ?></div>
                    </div>
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
                    <button class="btn btn-primary-deep w-100 mb-3"><i class="bi bi-box-arrow-in-right"></i> Войти</button>
                    <p class="text-center mb-0">
                        Еще не зарегистрированы? <a href="<?= e(url('register.php')) ?>" class="text-primary-deep fw-medium">Регистрация</a>
                    </p>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
