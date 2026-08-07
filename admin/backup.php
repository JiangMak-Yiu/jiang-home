<?php
/**
 * 数据备份与恢复
 */
require_once __DIR__ . '/includes/auth.php';
require_login();
require_once __DIR__ . '/includes/header.php';

$backupDir = dirname(DB_PATH) . '/backup';
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0750, true);
}

// 立即备份
if (isset($_GET['do']) && $_GET['do'] === 'backup') {
    $name = 'site-backup-' . date('Ymd-His') . '.db';
    try {
        // VACUUM INTO 生成一致性备份(SQLite 3.27+)
        db()->exec("VACUUM INTO " . $pdo_quote = "'" . str_replace("'", "''", $backupDir . '/' . $name) . "'");
        flash_set('ok', "备份成功: {$name}");
    } catch (Exception $e) {
        // 备用方案:直接复制文件
        $src = DB_PATH;
        if (@copy($src, $backupDir . '/' . $name)) {
            flash_set('ok', "备份成功(文件复制): {$name}");
        } else {
            flash_set('error', '备份失败: ' . $e->getMessage());
        }
    }
    redirect('/admin/backup.php');
}

// 下载
if (isset($_GET['download'])) {
    $file = basename($_GET['download']);
    $path = $backupDir . '/' . $file;
    if (preg_match('/^site-backup-[\d-]+\.db$/', $file) && file_exists($path)) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $file . '"');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }
    redirect('/admin/backup.php');
}

// 删除
if (isset($_GET['delete'])) {
    $file = basename($_GET['delete']);
    $path = $backupDir . '/' . $file;
    if (preg_match('/^site-backup-[\d-]+\.db$/', $file) && file_exists($path)) {
        unlink($path);
        flash_set('ok', "已删除备份: {$file}");
    }
    redirect('/admin/backup.php');
}

$backups = [];
foreach (glob($backupDir . '/site-backup-*.db') ?: [] as $f) {
    $backups[] = [
        'name' => basename($f),
        'size' => round(filesize($f) / 1024, 1),
        'time' => date('Y-m-d H:i:s', filemtime($f)),
    ];
}
// 新的在前
$backups = array_reverse($backups);

admin_header('backup');
flash_show();
?>
<h1 class="page-title">数据备份</h1>
<p class="page-sub">备份包含全部站点数据(导航链接、友链、申请记录、设置)</p>

<div class="card">
    <h2 class="card-title">立即备份</h2>
    <p class="muted" style="margin-bottom:14px">点击按钮生成当前数据的完整备份文件,保存在 <code>data/backup/</code> 目录</p>
    <a class="btn btn-primary" href="/admin/backup.php?do=backup">🛡️ 立即备份</a>
</div>

<div class="card">
    <h2 class="card-title">备份文件 (<?= count($backups) ?>)</h2>
    <?php if (!$backups): ?>
    <p class="muted">还没有备份,点上面的按钮生成一个吧</p>
    <?php else: ?>
    <table class="table">
        <thead><tr><th>文件名</th><th>大小</th><th>时间</th><th>操作</th></tr></thead>
        <tbody>
        <?php foreach ($backups as $b): ?>
            <tr>
                <td data-label="文件名"><?= e($b['name']) ?></td>
                <td data-label="大小"><?= $b['size'] ?> KB</td>
                <td data-label="时间"><?= e($b['time']) ?></td>
                <td data-label="操作" class="ops">
                    <a class="btn btn-sm" href="/admin/backup.php?download=<?= urlencode($b['name']) ?>">下载</a>
                    <a class="btn btn-sm btn-danger" href="/admin/backup.php?delete=<?= urlencode($b['name']) ?>" onclick="return confirm('确定删除 <?= e($b['name']) ?>?')">删除</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<div class="card">
    <h2 class="card-title">恢复方法</h2>
    <ol class="muted" style="padding-left:20px;line-height:2">
        <li>下载需要恢复的备份文件</li>
        <li>停止网站或直接替换:用备份文件覆盖 <code>data/site.db</code>(建议先改名保留当前库)</li>
        <li>刷新页面即可,无需改任何配置</li>
    </ol>
</div>
<?php admin_footer(); ?>
