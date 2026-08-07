<?php
/**
 * 友链管理:增删改
 */
require_once __DIR__ . '/includes/auth.php';
require_login();
require_once __DIR__ . '/includes/header.php';

$pdo = db();

// ---- 增 / 改 / 删 ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $stmt = $pdo->prepare('INSERT INTO links (name, url, icon, cover, description, category, sort) VALUES (?,?,?,?,?,?,?)');
        $stmt->execute([
            trim($_POST['name'] ?? ''),
            trim($_POST['url'] ?? ''),
            trim($_POST['icon'] ?? ''),
            trim($_POST['cover'] ?? ''),
            trim($_POST['description'] ?? ''),
            trim($_POST['category'] ?: '默认'),
            (int)($_POST['sort'] ?? 0),
        ]);
        flash_set('ok', '友链已添加');
    } elseif ($action === 'update' && isset($_POST['id'])) {
        $stmt = $pdo->prepare('UPDATE links SET name=?, url=?, icon=?, cover=?, description=?, category=?, sort=? WHERE id=?');
        $stmt->execute([
            trim($_POST['name'] ?? ''),
            trim($_POST['url'] ?? ''),
            trim($_POST['icon'] ?? ''),
            trim($_POST['cover'] ?? ''),
            trim($_POST['description'] ?? ''),
            trim($_POST['category'] ?: '默认'),
            (int)($_POST['sort'] ?? 0),
            (int)$_POST['id'],
        ]);
        flash_set('ok', '友链已更新');
    } elseif ($action === 'delete' && isset($_POST['id'])) {
        $stmt = $pdo->prepare('DELETE FROM links WHERE id=?');
        $stmt->execute([(int)$_POST['id']]);
        flash_set('ok', '友链已删除');
    }
    redirect('/admin/links.php');
}

$links   = $pdo->query('SELECT * FROM links ORDER BY sort ASC, id ASC')->fetchAll();
$edit    = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM links WHERE id=?');
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch() ?: null;
}

admin_header('links');
flash_show();
?>
<h1 class="page-title">友链管理</h1>

<?php if ($edit): ?>
<div class="card form">
    <h2 class="card-title">编辑友链</h2>
    <form method="post">
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="id" value="<?= (int)$edit['id'] ?>">
        <?php require __DIR__ . '/includes/link_form.php'; ?>
        <button class="btn btn-primary" type="submit">保存修改</button>
        <a class="btn" href="/admin/links.php">取消</a>
    </form>
</div>
<?php else: ?>
<div class="card form">
    <h2 class="card-title">添加友链</h2>
    <form method="post">
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
        <input type="hidden" name="action" value="add">
        <?php require __DIR__ . '/includes/link_form.php'; ?>
        <button class="btn btn-primary" type="submit">添加</button>
    </form>
</div>
<?php endif; ?>

<div class="card">
    <h2 class="card-title">友链列表 (<?= count($links) ?>)</h2>
    <?php if (!$links): ?>
    <p class="muted">暂无友链,先添加一个吧</p>
    <?php else: ?>
    <table class="table">
        <thead>
            <tr><th>排序</th><th>名称</th><th>分类</th><th>链接</th><th>操作</th></tr>
        </thead>
        <tbody>
        <?php foreach ($links as $l): ?>
            <tr>
                <td data-label="排序"><?= (int)$l['sort'] ?></td>
                <td data-label="名称">
                    <?php if ($l['icon']): ?><img class="mini-icon" src="<?= e($l['icon']) ?>" alt="" onerror="this.style.display='none'"><?php endif; ?>
                    <?= e($l['name']) ?>
                    <?php if ($l['description']): ?><div class="muted small"><?= e($l['description']) ?></div><?php endif; ?>
                </td>
                <td data-label="分类"><span class="tag"><?= e($l['category']) ?></span></td>
                <td data-label="链接" class="url-cell"><a href="<?= e($l['url']) ?>" target="_blank" rel="noopener"><?= e($l['url']) ?></a></td>
                <td data-label="操作" class="ops">
                    <a class="btn btn-sm" href="/admin/links.php?edit=<?= (int)$l['id'] ?>">编辑</a>
                    <form method="post" class="inline" onsubmit="return confirm('确定删除「<?= e($l['name']) ?>」?')">
                        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= (int)$l['id'] ?>">
                        <button class="btn btn-sm btn-danger" type="submit">删除</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>
<?php admin_footer(); ?>
