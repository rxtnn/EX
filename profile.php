<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';
require_user();

$uid = current_user_id();
$user = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$user->execute([$uid]);
$user = $user->fetch();

$stmt = $pdo->prepare('
    SELECT a.*, r.id AS review_id, r.rating, r.text AS review_text
    FROM applications a
    LEFT JOIN reviews r ON r.application_id = a.id
    WHERE a.user_id = ?
    ORDER BY a.created_at DESC
');
$stmt->execute([$uid]);
$apps = $stmt->fetchAll();

$flashOk = flash_get('ok');

$pageTitle = 'Личный кабинет — Водить.РФ';
include __DIR__ . '/includes/header.php';
?>
<div class="container">
    <?php if ($flashOk): ?>
        <div class="position-fixed top-0 end-0 p-3" style="z-index:1080">
            <div class="toast align-items-center text-white bg-success border-0" role="alert">
                <div class="d-flex">
                    <div class="toast-body"><i class="bi bi-check-circle me-1"></i><?= e($flashOk) ?></div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Заголовок профиля -->
    <div class="profile-summary fade-in-up mb-4">
        <div class="row align-items-center g-3">
            <div class="col-auto">
                <span class="avatar"><i class="bi bi-person"></i></span>
            </div>
            <div class="col">
                <h1 class="text-white mb-1"><?= e($user['fio']) ?></h1>
                <div class="small">
                    <i class="bi bi-at"></i> <?= e($user['login']) ?> &nbsp;·&nbsp;
                    <i class="bi bi-envelope"></i> <?= e($user['email']) ?> &nbsp;·&nbsp;
                    <i class="bi bi-telephone"></i> <?= e($user['phone']) ?>
                </div>
            </div>
            <div class="col-auto">
                <a href="/exam/apply.php" class="btn btn-light"><i class="bi bi-plus-circle"></i> Новая заявка</a>
            </div>
        </div>
    </div>

    <!-- Слайдер личного кабинета -->
    <section id="profileSlider" class="hero-slider fade-in-up mb-4">
        <div class="slides">
            <div class="slide" style="background-image:url('/exam/assets/images/hero2.jpg')"><div class="caption"><h2 class="text-white">Добро пожаловать!</h2></div></div>
            <div class="slide" style="background-image:url('/exam/assets/images/hero3.jpg')"><div class="caption"><h2 class="text-white">Следите за статусом заявок</h2></div></div>
            <div class="slide" style="background-image:url('/exam/assets/images/hero1.jpg')"><div class="caption"><h2 class="text-white">Оставляйте отзывы после обучения</h2></div></div>
            <div class="slide" style="background-image:url('/exam/assets/images/hero4.jpg')"><div class="caption"><h2 class="text-white">Записывайтесь онлайн</h2></div></div>
        </div>
        <button class="slider-btn prev" type="button"><i class="bi bi-chevron-left"></i></button>
        <button class="slider-btn next" type="button"><i class="bi bi-chevron-right"></i></button>
        <div class="dots">
            <button class="dot" type="button"></button><button class="dot" type="button"></button>
            <button class="dot" type="button"></button><button class="dot" type="button"></button>
        </div>
    </section>

    <h2 class="mb-3"><i class="bi bi-list-check"></i> История заявок</h2>

    <?php if (!$apps): ?>
        <div class="alert alert-info">У вас пока нет заявок. <a href="/exam/apply.php" class="alert-link">Подать заявку</a></div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($apps as $a):
                $statusKey = $a['status'] === 'Новая' ? 'новая' : ($a['status'] === 'Идет обучение' ? 'идет' : 'завершено');
                $canReview = !$a['review_id'] && $a['status'] !== 'Новая';
            ?>
                <div class="col-12">
                    <div class="card app-card status-<?= e($statusKey) ?> fade-in-up">
                        <div class="card-body">
                            <div class="row g-2 align-items-center">
                                <div class="col-md-3">
                                    <div class="text-muted small">Заявка #<?= (int)$a['id'] ?></div>
                                    <h3 class="mb-0"><i class="bi bi-water"></i> <?= e($a['transport_type']) ?></h3>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-muted small">Дата начала</div>
                                    <div><?= e(date('d.m.Y', strtotime($a['start_date']))) ?></div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-muted small">Оплата</div>
                                    <div><?= e($a['payment_method']) ?></div>
                                </div>
                                <div class="col-md-3 text-md-end">
                                    <span class="badge <?= status_badge($a['status']) ?> mb-2"><?= e($a['status']) ?></span>
                                    <div class="text-muted small">Подана <?= e(date('d.m.Y H:i', strtotime($a['created_at']))) ?></div>
                                </div>
                            </div>

                            <?php if ($a['review_id']): ?>
                                <hr>
                                <div class="d-flex align-items-start">
                                    <div class="me-3"><i class="bi bi-chat-square-quote text-primary-deep fs-4"></i></div>
                                    <div>
                                        <div class="rating-stars">
                                            <?php for ($i=1; $i<=5; $i++): ?>
                                                <i class="bi bi-star<?= $i <= (int)$a['rating'] ? '-fill' : '' ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                        <div class="text-muted small">Ваш отзыв:</div>
                                        <div><?= nl2br(e($a['review_text'])) ?></div>
                                    </div>
                                </div>
                            <?php elseif ($canReview): ?>
                                <hr>
                                <a href="/exam/review.php?app=<?= (int)$a['id'] ?>" class="btn btn-forest btn-sm">
                                    <i class="bi bi-chat-square-text"></i> Оставить отзыв
                                </a>
                            <?php else: ?>
                                <hr>
                                <div class="text-muted small"><i class="bi bi-info-circle"></i> Отзыв можно оставить после изменения статуса заявки администратором.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
