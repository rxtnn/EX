<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ---------- Авто-определение базового URL ----------
// Работает и при деплое в /exam/, и при запуске сервера из корня проекта.
if (!defined('BASE_URL')) {
    $sn = $_SERVER['SCRIPT_NAME'] ?? '/';
    $sn = preg_replace('#/admin/[^/]+\.php$#', '', $sn);
    $sn = preg_replace('#/[^/]+\.php$#', '', $sn);
    define('BASE_URL', $sn === '/' ? '' : rtrim($sn, '/'));
}

function url(string $path = ''): string {
    return BASE_URL . '/' . ltrim($path, '/');
}

function e(?string $s): string {
    return htmlspecialchars((string)$s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function is_user(): bool {
    return !empty($_SESSION['user_id']);
}

function current_user_id(): ?int {
    return is_user() ? (int)$_SESSION['user_id'] : null;
}

function is_admin(): bool {
    return !empty($_SESSION['is_admin']);
}

function require_user(): void {
    if (!is_user()) {
        header('Location: ' . url('login.php'));
        exit;
    }
}

function require_admin(): void {
    if (!is_admin()) {
        header('Location: ' . url('admin/login.php'));
        exit;
    }
}

function validate_login(string $login): ?string {
    if (mb_strlen($login) < 6) return 'Логин не короче 6 символов.';
    if (!preg_match('/^[A-Za-z0-9]+$/', $login)) return 'Логин — только латинские буквы и цифры.';
    return null;
}

function validate_password(string $pw): ?string {
    if (mb_strlen($pw) < 8) return 'Пароль не короче 8 символов.';
    return null;
}

function validate_phone(string $p): ?string {
    if (!preg_match('/^\+?[0-9\s\-\(\)]{10,20}$/', $p)) return 'Неверный формат телефона.';
    return null;
}

function validate_email(string $em): ?string {
    if (!filter_var($em, FILTER_VALIDATE_EMAIL)) return 'Неверный формат e-mail.';
    return null;
}

function flash_set(string $key, string $msg): void {
    $_SESSION['flash'][$key] = $msg;
}

function flash_get(string $key): ?string {
    if (!empty($_SESSION['flash'][$key])) {
        $m = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $m;
    }
    return null;
}

function status_badge(string $s): string {
    return match ($s) {
        'Новая' => 'bg-primary',
        'Идет обучение' => 'bg-warning text-dark',
        'Обучение завершено' => 'bg-success',
        default => 'bg-secondary',
    };
}
