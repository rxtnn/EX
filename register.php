<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';

if (is_user()) { header('Location: /exam/profile.php'); exit; }

$errors = [];
$old = ['login'=>'','fio'=>'','birthdate'=>'','phone'=>'','email'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';
    $fio = trim($_POST['fio'] ?? '');
    $birthdate = $_POST['birthdate'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $old = compact('login','fio','birthdate','phone','email');

    if ($e = validate_login($login)) $errors['login'] = $e;
    if ($e = validate_password($password)) $errors['password'] = $e;
    if ($fio === '') $errors['fio'] = 'Введите ФИО.';
    if ($birthdate === '' || !strtotime($birthdate)) $errors['birthdate'] = 'Укажите корректную дату рождения.';
    if ($e = validate_phone($phone)) $errors['phone'] = $e;
    if ($e = validate_email($email)) $errors['email'] = $e;

    if (!$errors) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE login = ?');
        $stmt->execute([$login]);
        if ($stmt->fetch()) {
            $errors['login'] = 'Логин уже занят.';
        }
    }
    if (!$errors) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $pdo->prepare('INSERT INTO users (login,password,fio,birthdate,phone,email) VALUES (?,?,?,?,?,?)')
            ->execute([$login, $hash, $fio, $birthdate, $phone, $email]);
        $_SESSION['user_id'] = (int)$pdo->lastInsertId();
        $_SESSION['user_login'] = $login;
        flash_set('ok', 'Регистрация прошла успешно. Добро пожаловать!');
        header('Location: /exam/profile.php'); exit;
    }
}

$pageTitle = 'Регистрация — Водить.РФ';
include __DIR__ . '/includes/header.php';
?>
<div class="container">
    <div class="row justify-content-center fade-in-up">
        <div class="col-md-7 col-lg-6">
            <div class="form-card">
                <h1 class="mb-1 text-primary-deep"><i class="bi bi-person-plus"></i> Регистрация</h1>
                <p class="text-muted mb-4">Создайте аккаунт для записи на курсы</p>

                <form method="post" id="regForm" novalidate>
                    <div class="mb-3">
                        <label class="form-label">Логин <span class="text-danger">*</span></label>
                        <input type="text" name="login" class="form-control <?= isset($errors['login']) ? 'is-invalid' : '' ?>" value="<?= e($old['login']) ?>" required>
                        <div class="field-hint">Латинские буквы и цифры, минимум 6 символов.</div>
                        <div class="field-error <?= isset($errors['login']) ? 'show' : '' ?>" data-err="login"><?= e($errors['login'] ?? '') ?></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Пароль <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" required>
                        <div class="field-hint">Минимум 8 символов.</div>
                        <div class="field-error <?= isset($errors['password']) ? 'show' : '' ?>" data-err="password"><?= e($errors['password'] ?? '') ?></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">ФИО <span class="text-danger">*</span></label>
                        <input type="text" name="fio" class="form-control <?= isset($errors['fio']) ? 'is-invalid' : '' ?>" value="<?= e($old['fio']) ?>" required>
                        <div class="field-error <?= isset($errors['fio']) ? 'show' : '' ?>" data-err="fio"><?= e($errors['fio'] ?? '') ?></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Дата рождения <span class="text-danger">*</span></label>
                        <input type="date" name="birthdate" class="form-control <?= isset($errors['birthdate']) ? 'is-invalid' : '' ?>" value="<?= e($old['birthdate']) ?>" required>
                        <div class="field-error <?= isset($errors['birthdate']) ? 'show' : '' ?>" data-err="birthdate"><?= e($errors['birthdate'] ?? '') ?></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Телефон <span class="text-danger">*</span></label>
                        <input type="text" name="phone" class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>" placeholder="+7 (___) ___-__-__" value="<?= e($old['phone']) ?>" required>
                        <div class="field-error <?= isset($errors['phone']) ? 'show' : '' ?>" data-err="phone"><?= e($errors['phone'] ?? '') ?></div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">E-mail <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" value="<?= e($old['email']) ?>" required>
                        <div class="field-error <?= isset($errors['email']) ? 'show' : '' ?>" data-err="email"><?= e($errors['email'] ?? '') ?></div>
                    </div>

                    <button class="btn btn-primary-deep w-100 mb-3"><i class="bi bi-check2-circle"></i> Зарегистрироваться</button>
                    <p class="text-center mb-0">
                        Уже зарегистрированы? <a href="/exam/login.php" class="text-primary-deep fw-medium">Войти</a>
                    </p>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
