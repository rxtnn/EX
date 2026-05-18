<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';
require_user();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tt = $_POST['transport_type'] ?? '';
    $date = $_POST['start_date'] ?? '';
    $pm = $_POST['payment_method'] ?? '';
    $allowedT = ['Катер','Круизный лайнер','Яхта'];
    $allowedP = ['Предоплата по QR-коду','Оплата картой МИР','Постоплата в офисе'];

    if (!in_array($tt, $allowedT, true)) $error = 'Выберите вид транспорта.';
    elseif (!$date || !strtotime($date)) $error = 'Укажите корректную дату.';
    elseif (!in_array($pm, $allowedP, true)) $error = 'Выберите способ оплаты.';
    else {
        $pdo->prepare('INSERT INTO applications (user_id, transport_type, start_date, payment_method) VALUES (?,?,?,?)')
            ->execute([current_user_id(), $tt, $date, $pm]);
        flash_set('ok', 'Заявка отправлена администратору на согласование.');
        header('Location: ' . url('profile.php')); exit;
    }
}

$pageTitle = 'Подать заявку — Водить.РФ';
include __DIR__ . '/includes/header.php';
?>
<div class="container">
    <div class="row justify-content-center fade-in-up">
        <div class="col-md-7 col-lg-6">
            <div class="form-card">
                <h1 class="mb-1 text-primary-deep"><i class="bi bi-file-earmark-plus"></i> Новая заявка</h1>
                <p class="text-muted mb-4">Заполните форму — мы свяжемся с вами после согласования</p>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-1"></i><?= e($error) ?></div>
                <?php endif; ?>

                <form id="applyForm" method="post" novalidate>
                    <div class="mb-3">
                        <label class="form-label">Вид транспорта <span class="text-danger">*</span></label>
                        <select name="transport_type" class="form-select" required>
                            <option value="">— выберите —</option>
                            <option>Катер</option>
                            <option>Круизный лайнер</option>
                            <option>Яхта</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Дата начала <span class="text-danger">*</span></label>
                        <input type="date" name="start_date" class="form-control" required min="<?= date('Y-m-d') ?>">
                        <div class="field-hint">Дата отображается в формате ДД.ММ.ГГГГ</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Способ оплаты <span class="text-danger">*</span></label>
                        <select name="payment_method" class="form-select" required>
                            <option value="">— выберите —</option>
                            <option>Предоплата по QR-коду</option>
                            <option>Оплата картой МИР</option>
                            <option>Постоплата в офисе</option>
                        </select>
                    </div>

                    <button class="btn btn-primary-deep w-100"><i class="bi bi-send"></i> Отправить заявку</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
