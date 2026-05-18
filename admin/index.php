<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';
require_admin();

// ---------- Обработка смены статуса ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_status') {
    $id = (int)($_POST['id'] ?? 0);
    $st = $_POST['status'] ?? '';
    $allowed = ['Новая', 'Идет обучение', 'Обучение завершено'];

    if ($id <= 0) {
        flash_set('err', 'Некорректный идентификатор заявки.');
    } elseif (!in_array($st, $allowed, true)) {
        flash_set('err', 'Недопустимый статус заявки.');
    } else {
        $check = $pdo->prepare('SELECT status FROM applications WHERE id = ?');
        $check->execute([$id]);
        $prev = $check->fetchColumn();
        if ($prev === false) {
            flash_set('err', "Заявка #$id не найдена.");
        } elseif ($prev === $st) {
            flash_set('info', "У заявки #$id уже статус «$st».");
        } else {
            $pdo->prepare('UPDATE applications SET status = ? WHERE id = ?')->execute([$st, $id]);
            flash_set('ok', "Статус заявки #$id изменён: «$prev» → «$st».");
        }
    }

    $back = $_POST['redirect_to'] ?? url('admin/index.php');
    $expected = url('admin/');
    if (!str_starts_with($back, $expected)) $back = url('admin/index.php');
    header('Location: ' . $back);
    exit;
}

// ---------- Параметры запроса ----------
$fStatus    = trim($_GET['status']    ?? '');
$fTransport = trim($_GET['transport'] ?? '');
$fSearch    = trim($_GET['q']         ?? '');
$fDateFrom  = trim($_GET['date_from'] ?? '');
$fDateTo    = trim($_GET['date_to']   ?? '');

$sort = $_GET['sort'] ?? 'created_at';
$dir  = strtolower($_GET['dir'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';
$allowedSort = ['id', 'fio', 'transport_type', 'start_date', 'created_at', 'status'];
if (!in_array($sort, $allowedSort, true)) $sort = 'created_at';

$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;

// ---------- WHERE ----------
$where  = [];
$params = [];
if ($fStatus !== '')    { $where[] = 'a.status = ?';         $params[] = $fStatus; }
if ($fTransport !== '') { $where[] = 'a.transport_type = ?'; $params[] = $fTransport; }
if ($fSearch !== '') {
    $where[] = '(u.fio LIKE ? OR u.login LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)';
    $like = "%$fSearch%";
    array_push($params, $like, $like, $like, $like);
}
if ($fDateFrom && strtotime($fDateFrom)) { $where[] = 'a.start_date >= ?'; $params[] = $fDateFrom; }
if ($fDateTo   && strtotime($fDateTo))   { $where[] = 'a.start_date <= ?'; $params[] = $fDateTo; }
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// ---------- Подсчёт ----------
$cnt = $pdo->prepare("SELECT COUNT(*) FROM applications a JOIN users u ON u.id = a.user_id $whereSql");
$cnt->execute($params);
$total = (int)$cnt->fetchColumn();
$pages = max(1, (int)ceil($total / $perPage));
if ($page > $pages) $page = $pages;
$offset = ($page - 1) * $perPage;

// ---------- Выборка ----------
$orderCol = $sort === 'fio' ? 'u.fio' : "a.$sort";
$sql = "
    SELECT a.*, u.fio, u.login, u.email, u.phone, u.birthdate,
           r.id AS review_id, r.rating, r.text AS review_text, r.created_at AS review_created
    FROM applications a
    JOIN users u ON u.id = a.user_id
    LEFT JOIN reviews r ON r.application_id = a.id
    $whereSql
    ORDER BY $orderCol $dir
    LIMIT $perPage OFFSET $offset
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

// ---------- Статистика ----------
$stats = $pdo->query("SELECT status, COUNT(*) c FROM applications GROUP BY status")
    ->fetchAll(PDO::FETCH_KEY_PAIR);

// ---------- Сообщения ----------
$flashOk   = flash_get('ok');
$flashErr  = flash_get('err');
$flashInfo = flash_get('info');

// ---------- URL helpers ----------
function qs(array $over = []): string {
    $q = array_merge($_GET, $over);
    return '?' . http_build_query($q);
}
$currentUrl = url('admin/index.php') . (empty($_GET) ? '' : '?' . http_build_query($_GET));

$pageTitle = 'Панель администратора — Водить.РФ';
include __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid px-3 px-md-4">

    <!-- Тосты-уведомления -->
    <div class="position-fixed top-0 end-0 p-3" style="z-index:1080">
        <?php if ($flashOk): ?>
            <div class="toast align-items-center text-white bg-success border-0" role="alert">
                <div class="d-flex">
                    <div class="toast-body"><i class="bi bi-check-circle me-1"></i><?= e($flashOk) ?></div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        <?php endif; ?>
        <?php if ($flashErr): ?>
            <div class="toast align-items-center text-white bg-danger border-0" role="alert">
                <div class="d-flex">
                    <div class="toast-body"><i class="bi bi-exclamation-triangle me-1"></i><?= e($flashErr) ?></div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        <?php endif; ?>
        <?php if ($flashInfo): ?>
            <div class="toast align-items-center text-white bg-info border-0" role="alert">
                <div class="d-flex">
                    <div class="toast-body"><i class="bi bi-info-circle me-1"></i><?= e($flashInfo) ?></div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Шапка панели -->
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
        <div>
            <h1 class="text-primary-deep mb-0"><i class="bi bi-shield-check"></i> Панель администратора</h1>
            <div class="text-muted small">Управление заявками на обучение</div>
        </div>
        <div class="d-flex gap-2">
            <span class="badge bg-light text-dark py-2 px-3">
                <i class="bi bi-person-badge"></i> <?= e($_SESSION['admin_login'] ?? 'Admin26') ?>
            </span>
            <a href="<?= e(url('admin/logout.php')) ?>" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-box-arrow-right"></i> Выйти
            </a>
        </div>
    </div>

    <!-- Статистика -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm fade-in-up h-100">
                <div class="card-body">
                    <div class="text-muted small">Всего заявок</div>
                    <div class="h2 mb-0 text-primary-deep"><?= (int)array_sum($stats) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm fade-in-up h-100">
                <div class="card-body">
                    <div class="text-muted small">Новые</div>
                    <div class="h2 mb-0 text-primary-deep"><?= (int)($stats['Новая'] ?? 0) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm fade-in-up h-100">
                <div class="card-body">
                    <div class="text-muted small">Идет обучение</div>
                    <div class="h2 mb-0 text-warning"><?= (int)($stats['Идет обучение'] ?? 0) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm fade-in-up h-100">
                <div class="card-body">
                    <div class="text-muted small">Завершено</div>
                    <div class="h2 mb-0 text-success"><?= (int)($stats['Обучение завершено'] ?? 0) ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Фильтры -->
    <form class="card border-0 shadow-sm p-3 mb-3" method="get">
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small mb-1">Поиск (ФИО, логин, e-mail, телефон)</label>
                <input type="text" name="q" class="form-control form-control-sm" value="<?= e($fSearch) ?>" placeholder="Иванов / petr123">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Статус</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">— все —</option>
                    <?php foreach (['Новая', 'Идет обучение', 'Обучение завершено'] as $s): ?>
                        <option <?= $fStatus === $s ? 'selected' : '' ?>><?= e($s) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Транспорт</label>
                <select name="transport" class="form-select form-select-sm">
                    <option value="">— все —</option>
                    <?php foreach (['Катер', 'Круизный лайнер', 'Яхта'] as $t): ?>
                        <option <?= $fTransport === $t ? 'selected' : '' ?>><?= e($t) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Старт с</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="<?= e($fDateFrom) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">по</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="<?= e($fDateTo) ?>">
            </div>
            <div class="col-md-1 d-flex gap-1">
                <button class="btn btn-primary-deep btn-sm flex-grow-1" title="Применить"><i class="bi bi-funnel-fill"></i></button>
            </div>
        </div>
        <?php if ($fStatus || $fTransport || $fSearch || $fDateFrom || $fDateTo): ?>
            <div class="mt-2">
                <a class="btn btn-link btn-sm p-0 text-muted" href="<?= e(url('admin/index.php')) ?>">
                    <i class="bi bi-x-circle"></i> Сбросить все фильтры
                </a>
                <span class="text-muted small ms-2">Найдено: <?= (int)$total ?></span>
            </div>
        <?php endif; ?>
    </form>

    <!-- Таблица заявок -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table admin-table mb-0 align-middle">
                <thead>
                    <tr>
                        <?php
                        $cols = [
                            'id'             => '#',
                            'fio'            => 'Пользователь',
                            'transport_type' => 'Транспорт',
                            'start_date'     => 'Дата старта',
                            'created_at'     => 'Подана',
                            'status'         => 'Статус',
                        ];
                        foreach ($cols as $key => $label):
                            $isCur  = $sort === $key;
                            $newDir = ($isCur && $dir === 'ASC') ? 'desc' : 'asc';
                            $arrow  = !$isCur ? '↕' : ($dir === 'ASC' ? '↑' : '↓');
                        ?>
                            <th class="sortable">
                                <a href="<?= e(qs(['sort' => $key, 'dir' => $newDir, 'page' => 1])) ?>"
                                   class="text-white text-decoration-none <?= $isCur ? 'fw-bold' : '' ?>">
                                    <?= e($label) ?> <span class="sort-arrow"><?= $arrow ?></span>
                                </a>
                            </th>
                        <?php endforeach; ?>
                        <th>Управление</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$rows): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">
                            <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                            Заявок по выбранным фильтрам не найдено
                        </td></tr>
                    <?php else: foreach ($rows as $r):
                        $detailId = 'app' . (int)$r['id'];
                    ?>
                        <tr>
                            <td class="fw-medium">#<?= (int)$r['id'] ?></td>
                            <td>
                                <div class="fw-medium"><?= e($r['fio']) ?></div>
                                <div class="text-muted small">
                                    <i class="bi bi-at"></i><?= e($r['login']) ?>
                                    &nbsp;<i class="bi bi-envelope"></i> <?= e($r['email']) ?>
                                </div>
                            </td>
                            <td>
                                <i class="bi bi-water text-primary-deep"></i> <?= e($r['transport_type']) ?>
                            </td>
                            <td><?= e(date('d.m.Y', strtotime($r['start_date']))) ?></td>
                            <td><span class="text-muted small"><?= e(date('d.m.Y H:i', strtotime($r['created_at']))) ?></span></td>
                            <td>
                                <span class="badge <?= status_badge($r['status']) ?>"><?= e($r['status']) ?></span>
                                <?php if ($r['review_id']): ?>
                                    <span class="badge bg-warning text-dark ms-1" title="Есть отзыв">
                                        <i class="bi bi-star-fill"></i> <?= (int)$r['rating'] ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="d-flex gap-1 flex-wrap">
                                    <button type="button"
                                            class="btn btn-outline-primary btn-sm"
                                            data-bs-toggle="modal"
                                            data-bs-target="#detail-<?= $detailId ?>"
                                            title="Подробнее">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button type="button"
                                            class="btn btn-primary-deep btn-sm"
                                            data-bs-toggle="modal"
                                            data-bs-target="#status-<?= $detailId ?>"
                                            title="Сменить статус">
                                        <i class="bi bi-pencil-square"></i> Статус
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
        <?php if ($rows): ?>
            <div class="card-footer bg-white border-0 small text-muted">
                Показаны <?= $offset + 1 ?>–<?= min($offset + $perPage, $total) ?> из <?= (int)$total ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Модалки (вынесены за пределы таблицы: внутри <tbody> браузер
         "фостер-парентит" <div>/<form>, ломая submit формы смены статуса) -->
    <?php foreach ($rows as $r): $detailId = 'app' . (int)$r['id']; ?>
        <!-- Модалка деталей -->
        <div class="modal fade" id="detail-<?= $detailId ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-primary-deep text-white">
                        <h5 class="modal-title"><i class="bi bi-file-earmark-text"></i> Заявка #<?= (int)$r['id'] ?></h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <h6 class="text-muted">Пользователь</h6>
                        <ul class="list-unstyled small mb-3">
                            <li><strong>ФИО:</strong> <?= e($r['fio']) ?></li>
                            <li><strong>Логин:</strong> <?= e($r['login']) ?></li>
                            <li><strong>Дата рождения:</strong> <?= e(date('d.m.Y', strtotime($r['birthdate']))) ?></li>
                            <li><strong>E-mail:</strong> <?= e($r['email']) ?></li>
                            <li><strong>Телефон:</strong> <?= e($r['phone']) ?></li>
                        </ul>
                        <h6 class="text-muted">Заявка</h6>
                        <ul class="list-unstyled small mb-3">
                            <li><strong>Транспорт:</strong> <?= e($r['transport_type']) ?></li>
                            <li><strong>Дата начала:</strong> <?= e(date('d.m.Y', strtotime($r['start_date']))) ?></li>
                            <li><strong>Оплата:</strong> <?= e($r['payment_method']) ?></li>
                            <li><strong>Подана:</strong> <?= e(date('d.m.Y H:i', strtotime($r['created_at']))) ?></li>
                            <li><strong>Статус:</strong> <span class="badge <?= status_badge($r['status']) ?>"><?= e($r['status']) ?></span></li>
                        </ul>
                        <?php if ($r['review_id']): ?>
                            <h6 class="text-muted">Отзыв клиента</h6>
                            <div class="rating-stars mb-1">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="bi bi-star<?= $i <= (int)$r['rating'] ? '-fill' : '' ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <p class="small mb-1"><?= nl2br(e($r['review_text'])) ?></p>
                            <small class="text-muted">Оставлен <?= e(date('d.m.Y H:i', strtotime($r['review_created']))) ?></small>
                        <?php endif; ?>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Модалка смены статуса -->
        <div class="modal fade" id="status-<?= $detailId ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form method="post" action="<?= e(url('admin/index.php')) ?>" class="modal-content">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                    <input type="hidden" name="redirect_to" value="<?= e($currentUrl) ?>">
                    <div class="modal-header bg-primary-deep text-white">
                        <h5 class="modal-title"><i class="bi bi-pencil-square"></i> Смена статуса #<?= (int)$r['id'] ?></h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="small text-muted mb-3">
                            Заявка <strong><?= e($r['fio']) ?></strong> · <?= e($r['transport_type']) ?> ·
                            <?= e(date('d.m.Y', strtotime($r['start_date']))) ?>
                        </p>
                        <p class="mb-2">Текущий статус: <span class="badge <?= status_badge($r['status']) ?>"><?= e($r['status']) ?></span></p>
                        <label class="form-label">Новый статус</label>
                        <select name="status" class="form-select" required>
                            <?php foreach (['Новая', 'Идет обучение', 'Обучение завершено'] as $s): ?>
                                <option value="<?= e($s) ?>" <?= $r['status'] === $s ? 'selected' : '' ?>><?= e($s) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-primary-deep"><i class="bi bi-check2"></i> Сохранить</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endforeach; ?>

    <!-- Пагинация -->
    <?php if ($pages > 1): ?>
        <nav class="mt-3" aria-label="Постраничная навигация">
            <ul class="pagination justify-content-center">
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= e(qs(['page' => max(1, $page - 1)])) ?>" aria-label="Назад">&laquo;</a>
                </li>
                <?php
                $start = max(1, $page - 3);
                $end   = min($pages, $page + 3);
                if ($start > 1): ?>
                    <li class="page-item"><a class="page-link" href="<?= e(qs(['page' => 1])) ?>">1</a></li>
                    <?php if ($start > 2): ?><li class="page-item disabled"><span class="page-link">…</span></li><?php endif; ?>
                <?php endif; ?>
                <?php for ($i = $start; $i <= $end; $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="<?= e(qs(['page' => $i])) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <?php if ($end < $pages): ?>
                    <?php if ($end < $pages - 1): ?><li class="page-item disabled"><span class="page-link">…</span></li><?php endif; ?>
                    <li class="page-item"><a class="page-link" href="<?= e(qs(['page' => $pages])) ?>"><?= $pages ?></a></li>
                <?php endif; ?>
                <li class="page-item <?= $page >= $pages ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= e(qs(['page' => min($pages, $page + 1)])) ?>" aria-label="Вперед">&raquo;</a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
