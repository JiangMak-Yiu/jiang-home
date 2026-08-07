<?php
/**
 * 后台公共布局
 */

function admin_header(string $active = ''): void
{
    $site = setting('site_title', '江江的小站');
    $menu = [
        'dashboard' => ['仪表盘', '/admin/dashboard.php'],
        'nav'       => ['导航管理', '/admin/nav.php'],
        'links'     => ['友链管理', '/admin/links.php'],
        'requests'  => ['友链申请', '/admin/requests.php'],
        'settings'  => ['站点设置', '/admin/settings.php'],
        'backup'    => ['数据备份', '/admin/backup.php'],
        'password'  => ['修改密码', '/admin/password.php'],
    ];
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($active ? $menu[$active][0] . ' - ' : '') ?>江江.com 后台</title>
<link rel="stylesheet" href="/assets/admin.css">
</head>
<body>
<div class="admin-wrap">
    <aside class="sidebar">
        <div class="brand">
            <span class="brand-dot"></span>
            <?= e($site) ?> <small>后台</small>
        </div>
        <nav class="nav">
            <?php foreach ($menu as $key => $item): ?>
            <a class="nav-item <?= $active === $key ? 'active' : '' ?>" href="<?= $item[1] ?>"><?= $item[0] ?></a>
            <?php endforeach; ?>
        </nav>
        <div class="sidebar-foot">
            <a class="nav-item" href="/" target="_blank">查看前台 ↗</a>
            <a class="nav-item" href="/admin/logout.php">退出登录</a>
        </div>
    </aside>
    <main class="content">
<?php
}

function admin_footer(): void
{
    echo "</main></div></body></html>\n";
}

/** 闪现消息(一次性提示) */
function flash_set(string $type, string $msg): void
{
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function flash_show(): void
{
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        $cls = $f['type'] === 'error' ? 'flash-error' : 'flash-ok';
        echo '<div class="flash ' . $cls . '">' . e($f['msg']) . '</div>';
        unset($_SESSION['flash']);
    }
}
