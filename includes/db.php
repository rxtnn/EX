<?php
// Подключение к базе данных через PDO
// Локальные креды можно переопределить в includes/db.local.php (он в .gitignore)
$DB_HOST = '127.0.0.1';
$DB_PORT = 3306;
$DB_NAME = 'vodit_rf';
$DB_USER = 'root';
$DB_PASS = '';

if (is_file(__DIR__ . '/db.local.php')) {
    require __DIR__ . '/db.local.php';
}

try {
    $pdo = new PDO(
        "mysql:host={$DB_HOST};port={$DB_PORT};dbname={$DB_NAME};charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $ex) {
    http_response_code(500);
    echo '<!doctype html><meta charset="utf-8"><div style="font-family:sans-serif;padding:24px;max-width:720px;margin:40px auto;background:#fff3f3;border:1px solid #f5b0b0;border-radius:8px">';
    echo '<h2 style="color:#b00">Не удалось подключиться к базе данных</h2>';
    echo '<p>' . htmlspecialchars($ex->getMessage()) . '</p>';
    echo '<p><b>Что проверить:</b></p><ol>';
    echo '<li>Запущен ли сервис MySQL (XAMPP/OSPanel/Open Server)?</li>';
    echo '<li>Импортирован ли <code>install.sql</code> через phpMyAdmin? Должна появиться БД <code>vodit_rf</code>.</li>';
    echo '<li>Совпадают ли логин/пароль MySQL в <code>includes/db.php</code> (по умолчанию <code>root</code> без пароля)?</li>';
    echo '<li>Можно создать файл <code>includes/db.local.php</code> и переопределить $DB_USER/$DB_PASS, чтобы не править основной файл.</li>';
    echo '</ol></div>';
    exit;
}
