<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';
require_user();

$uid = current_user_id();
$appId = (int)($_GET['app'] ?? $_POST['app'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM applications WHERE id = ? AND user_id = ?');
$stmt->execute([$appId, $uid]);
$app = $stmt->fetch();
if (!$app) { http_response_code(404); die('Заявка не найдена.'); }
if ($app['status'] === 'Новая') { die('Отзыв можно оставить только после изменения статуса администратором.'); }

$check = $pdo->prepare('SELECT id FROM reviews WHERE application_id = ?');
$check->execute([$appId]);
if ($check->fetch()) { header('Location: /exam/profile.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = (int)($_POST['rating'] ?? 0);
    $text = trim($_POST['text'] ?? '');
    if ($rating < 1 || $rating > 5) $error = 'Поставьте оценку от 1 до 5.';
    elseif (mb_strlen($text) < 5) $error = 'Опишите впечатления (минимум 5 символов).';
    else {
        $pdo->prepare('INSERT INTO reviews (application_id, user_id, rating, text) VALUES (?,?,?,?)')
            ->execute([$appId, $uid, $rating, $text]);
        flash_set('ok', 'Спасибо за отзыв!');
        header('Location: /exam/profile.php'); exit;
    }
}

$pageTitle = 'Отзыв — Водить.РФ';
include __DIR__ . '/includes/header.php';
?>
<div class="container">
    <div class="row justify-content-center fade-in-up">
        <div class="col-md-7 col-lg-6">
            <div class="form-card">
                <h1 class="mb-1 text-primary-deep"><i class="bi bi-chat-square-text"></i> Отзыв</h1>
                <p class="text-muted mb-4">Заявка #<?= (int)$app['id'] ?> — <?= e($app['transport_type']) ?></p>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= e($error) ?></div>
                <?php endif; ?>

                <form method="post" novalidate>
                    <input type="hidden" name="app" value="<?= (int)$app['id'] ?>">
                    <input type="hidden" name="rating" value="0">

                    <div class="mb-3">
                        <label class="form-label d-block">Оценка</label>
                        <div class="rating-input">
                            <i class="bi bi-star-fill star"></i>
                            <i class="bi bi-star-fill star"></i>
                            <i class="bi bi-star-fill star"></i>
                            <i class="bi bi-star-fill star"></i>
                            <i class="bi bi-star-fill star"></i>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Ваши впечатления</label>
                        <textarea name="text" class="form-control" rows="4" required></textarea>
                    </div>

                    <button class="btn btn-forest w-100"><i class="bi bi-send"></i> Отправить отзыв</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
