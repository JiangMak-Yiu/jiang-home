<?php
/**
 * 后台认证
 */
session_start();
require_once __DIR__ . '/../../includes/functions.php';

/** 会话超时时间(秒):30 分钟无操作自动登出 */
const SESSION_TIMEOUT = 1800;

function require_login(): void
{
    if (empty($_SESSION['uid'])) {
        redirect('/admin/');
    }
    // 空闲超时检查
    $now = time();
    $last = (int)($_SESSION['last_active'] ?? 0);
    if ($last && ($now - $last) > SESSION_TIMEOUT) {
        session_unset();
        redirect('/admin/?timeout=1');
    }
    $_SESSION['last_active'] = $now;
}
