<?php
/**
 * 友链申请审核
 */
require_once __DIR__ . '/includes/auth.php';
require_login();
require_once __DIR__ . '/includes/header.php';

$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    if ($action === 'approve' && $id) {
        // 取申请数据
        $stmt = $pdo->prepare('SELECT * FROM friend_requests WHERE id=?');
        $stmt->execute([$id]);
        $r = $stmt->fetch();
        if ($r) {
            $stmt = $pdo->prepare('INSERT INTO links (name, url, icon, cover, description, category, sort) VALUES (?,?,?,?,?,?,?)');
            $stmt->execute([$r['name'], $r['url'], $r['icon'], '', $r['description'], '推荐网站', 99]);
            $pdo->prepare('UPDATE friend_requests SET status=? WHERE id=?')->execute(['approved', $id]);
            flash_set('ok', "已通过「{$r['name']}」并加入友链");
        }
    } elseif ($action === 'reject' && $id) {
        $pdo->prepare('UPDATE friend_requests SET status=? WHERE id=?')->execute(['rejected', $id]);
        flash_set('ok', '已拒绝该申请');
    } elseif ($action === 'delete' && $id) {
        $pdo->prepare('DELETE FROM friend_requests WHERE id=?')->execute([$id]);
        flash_set('ok', '已删除记录');
    }
    redirect('/admin/requests.php');
}

$pending = $pdo->query("SELECT * FROM friend_requests WHERE status='pending' ORDER BY id DESC")->fetchAll();
$done    = $pdo->query("SELECT * FROM friend_requests WHERE status!='pending' ORDER BY id DESC LIMIT 50")->fetchAll();

admin_header('requests');
flash_show();
?>
<h1 class="page-title">友链申请</h1>
<p class="page-sub">前台「申请友链」提交的申请,通过后自动加入友链(默认分类:推荐网站)</p>

<div class="card">
    <h2 class="card-title">待审核 (<?= count($pending) ?>)</h2>
    <?php if (!$pending): ?>
    <p class="muted">暂无待审核的申请</p>
    <?php else: ?>
    <table class="table">
        <thead><tr><th>站点</th><th>链接</th><th>提交时间</th><th>操作</th></tr></thead>
        <tbody>
        <?php foreach ($pending as $r): ?>
            <tr>
                <td data-label="站点">
                    <?php if ($r['icon']): ?><img class="mini-icon" src="<?= e($r['icon']) ?>" alt="" onerror="this.style.display='none'"><?php endif; ?>
                    <?= e($r['name']) ?>
                    <?php if ($r['description']): ?><div class="muted small"><?= e($r['description']) ?></div><?php endif; ?>
                </td>
                <td data-label="链接" class="url-cell"><a href="<?= e($r['url']) ?>" target="_blank" rel="noopener"><?= e($r['url']) ?></a></td>
                <td data-label="提交时间"><?= e($r['created_at']) ?></td>
                <td data-label="操作" class="ops">
                    <form method="post" class="inline">
                        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                        <input type="hidden" name="action" value="approve">
                        <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                        <button class="btn btn-sm btn-primary" type="submit">通过</button>
                    </form>
                    <form method="post" class="inline">
                        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                        <input type="hidden" name="action" value="reject">
                        <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                        <button class="btn btn-sm" type="submit">拒绝</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<div class="card">
    <h2 class="card-title">已处理 (<?= count($done) ?>)</h2>
    <?php if (!$done): ?>
    <p class="muted">暂无记录</p>
    <?php else: ?>
    <table class="table">
        <thead><tr><th>站点</th><th>状态</th><th>时间</th><th>操作</th></tr></thead>
        <tbody>
        <?php foreach ($done as $r): ?>
            <tr>
                <td data-label="站点"><?= e($r['name']) ?></td>
                <td data-label="状态"><span class="tag"><?= $r['status'] === 'approved' ? '已通过' : '已拒绝' ?></span></td>
                <td data-label="时间"><?= e($r['created_at']) ?></td>
                <td data-label="操作" class="ops">
                    <form method="post" class="inline" onsubmit="return confirm('确定删除这条记录?')">
                        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
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
