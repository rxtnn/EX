<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';
require_admin();

// Обработка смены статуса
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_status') {
    $id = (int)($_POST['id'] ?? 0);
    $st = $_POST['status'] ?? '';
    $allowed = ['Новая','Идет обучение','Обучение завершено'];
    if ($id && in_array($st, $allowed, true)) {
        $pdo->prepare('UPDATE applications SET status = ? WHERE id = ?')->execute([$st, $id]);
        flash_set('ok', "Статус заявки #$id изменен на «$st».");
    }
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/exam/admin/index.php'));
    exit;
}

// Фильтры
$fStatus = $_GET['status'] ?? '';
$fTransport = $_GET['transport'] ?? '';
$fSearch = trim($_GET['q'] ?? '');
$sort = $_GET['sort'] ?? 'created_at';
$dir = strtolower($_GET['dir'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';
$allowedSort = ['id','transport_type','start_date','status','created_at','fio'];
if (!in_array($sort, $allowedSort, true)) $sort = 'created_at';

// Пагинация
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;

$where = [];
$params = [];
if ($fStatus) { $where[] = 'a.status = ?'; $params[] = $fStatus; }
if ($fTransport) { $where[] = 'a.transport_type = ?'; $params[] = $fTransport; }
if ($fSearch !== '') {
    $where[] = '(u.fio LIKE ? OR u.login LIKE ? OR u.email LIKE ?)';
    $params[] = "%$fSearch%"; $params[] = "%$fSearch%"; $params[] = "%$fSearch%";
}
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$total = $pdo->prepare("SELECT COUNT(*) FROM applications a JOIN users u ON u.id = a.user_id $whereSql");
$total->execute($params);
$total = (int)$total->fetchColumn();
$pages = max(1, (int)ceil($total / $perPage));
if ($page > $pages) $page = $pages;
$offset = ($page - 1) * $perPage;

$orderCol = $sort === 'fio' ? 'u.fio' : "a.$sort";
$sql = "
    SELECT a.*, u.fio, u.login, u.email, u.phone
    FROM applications a
    JOIN users u ON u.id = a.user_id
    $whereSql
    ORDER BY $orderCol $dir
    LIMIT $perPage OFFSET $offset
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

// Статистика
$stats = $pdo->query("
    SELECT status, COUNT(*) c FROM applications GROUP BY status
")->fetchAll(PDO::FETCH_KEY_PAIR);

$flashOk = flash_get('ok');

function qs(array $over = []): string {
    $q = array_merge($_GET, $over);
    return '?' . http_build_query($q);
}

$pageTitle = 'Панель администратора';
include __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid px-3 px-md-4">
    <?php if ($flashOk): ?>
        <div class="position-fixed top-0 end-0 p-3" style="z-index:1080">
            <div class="toast align-items-center text-white bg-success border-0">
                <div class="d-flex">
                    <div class="toast-body"><i class="bi bi-check-circle me-1"></i><?= e($flashOk) ?></div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
        <h1 class="text-primary-deep mb-0"><i class="bi bi-shield-check"></i> Панель администратора</h1>
        <a href="/exam/admin/logout.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-box-arrow-right"></i> Выйти</a>
    </div>

    <!-- Статистика -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm fade-in-up">
                <div class="card-body">
                    <div class="text-muted small">Всего заявок</div>
                    <div class="h2 mb-0 text-primary-deep"><?= (int)array_sum($stats) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm fade-in-up">
                <div class="card-body">
                    <div class="text-muted small">Новые</div>
                    <div class="h2 mb-0 text-primary-deep"><?= (int)($stats['Новая'] ?? 0) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm fade-in-up">
                <div class="card-body">
                    <div class="text-muted small">Идет обучение</div>
                    <div class="h2 mb-0 text-warning"><?= (int)($stats['Идет обучение'] ?? 0) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm fade-in-up">
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
                <label class="form-label small">Поиск (ФИО/логин/e-mail)</label>
                <input type="text" name="q" class="form-control form-control-sm" value="<?= e($fSearch) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small">Статус</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">— все —</option>
                    <?php foreach (['Новая','Идет обучение','Обучение завершено'] as $s): ?>
                        <option <?= $fStatus === $s ? 'selected' : '' ?>><?= e($s) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small">Транспорт</label>
                <select name="transport" class="form-select form-select-sm">
                    <option value="">— все —</option>
                    <?php foreach (['Катер','Круизный лайнер','Яхта'] as $t): ?>
                        <option <?= $fTransport === $t ? 'selected' : '' ?>><?= e($t) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-primary-deep btn-sm flex-grow-1"><i class="bi bi-funnel"></i> Применить</button>
                <a class="btn btn-outline-secondary btn-sm" href="/exam/admin/index.php">Сброс</a>
            </div>
        </div>
    </form>

    <!-- Таблица -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table admin-table mb-0 align-middle">
                <thead>
                    <tr>
                        <?php
                        $cols = [
                            'id' => '#',
                            'fio' => 'Пользователь',
                            'transport_type' => 'Транспорт',
                            'start_date' => 'Дата старта',
                            'created_at' => 'Подана',
                            'status' => 'Статус',
                        ];
                        foreach ($cols as $key => $label):
                            $isCur = $sort === $key;
                            $newDir = ($isCur && $dir === 'ASC') ? 'desc' : 'asc';
                            $arrow = !$isCur ? '↕' : ($dir === 'ASC' ? '↑' : '↓');
                        ?>
                            <th class="sortable">
                                <a href="<?= e(qs(['sort'=>$key,'dir'=>$newDir])) ?>" class="text-white text-decoration-none">
                                    <?= e($label) ?> <span class="sort-arrow"><?= $arrow ?></span>
                                </a>
                            </th>
                        <?php endforeach; ?>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$rows): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">Заявок не найдено</td></tr>
                    <?php else: foreach ($rows as $r): ?>
                        <tr>
                            <td>#<?= (int)$r['id'] ?></td>
                            <td>
                                <div class="fw-medium"><?= e($r['fio']) ?></div>
                                <div class="text-muted small"><?= e($r['login']) ?> · <?= e($r['email']) ?></div>
                            </td>
                            <td><?= e($r['transport_type']) ?></td>
                            <td><?= e(date('d.m.Y', strtotime($r['start_date']))) ?></td>
                            <td><?= e(date('d.m.Y H:i', strtotime($r['created_at']))) ?></td>
                            <td><span class="badge <?= status_badge($r['status']) ?>"><?= e($r['status']) ?></span></td>
                            <td>
                                <form method="post" class="d-flex gap-1">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                                    <select name="status" class="form-select form-select-sm">
                                        <?php foreach (['Новая','Идет обучение','Обучение завершено'] as $s): ?>
                                            <option <?= $r['status']===$s?'selected':'' ?>><?= e($s) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button class="btn btn-primary-deep btn-sm"><i class="bi bi-check2"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Пагинация -->
    <?php if ($pages > 1): ?>
        <nav class="mt-3">
            <ul class="pagination justify-content-center">
                <li class="page-item <?= $page<=1?'disabled':'' ?>"><a class="page-link" href="<?= e(qs(['page'=>max(1,$page-1)])) ?>">&laquo;</a></li>
                <?php for ($i=1; $i<=$pages; $i++): ?>
                    <li class="page-item <?= $i===$page?'active':'' ?>"><a class="page-link" href="<?= e(qs(['page'=>$i])) ?>"><?= $i ?></a></li>
                <?php endfor; ?>
                <li class="page-item <?= $page>=$pages?'disabled':'' ?>"><a class="page-link" href="<?= e(qs(['page'=>min($pages,$page+1)])) ?>">&raquo;</a></li>
            </ul>
        </nav>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
