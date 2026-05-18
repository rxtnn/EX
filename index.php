<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Водить.РФ — обучение вождению речного транспорта';
include __DIR__ . '/includes/header.php';
?>
<div class="container">
    <!-- Слайдер: 4 изображения, авто-переключение каждые 3 сек -->
    <section id="heroSlider" class="hero-slider fade-in-up mb-5">
        <div class="slides">
            <div class="slide" style="background-image:url('<?= e(url('assets/images/hero1.jpg')) ?>')">
                <div class="caption">
                    <h1>Курсы вождения речного транспорта</h1>
                    <p class="lead">Катер, круизный лайнер, яхта — научим управлять уверенно</p>
                </div>
            </div>
            <div class="slide" style="background-image:url('<?= e(url('assets/images/hero2.jpg')) ?>')">
                <div class="caption">
                    <h1>Опытные инструкторы</h1>
                    <p class="lead">Программы для новичков и продолжающих</p>
                </div>
            </div>
            <div class="slide" style="background-image:url('<?= e(url('assets/images/hero3.jpg')) ?>')">
                <div class="caption">
                    <h1>Практика на современной технике</h1>
                    <p class="lead">Полный цикл: теория, тренажер, выход на воду</p>
                </div>
            </div>
            <div class="slide" style="background-image:url('<?= e(url('assets/images/hero4.jpg')) ?>')">
                <div class="caption">
                    <h1>Удобная запись онлайн</h1>
                    <p class="lead">Подайте заявку за пару минут</p>
                </div>
            </div>
        </div>
        <button class="slider-btn prev" type="button" aria-label="Назад"><i class="bi bi-chevron-left"></i></button>
        <button class="slider-btn next" type="button" aria-label="Вперед"><i class="bi bi-chevron-right"></i></button>
        <div class="dots">
            <button class="dot" type="button" aria-label="1"></button>
            <button class="dot" type="button" aria-label="2"></button>
            <button class="dot" type="button" aria-label="3"></button>
            <button class="dot" type="button" aria-label="4"></button>
        </div>
    </section>

    <!-- О сервисе -->
    <section class="mb-5">
        <div class="row g-4">
            <div class="col-md-4 fade-in-up">
                <div class="feature-icon"><i class="bi bi-water"></i></div>
                <h3>Три направления</h3>
                <p>Курсы по вождению катеров, круизных лайнеров и яхт. Выбирайте удобный формат.</p>
            </div>
            <div class="col-md-4 fade-in-up">
                <div class="feature-icon"><i class="bi bi-calendar-check"></i></div>
                <h3>Гибкое расписание</h3>
                <p>Указывайте удобную дату старта — мы подберем подходящую группу.</p>
            </div>
            <div class="col-md-4 fade-in-up">
                <div class="feature-icon"><i class="bi bi-shield-check"></i></div>
                <h3>Официальное обучение</h3>
                <p>Очные занятия, опытные инструкторы и удобные способы оплаты.</p>
            </div>
        </div>
    </section>

    <!-- Виды транспорта -->
    <section class="mb-5">
        <h2 class="text-center mb-4">Виды транспорта</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card transport-card">
                    <img src="<?= e(url('assets/images/transport1.jpg')) ?>" alt="Катер">
                    <div class="card-body">
                        <h3 class="card-title">Катер</h3>
                        <p class="card-text text-muted">Базовый курс для уверенного управления катером на реке.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card transport-card">
                    <img src="<?= e(url('assets/images/transport2.jpg')) ?>" alt="Круизный лайнер">
                    <div class="card-body">
                        <h3 class="card-title">Круизный лайнер</h3>
                        <p class="card-text text-muted">Продвинутая программа для управления крупными судами.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card transport-card">
                    <img src="<?= e(url('assets/images/transport3.jpg')) ?>" alt="Яхта">
                    <div class="card-body">
                        <h3 class="card-title">Яхта</h3>
                        <p class="card-text text-muted">Парусное и моторное вождение: теория и практика на воде.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="mb-5">
        <div class="bg-primary-deep text-white p-4 p-md-5 rounded-3 text-center fade-in-up">
            <h2 class="text-white mb-3">Готовы начать?</h2>
            <p class="mb-4">Зарегистрируйтесь и подайте заявку на обучение прямо сейчас.</p>
            <?php if (is_user()): ?>
                <a href="<?= e(url('apply.php')) ?>" class="btn btn-turquoise btn-lg pulse"><i class="bi bi-file-earmark-plus"></i> Подать заявку</a>
            <?php else: ?>
                <a href="<?= e(url('register.php')) ?>" class="btn btn-turquoise btn-lg me-2 pulse"><i class="bi bi-person-plus"></i> Регистрация</a>
                <a href="<?= e(url('login.php')) ?>" class="btn btn-light btn-lg">Войти</a>
            <?php endif; ?>
        </div>
    </section>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
