<?php
/**
 * 仪表盘
 */
require_once __DIR__ . '/includes/auth.php';
require_login();
require_once __DIR__ . '/includes/header.php';

$linkCount = (int)db()->query('SELECT COUNT(*) FROM links')->fetchColumn();
$catCount  = (int)db()->query('SELECT COUNT(DISTINCT category) FROM links')->fetchColumn();
$notice    = setting('site_notice', '');
$reqCount  = (int)db()->query("SELECT COUNT(*) FROM friend_requests WHERE status='pending'")->fetchColumn();

admin_header('dashboard');
flash_show();
?>
<h1 class="page-title">仪表盘</h1>
<p class="page-sub">欢迎回来,<?= e($_SESSION['username']) ?> 👋</p>

<div class="stats">
    <div class="stat-card">
        <div class="stat-num"><?= $linkCount ?></div>
        <div class="stat-label">友链总数</div>
    </div>
    <div class="stat-card">
        <div class="stat-num"><?= $catCount ?></div>
        <div class="stat-label">分类数</div>
    </div>
    <div class="stat-card">
        <div class="stat-num"><?= $notice ? '已开启' : '未设置' ?></div>
        <div class="stat-label">公告状态</div>
    </div>
    <div class="stat-card">
        <div class="stat-num"><?= $reqCount ?></div>
        <div class="stat-label">待审核申请</div>
    </div>
</div>

<div class="quick">
    <h2>快捷操作</h2>
    <div class="quick-actions">
        <a class="btn" href="/admin/links.php">管理友链</a>
        <a class="btn" href="/admin/settings.php">站点设置</a>
        <a class="btn" href="/" target="_blank">查看前台</a>
    </div>
</div>
<?php admin_footer(); ?>
