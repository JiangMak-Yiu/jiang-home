<?php
/**
 * 数据库连接与初始化
 * 使用 SQLite,零配置
 */

define('DB_PATH', __DIR__ . '/../data/site.db');

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $dir = dirname(DB_PATH);
        if (!is_dir($dir)) {
            mkdir($dir, 0750, true);
        }
        $pdo = new PDO('sqlite:' . DB_PATH);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->exec('PRAGMA journal_mode=WAL');
        init_schema($pdo);
    }
    return $pdo;
}

function init_schema(PDO $pdo): void
{
    $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
        key TEXT PRIMARY KEY,
        value TEXT NOT NULL DEFAULT ''
    )");
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE NOT NULL,
        password_hash TEXT NOT NULL,
        created_at TEXT NOT NULL DEFAULT (datetime('now'))
    )");
    $pdo->exec("CREATE TABLE IF NOT EXISTS links (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        url TEXT NOT NULL,
        icon TEXT NOT NULL DEFAULT '',
        description TEXT NOT NULL DEFAULT '',
        category TEXT NOT NULL DEFAULT '默认',
        sort INTEGER NOT NULL DEFAULT 0,
        created_at TEXT NOT NULL DEFAULT (datetime('now'))
    )");
    $pdo->exec("CREATE TABLE IF NOT EXISTS nav_links (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        url TEXT NOT NULL,
        icon TEXT NOT NULL DEFAULT '',
        description TEXT NOT NULL DEFAULT '',
        type TEXT NOT NULL DEFAULT 'group',
        group_name TEXT NOT NULL DEFAULT '',
        sort INTEGER NOT NULL DEFAULT 0,
        created_at TEXT NOT NULL DEFAULT (datetime('now'))
    )");

    // 友链申请(前台提交,后台审核)
    $pdo->exec("CREATE TABLE IF NOT EXISTS friend_requests (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        url TEXT NOT NULL,
        icon TEXT NOT NULL DEFAULT '',
        description TEXT NOT NULL DEFAULT '',
        status TEXT NOT NULL DEFAULT 'pending',
        created_at TEXT NOT NULL DEFAULT (datetime('now'))
    )");

    // 登录失败记录(防暴力破解)
    $pdo->exec("CREATE TABLE IF NOT EXISTS login_attempts (
        ip TEXT PRIMARY KEY,
        fails INTEGER NOT NULL DEFAULT 0,
        locked_until TEXT NOT NULL DEFAULT ''
    )");

    // 兼容迁移:links 表补 cover 列(友链封面图)
    $cols = $pdo->query('PRAGMA table_info(links)')->fetchAll();
    $hasCover = false;
    foreach ($cols as $c) {
        if ($c['name'] === 'cover') { $hasCover = true; break; }
    }
    if (!$hasCover) {
        $pdo->exec("ALTER TABLE links ADD COLUMN cover TEXT NOT NULL DEFAULT ''");
    }

    // 默认设置
    $defaults = [
        'site_title'     => '江江个人主页',
        'site_desc'      => '江江的个人主页',
        'site_notice'    => '',
        'site_icp'       => '',
        'site_footer'    => '',
        'site_hero_name' => '江江同学',
        'site_hero_desc' => '秋凉意，江秋寒.',
        'site_qq_url'    => '',
        'site_tg_url'    => '',
        'site_avatar'    => './favicon.ico',
        'site_roles'     => 'fa-child:小学生,fa-seedling:小白,fa-trash-alt:废物',
        'site_typing'    => '编程,公益,创新,分享',
    ];
    $stmt = $pdo->prepare('INSERT OR IGNORE INTO settings (key, value) VALUES (?, ?)');
    foreach ($defaults as $k => $v) {
        $stmt->execute([$k, $v]);
    }

    // 默认管理员(首次运行时创建,随机密码写入 data/初始密码.txt)
    $count = (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    if ($count === 0) {
        $pass = bin2hex(random_bytes(6));
        $stmt = $pdo->prepare('INSERT INTO users (username, password_hash) VALUES (?, ?)');
        $stmt->execute(['admin', password_hash($pass, PASSWORD_DEFAULT)]);
        $tipFile = dirname(DB_PATH) . '/初始密码.txt';
        file_put_contents(
            $tipFile,
            "江江.com 后台初始登录信息\n============================\n账号: admin\n密码: {$pass}\n\n登录地址: https://江江.com/admin/\n登录后请立即在「修改密码」中更换!\n",
            LOCK_EX
        );
    }
}
