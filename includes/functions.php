<?php
/**
 * 公共函数
 */

require_once __DIR__ . '/db.php';

/** 读取站点设置(带缓存) */
function setting(string $key, string $default = ''): string
{
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        foreach (db()->query('SELECT key, value FROM settings') as $row) {
            $cache[$row['key']] = $row['value'];
        }
    }
    return $cache[$key] ?? $default;
}

/** 设置站点设置 */
function set_setting(string $key, string $value): void
{
    $stmt = db()->prepare('INSERT INTO settings (key, value) VALUES (?, ?)
        ON CONFLICT(key) DO UPDATE SET value = excluded.value');
    $stmt->execute([$key, $value]);
}

/** HTML 转义 */
function e(?string $s): string
{
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

/** 获取或生成 CSRF token */
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

/** 校验 CSRF token */
function csrf_check(): void
{
    $t = $_POST['csrf'] ?? '';
    if (empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $t)) {
        http_response_code(403);
        exit('CSRF 校验失败,请返回重试');
    }
}

/** 重定向 */
function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

/** 后台基础 URL */
function admin_url(): string
{
    return '/admin/';
}

/** 前台相对路径(处理站点可能部署在子目录的情况,这里固定根目录) */
function base_url(): string
{
    return '';
}
