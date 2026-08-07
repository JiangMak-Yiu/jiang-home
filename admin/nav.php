<?php
/**
 * 导航管理:常用入口(featured)+ 站点索引(group)
 */
require_once __DIR__ . '/includes/auth.php';
require_login();
require_once __DIR__ . '/includes/header.php';

$pdo = db();

// ---- 增 / 改 / 删 ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';
    $type = ($_POST['type'] ?? 'group') === 'featured' ? 'featured' : 'group';
    $groupName = trim($_POST['group_name'] ?? '');
    if ($type === 'featured') $groupName = '';

    if ($action === 'add') {
        $stmt = $pdo->prepare('INSERT INTO nav_links (name, url, icon, description, type, group_name, sort) VALUES (?,?,?,?,?,?,?)');
        $stmt->execute([
            trim($_POST['name'] ?? ''),
            trim($_POST['url'] ?? ''),
            trim($_POST['icon'] ?? ''),
            trim($_POST['description'] ?? ''),
            $type, $groupName, (int)($_POST['sort'] ?? 0),
        ]);
        flash_set('ok', '已添加');
    } elseif ($action === 'update' && isset($_POST['id'])) {
        $stmt = $pdo->prepare('UPDATE nav_links SET name=?, url=?, icon=?, description=?, type=?, group_name=?, sort=? WHERE id=?');
        $stmt->execute([
            trim($_POST['name'] ?? ''),
            trim($_POST['url'] ?? ''),
            trim($_POST['icon'] ?? ''),
            trim($_POST['description'] ?? ''),
            $type, $groupName, (int)($_POST['sort'] ?? 0), (int)$_POST['id'],
        ]);
        flash_set('ok', '已更新');
    } elseif ($action === 'delete' && isset($_POST['id'])) {
        $stmt = $pdo->prepare('DELETE FROM nav_links WHERE id=?');
        $stmt->execute([(int)$_POST['id']]);
        flash_set('ok', '已删除');
    }
    redirect('/admin/nav.php');
}

$featured = $pdo->query("SELECT * FROM nav_links WHERE type='featured' ORDER BY sort ASC, id ASC")->fetchAll();
$groups   = $pdo->query("SELECT * FROM nav_links WHERE type='group' ORDER BY group_name ASC, sort ASC, id ASC")->fetchAll();
$grouped  = [];
foreach ($groups as $g) {
    $grouped[$g['group_name']][] = $g;
}

$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM nav_links WHERE id=?');
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch() ?: null;
}

admin_header('nav');
flash_show();
?>
<h1 class="page-title">导航管理</h1>
<p class="page-sub">管理首页「常用入口」和「站点索引」的链接,图标为 FontAwesome 类名(如 fa-hdd)</p>

<div class="card form">
    <h2 class="card-title"><?= $edit ? '编辑链接' : '添加链接' ?></h2>
    <form method="post">
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
        <input type="hidden" name="action" value="<?= $edit ? 'update' : 'add' ?>">
        <?php if ($edit): ?><input type="hidden" name="id" value="<?= (int)$edit['id'] ?>"><?php endif; ?>

        <div class="form-grid">
            <label class="field">
                <span>名称 *</span>
                <input type="text" name="name" value="<?= e($edit['name'] ?? '') ?>" required maxlength="60">
            </label>
            <label class="field">
                <span>链接 URL *</span>
                <input type="url" name="url" value="<?= e($edit['url'] ?? '') ?>" required maxlength="300">
            </label>
            <label class="field">
                <span>图标(FontAwesome 类名)</span>
                <input type="text" name="icon" value="<?= e($edit['icon'] ?? '') ?>" maxlength="60" placeholder="如 fa-hdd、fab fa-telegram">
            </label>
            <label class="field">
                <span>简介</span>
                <input type="text" name="description" value="<?= e($edit['description'] ?? '') ?>" maxlength="120">
            </label>
            <label class="field">
                <span>类型</span>
                <select name="type" id="nav-type">
                    <option value="group" <?= ($edit['type'] ?? 'group') === 'group' ? 'selected' : '' ?>>站点索引(分组)</option>
                    <option value="featured" <?= ($edit['type'] ?? '') === 'featured' ? 'selected' : '' ?>>常用入口</option>
                </select>
            </label>
            <label class="field" id="group-field">
                <span>分组名(站点索引时)</span>
                <input type="text" name="group_name" value="<?= e($edit['group_name'] ?? '') ?>" maxlength="30" list="group-list" placeholder="输入新分组或选已有">
                <datalist id="group-list">
                    <?php foreach (array_keys($grouped) as $gn): ?>
                    <option value="<?= e($gn) ?>">
                    <?php endforeach; ?>
                </datalist>
            </label>
            <label class="field">
                <span>排序(数字越小越靠前)</span>
                <input type="number" name="sort" value="<?= e($edit['sort'] ?? '0') ?>" min="0" max="9999">
            </label>
        </div>
        <button class="btn btn-primary" type="submit"><?= $edit ? '保存修改' : '添加' ?></button>
        <?php if ($edit): ?><a class="btn" href="/admin/nav.php">取消</a><?php endif; ?>
    </form>
</div>

<div class="card">
    <h2 class="card-title">常用入口 (<?= count($featured) ?>)</h2>
    <table class="table">
        <thead><tr><th>排序</th><th>名称</th><th>链接</th><th>操作</th></tr></thead>
        <tbody>
        <?php foreach ($featured as $l): ?>
            <tr>
                <td data-label="排序"><?= (int)$l['sort'] ?></td>
                <td data-label="名称"><i class="fas <?= e($l['icon']) ?>"></i> <?= e($l['name']) ?></td>
                <td data-label="链接" class="url-cell"><a href="<?= e($l['url']) ?>" target="_blank" rel="noopener"><?= e($l['url']) ?></a></td>
                <td data-label="操作" class="ops">
                    <a class="btn btn-sm" href="/admin/nav.php?edit=<?= (int)$l['id'] ?>">编辑</a>
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
</div>

<div class="card">
    <h2 class="card-title">站点索引 (<?= count($groups) ?>)</h2>
    <?php foreach ($grouped as $gn => $items): ?>
    <h3 class="group-title"><?= e($gn) ?> (<?= count($items) ?>)</h3>
    <table class="table">
        <tbody>
        <?php foreach ($items as $l): ?>
            <tr>
                <td data-label="排序"><?= (int)$l['sort'] ?></td>
                <td data-label="名称"><i class="fas <?= e($l['icon']) ?>"></i> <?= e($l['name']) ?></td>
                <td data-label="链接" class="url-cell"><a href="<?= e($l['url']) ?>" target="_blank" rel="noopener"><?= e($l['url']) ?></a></td>
                <td data-label="操作" class="ops">
                    <a class="btn btn-sm" href="/admin/nav.php?edit=<?= (int)$l['id'] ?>">编辑</a>
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
    <?php endforeach; ?>
</div>

<script>
// 类型切换时显示/隐藏分组名输入
document.addEventListener('DOMContentLoaded', function () {
    var type = document.getElementById('nav-type');
    var gf = document.getElementById('group-field');
    function sync() { gf.style.display = type.value === 'featured' ? 'none' : ''; }
    type.addEventListener('change', sync);
    sync();
});
</script>
<?php admin_footer(); ?>
