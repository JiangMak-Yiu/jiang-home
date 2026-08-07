<?php
/**
 * 后台登录页
 */
session_start();
require_once __DIR__ . '/includes/auth.php';

// 已登录则直接进仪表盘
if (!empty($_SESSION['uid'])) {
    redirect('/admin/dashboard.php');
}

$error = '';
if (isset($_GET['timeout'])) {
    $error = '登录已超时,请重新登录';
}
$ip = $_SERVER['REMOTE_ADDR'] ?? '';
$maxFails = 5;      // 连续失败 5 次
$lockMinutes = 15;  // 锁定 15 分钟

// 检查是否被锁定
if ($ip) {
    $stmt = db()->prepare('SELECT fails, locked_until FROM login_attempts WHERE ip=?');
    $stmt->execute([$ip]);
    $attempt = $stmt->fetch();
    if ($attempt && $attempt['locked_until'] !== '') {
        $until = strtotime($attempt['locked_until']);
        if ($until > time()) {
            $mins = ceil(($until - time()) / 60);
            $error = "失败次数过多,已锁定,请 {$mins} 分钟后再试";
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $error === '') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = db()->prepare('SELECT * FROM users WHERE username = ?');
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        // 登录成功:清空失败记录
        if ($ip) {
            db()->prepare('DELETE FROM login_attempts WHERE ip=?')->execute([$ip]);
        }
        session_regenerate_id(true);
        $_SESSION['uid'] = (int)$user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['last_active'] = time();
        redirect('/admin/dashboard.php');
    }

    // 记录失败
    if ($ip) {
        $pdo = db();
        $stmt = $pdo->prepare('SELECT fails FROM login_attempts WHERE ip=?');
        $stmt->execute([$ip]);
        $fails = (int)($stmt->fetchColumn() ?: 0) + 1;
        $locked = $fails >= $maxFails ? date('Y-m-d H:i:s', time() + $lockMinutes * 60) : '';
        $pdo->prepare('INSERT INTO login_attempts (ip, fails, locked_until) VALUES (?,?,?)
            ON CONFLICT(ip) DO UPDATE SET fails=excluded.fails, locked_until=excluded.locked_until')
            ->execute([$ip, $fails, $locked]);
        $error = $fails >= $maxFails
            ? "连续失败 {$maxFails} 次,已锁定 {$lockMinutes} 分钟"
            : "用户名或密码错误(还可尝试 " . ($maxFails - $fails) . " 次)";
    } else {
        $error = '用户名或密码错误';
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>登录 - 江江.com 后台</title>
<link rel="stylesheet" href="/assets/admin.css">
</head>
<body class="login-body">
<div class="login-box">
    <h1 class="login-title">江江.com 后台</h1>
    <?php if ($error): ?><div class="flash flash-error"><?= e($error) ?></div><?php endif; ?>
    <form method="post" autocomplete="off">
        <label class="field">
            <span>用户名</span>
            <input type="text" name="username" required autofocus>
        </label>
        <label class="field">
            <span>密码</span>
            <input type="password" name="password" required>
        </label>
        <button class="btn btn-primary btn-block" type="submit">登 录</button>
    </form>
    <p class="login-back"><a href="/">← 返回前台</a></p>
</div>
</body>
</html>
