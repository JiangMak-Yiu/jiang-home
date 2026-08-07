<?php
/**
 * 站点设置:标题 / 简介 / 公告 / ICP / 页脚
 */
require_once __DIR__ . '/includes/auth.php';
require_login();
require_once __DIR__ . '/includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $fields = ['site_title', 'site_desc', 'site_notice', 'site_icp', 'site_footer',
               'site_hero_name', 'site_hero_desc', 'site_qq_url', 'site_tg_url',
               'site_avatar', 'site_roles', 'site_typing'];
    foreach ($fields as $f) {
        set_setting($f, trim($_POST[$f] ?? ''));
    }
    flash_set('ok', '设置已保存');
    redirect('/admin/settings.php');
}

$vals = [];
foreach (['site_title', 'site_desc', 'site_notice', 'site_icp', 'site_footer',
          'site_hero_name', 'site_hero_desc', 'site_qq_url', 'site_tg_url',
          'site_avatar', 'site_roles', 'site_typing'] as $f) {
    $vals[$f] = setting($f);
}

admin_header('settings');
flash_show();
?>
<h1 class="page-title">站点设置</h1>

<form method="post" class="card form">
    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
    <label class="field">
        <span>站点标题(浏览器标签页标题)</span>
        <input type="text" name="site_title" value="<?= e($vals['site_title']) ?>" maxlength="60">
    </label>
    <label class="field">
        <span>站点简介(SEO 描述)</span>
        <input type="text" name="site_desc" value="<?= e($vals['site_desc']) ?>" maxlength="200">
    </label>
    <label class="field">
        <span>主页大标题(hero 名称)</span>
        <input type="text" name="site_hero_name" value="<?= e($vals['site_hero_name']) ?>" maxlength="60">
    </label>
    <label class="field">
        <span>主页副标题(hero 描述)</span>
        <input type="text" name="site_hero_desc" value="<?= e($vals['site_hero_desc']) ?>" maxlength="120">
    </label>
    <label class="field">
        <span>头像 URL(默认 ./favicon.ico)</span>
        <input type="url" name="site_avatar" value="<?= e($vals['site_avatar']) ?>" maxlength="300" placeholder="./favicon.ico">
    </label>
    <label class="field">
        <span>角色标签(格式:图标:文字,用英文逗号分隔,如 fa-child:小学生)</span>
        <input type="text" name="site_roles" value="<?= e($vals['site_roles']) ?>" maxlength="300">
    </label>
    <label class="field">
        <span>打字机文字(英文逗号分隔,如 编程,公益,创新,分享)</span>
        <input type="text" name="site_typing" value="<?= e($vals['site_typing']) ?>" maxlength="200">
    </label>
    <label class="field">
        <span>公告(留空则不显示;支持 HTML,如 &lt;a href="https://..."&gt;链接&lt;/a&gt;)</span>
        <input type="text" name="site_notice" value="<?= e($vals['site_notice']) ?>" maxlength="500">
    </label>
    <label class="field">
        <span>页脚版权文字</span>
        <input type="text" name="site_footer" value="<?= e($vals['site_footer']) ?>" maxlength="120">
    </label>
    <label class="field">
        <span>ICP 备案号(留空不显示)</span>
        <input type="text" name="site_icp" value="<?= e($vals['site_icp']) ?>" maxlength="60">
    </label>
    <label class="field">
        <span>QQ 群链接(留空不显示)</span>
        <input type="url" name="site_qq_url" value="<?= e($vals['site_qq_url']) ?>" maxlength="300" placeholder="https://qm.qq.com/q/...">
    </label>
    <label class="field">
        <span>Telegram 链接(留空不显示)</span>
        <input type="url" name="site_tg_url" value="<?= e($vals['site_tg_url']) ?>" maxlength="300" placeholder="https://t.me/...">
    </label>
    <button class="btn btn-primary" type="submit">保存设置</button>
</form>
<?php admin_footer(); ?>
