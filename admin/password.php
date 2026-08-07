<?php
/**
 * 修改密码
 */
require_once __DIR__ . '/includes/auth.php';
require_login();
require_once __DIR__ . '/includes/header.php';

$error = '';
$ok = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $old = $_POST['old_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    $stmt = db()->prepare('SELECT * FROM users WHERE id=?');
    $stmt->execute([(int)$_SESSION['uid']]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($old, $user['password_hash'])) {
        $error = '当前密码不正确';
    } elseif (strlen($new) < 8) {
        $error = '新密码至少 8 位';
    } elseif ($new !== $confirm) {
        $error = '两次输入的新密码不一致';
    } else {
        $upd = db()->prepare('UPDATE users SET password_hash=? WHERE id=?');
        $upd->execute([password_hash($new, PASSWORD_DEFAULT), (int)$_SESSION['uid']]);
        $ok = true;
    }
}

admin_header('password');
flash_show();
?>
<h1 class="page-title">修改密码</h1>

<div class="card form" style="max-width:480px">
    <?php if ($ok): ?>
    <div class="flash flash-ok">✅ 密码修改成功</div>
    <?php endif; ?>
    <?php if ($error): ?>
    <div class="flash flash-error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
        <label class="field">
            <span>当前密码</span>
            <input type="password" name="old_password" required autocomplete="current-password">
        </label>
        <label class="field">
            <span>新密码(至少 8 位)</span>
            <input type="password" name="new_password" required minlength="8" autocomplete="new-password">
        </label>
        <label class="field">
            <span>确认新密码</span>
            <input type="password" name="confirm_password" required minlength="8" autocomplete="new-password">
        </label>
        <button class="btn btn-primary" type="submit">修改密码</button>
    </form>
</div>
<?php admin_footer(); ?>
