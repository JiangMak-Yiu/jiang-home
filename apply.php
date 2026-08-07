<?php
/**
 * 友链申请页
 */
session_start();
require_once __DIR__ . '/includes/functions.php';

$title = setting('site_title', '江江个人主页');
$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 蜜罐字段:填了就是机器人
    if (!empty($_POST['website'])) {
        exit('非法请求');
    }
    // 提交频率限制:60 秒一次
    $now = time();
    if (!empty($_SESSION['apply_last']) && ($now - $_SESSION['apply_last']) < 60) {
        $err = '提交太频繁了,请 ' . (60 - ($now - $_SESSION['apply_last'])) . ' 秒后再试';
    } else {
        $name = trim($_POST['name'] ?? '');
        $url  = trim($_POST['url'] ?? '');
        $icon = trim($_POST['icon'] ?? '');
        $desc = trim($_POST['description'] ?? '');

        if ($name === '' || $url === '') {
            $err = '名称和链接必填';
        } elseif (mb_strlen($name) > 30 || mb_strlen($desc) > 100) {
            $err = '内容超出长度限制';
        } else {
            $stmt = db()->prepare('INSERT INTO friend_requests (name, url, icon, description) VALUES (?,?,?,?)');
            $stmt->execute([$name, $url, $icon, $desc]);
            $_SESSION['apply_last'] = $now;
            $msg = '✅ 申请已提交,站长审核通过后会展示在友链区';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>申请友链 - <?= e($title) ?></title>
<link rel="stylesheet" href="https://cdn.staticfile.org/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="style.css">
<style>
    .apply-wrap { max-width: 560px; margin: 0 auto; padding: 80px 20px 40px; }
    .apply-card {
        background: rgba(255,255,255,0.06);
        border: 1px solid rgba(255,255,255,0.12);
        border-radius: 16px;
        padding: 30px;
        backdrop-filter: blur(10px);
    }
    .apply-card h1 { font-size: 24px; margin-bottom: 8px; }
    .apply-card .tip { color: rgba(255,255,255,0.55); font-size: 13px; margin-bottom: 22px; }
    .apply-field { margin-bottom: 16px; }
    .apply-field label { display: block; margin-bottom: 6px; font-size: 14px; color: rgba(255,255,255,0.8); }
    .apply-field input {
        width: 100%; padding: 10px 14px; border-radius: 10px;
        border: 1px solid rgba(255,255,255,0.15);
        background: rgba(255,255,255,0.06); color: #fff; font-size: 14px; outline: none;
    }
    .apply-field input:focus { border-color: #6c8cff; }
    .apply-btn {
        width: 100%; padding: 12px; border: none; border-radius: 10px;
        background: linear-gradient(120deg, #6c8cff, #9b6cff); color: #fff;
        font-size: 15px; cursor: pointer; font-weight: 600;
    }
    .apply-btn:hover { opacity: 0.9; }
    .apply-msg { padding: 12px 16px; border-radius: 10px; margin-bottom: 18px; font-size: 14px; }
    .apply-msg.ok { background: rgba(52,201,142,0.15); color: #34c98e; }
    .apply-msg.err { background: rgba(240,97,109,0.15); color: #f0616d; }
    .apply-back { text-align: center; margin-top: 20px; }
    .apply-back a { color: rgba(255,255,255,0.55); text-decoration: none; font-size: 14px; }
    .apply-back a:hover { color: #6c8cff; }
</style>
</head>
<body>
<div class="apply-wrap">
    <div class="apply-card">
        <h1>🌐 申请友链</h1>
        <p class="tip">填写你的站点信息,审核通过后就会出现在友链区啦</p>

        <?php if ($msg): ?><div class="apply-msg ok"><?= e($msg) ?></div><?php endif; ?>
        <?php if ($err): ?><div class="apply-msg err"><?= e($err) ?></div><?php endif; ?>

        <form method="post" autocomplete="off">
            <div class="apply-field">
                <label>站点名称 *</label>
                <input type="text" name="name" required maxlength="30" placeholder="你的站点名字">
            </div>
            <div class="apply-field">
                <label>站点链接 *</label>
                <input type="url" name="url" required maxlength="300" placeholder="https://example.com">
            </div>
            <div class="apply-field">
                <label>图标 URL</label>
                <input type="url" name="icon" maxlength="300" placeholder="https://example.com/icon.png(可留空)">
            </div>
            <div class="apply-field">
                <label>一句话简介</label>
                <input type="text" name="description" maxlength="100" placeholder="用一句话介绍你的站点(可留空)">
            </div>
            <!-- 蜜罐防机器人 -->
            <div style="position:absolute;left:-9999px">
                <input type="text" name="website" tabindex="-1" autocomplete="off">
            </div>
            <button class="apply-btn" type="submit">提交申请</button>
        </form>
        <p class="apply-back"><a href="/">← 返回首页</a></p>
    </div>
</div>
</body>
</html>
