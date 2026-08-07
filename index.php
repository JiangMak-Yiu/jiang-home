<?php
/**
 * 江江.com 前台首页(原站设计 + PHP 动态渲染)
 */
session_start();
require_once __DIR__ . '/includes/functions.php';

$title   = setting('site_title', '江江个人主页');
$desc    = setting('site_desc', '');
$notice  = setting('site_notice', '');            // 支持 HTML
$heroName = setting('site_hero_name', '江江同学');
$heroDesc = setting('site_hero_desc', '');
$footer  = setting('site_footer', '');
$icp     = setting('site_icp', '');
$qqUrl   = setting('site_qq_url', '');
$tgUrl   = setting('site_tg_url', '');
$avatar  = setting('site_avatar', './favicon.ico');
$rolesRaw = setting('site_roles', 'fa-child:小学生,fa-seedling:小白,fa-trash-alt:废物');
$typingRaw = setting('site_typing', '编程,公益,创新,分享');

// 角色标签: "图标:文字" 逗号分隔
$roles = [];
foreach (explode(',', $rolesRaw) as $r) {
    $r = trim($r);
    if ($r === '') continue;
    if (str_contains($r, ':')) {
        [$ic, $txt] = explode(':', $r, 2);
        $roles[] = ['icon' => trim($ic), 'text' => trim($txt)];
    } else {
        $roles[] = ['icon' => 'fa-tag', 'text' => $r];
    }
}
// 打字机词组
$typing = array_values(array_filter(array_map('trim', explode(',', $typingRaw))));

// 常用入口
$featured = db()->query("SELECT * FROM nav_links WHERE type='featured' ORDER BY sort ASC, id ASC")->fetchAll();
// 站点索引(按分组)
$groups = [];
foreach (db()->query("SELECT * FROM nav_links WHERE type='group' ORDER BY group_name ASC, sort ASC, id ASC")->fetchAll() as $g) {
    $groups[$g['group_name']][] = $g;
}
// 友链(按分类)
$flinks = [];
foreach (db()->query('SELECT * FROM links ORDER BY sort ASC, id ASC')->fetchAll() as $f) {
    $flinks[$f['category']][] = $f;
}
// 分组顺序保持原站顺序(工具/资源/教程/娱乐)
$groupOrder = ['工具与效率', '资源与下载', '教程与导航', '娱乐与其他'];
uasort($groups, function ($a, $b) use ($groupOrder) {
    $ka = array_search($a[0]['group_name'], $groupOrder);
    $kb = array_search($b[0]['group_name'], $groupOrder);
    if ($ka === false) $ka = 99;
    if ($kb === false) $kb = 99;
    return $ka <=> $kb;
});
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <!-- 基本元数据设置 -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title) ?></title>
    
    <!-- SEO优化元标签 -->
    <meta name="description" content="<?= e($desc) ?>">
    <meta name="keywords" content="江江">
    <meta name="author" content="江江">
    <link rel="canonical" href="https://www.江江.com/">
    
    <!-- 网站图标设置 -->
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
    
    <!-- 外部CSS资源引入 -->
    <link rel="stylesheet" href="https://cdn.staticfile.org/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=ZCOOL+KuaiLe&family=Noto+Sans+SC:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="preload" href="style.css" as="style">

    <!-- 结构化数据标记(Schema.org)，提升SEO效果 -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Person",
      "name": "伊卜拉伊木·买买提江",
      "alternateName": "江江",
      "url": "https://www.江江.com",
      "email": "i@sayqz.com",
      "jobTitle": "全栈开发工程师",
      "description": "一名热爱编程与技术创新的全栈开发工程师，TuneFree音乐播放器作者。",
      "knowsAbout": ["Vue3", "Node.js", "PHP", "Electron", "JavaScript", "全栈开发"],
      "sameAs": [
        "https://github.com/"
      ]
    }
    </script>

    <script async src="//busuanzi.ibruce.info/busuanzi/2.3/busuanzi.pure.mini.js"></script>
</head>
<body>
    <!-- 加载动画 -->
    <div class="loader-wrapper">
        <div class="loader-container" aria-hidden="true">
            <div class="loader-orb"></div>
            <div class="loader-orb loader-orb-small"></div>
        </div>
        <div class="loader-text">正在进入江江的星球</div>
        <div class="loader-tip">今天也要开心写代码</div>
        <div class="loader-progress">
            <span class="loader-progress-inner"></span>
        </div>
        <div class="loader-percent">0%</div>
    </div>

    <?php if ($notice): ?>
    <!-- 进站公告弹窗(自动展开/自动关闭) -->
    <div class="entry-notice is-hidden" role="dialog" aria-label="进站公告" aria-live="polite" data-entry-notice-id="2026-03-sites">
        <div class="entry-notice-inner">
            <div class="entry-notice-header">
                <div class="entry-notice-headline">
                    <i class="fas fa-bullhorn" aria-hidden="true"></i>
                    <span class="entry-notice-title">公告</span>
                </div>
                <button class="entry-notice-close" type="button" aria-label="关闭弹窗">
                    <i class="fas fa-times" aria-hidden="true"></i>
                </button>
            </div>
            <div class="entry-notice-body">
                <?= $notice ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- 主要内容区域 -->
    <main class="content">
        <!-- 首页内容部分 -->
        <section id="home" class="section active">
            <div class="hero-content">
                <div class="hero-profile">
                    <!-- 头像展示区域 -->
                    <div class="hero-avatar-wrapper">
                        <img src="<?= e($avatar) ?>" alt="头像" class="hero-avatar">
                        <div class="hero-avatar-glow"></div>
                    </div>
                    <!-- 个人信息文本区域 -->
                    <div class="hero-text">
                        <!-- 标题部分 -->
                        <div class="hero-title">
                            <span class="emoji-wrapper">
                                <span class="wave-emoji">🌹</span>
                            </span>
                            <h1 data-text="是青旨啊"><?= e($heroName) ?></h1>
                        </div>
                        <!-- 个人描述部分 -->
                        <div class="hero-description">
                            <p class="subtitle"><?= e($heroDesc) ?></p>
                            <!-- 角色标签 -->
                            <div class="role-tags">
                                <?php foreach ($roles as $r): ?>
                                <span class="role-tag"><i class="fas <?= e($r['icon']) ?>" aria-hidden="true"></i><?= e($r['text']) ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <!-- 打字机效果区域 -->
                        <div class="typing-container">
                            <div class="typing-prefix">热爱</div>
                            <div class="typing-text">
                                <span class="typed"></span>
                            </div>
                        </div>

                        <?php if ($notice): ?>
                        <!-- 公告 -->
                        <div class="site-announcement" role="region" aria-label="公告" data-announcement-id="2026-03-sites">
                            <div class="announcement-inner">
                                <i class="fas fa-bullhorn" aria-hidden="true"></i>
                                <div class="announcement-text">
                                    <strong>公告：</strong>
                                    <?= $notice ?>
                                </div>
                                <button class="announcement-close" type="button" aria-label="关闭公告">
                                    <i class="fas fa-times" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- 网站导航卡片区域 -->
                        <div class="home-links">
                            <div class="link-block-title">常用入口</div>
                            <div class="site-cards featured-links" role="list" aria-label="常用入口">
                                <?php foreach ($featured as $item): ?>
                                <a href="<?= e($item['url']) ?>" target="_blank" rel="noopener noreferrer" class="site-card" role="listitem">
                                    <i class="fas <?= e($item['icon']) ?>" aria-hidden="true"></i>
                                    <h3><?= e($item['name']) ?></h3>
                                    <p><?= e($item['description']) ?></p>
                                </a>
                                <?php endforeach; ?>
                            </div>

                            <?php if ($groups): ?>
                            <div class="more-sites">
                                <div class="more-sites-title">
                                    <span class="summary-title">站点索引</span>
                                    <span class="summary-meta">按场景快速直达</span>
                                </div>
                                <div class="link-groups" aria-label="站点分类">
                                <?php $gi = 0; foreach ($groups as $gname => $items): $gid = 'group-' . $gi++; ?>
                                    <section class="link-group" aria-labelledby="<?= $gid ?>">
                                        <h4 id="<?= $gid ?>"><?= e($gname) ?></h4>
                                        <div class="site-cards" role="list">
                                            <?php foreach ($items as $item): ?>
                                            <a href="<?= e($item['url']) ?>" target="_blank" rel="noopener noreferrer" class="site-card" role="listitem"><i class="fas <?= e($item['icon']) ?>" aria-hidden="true"></i><h3><?= e($item['name']) ?></h3><p><?= e($item['description']) ?></p></a>
                                            <?php endforeach; ?>
                                        </div>
                                    </section>
                                <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 友链部分 -->
        <?php if ($flinks): ?>
        <section id="friends" class="section">
            <div class="flink-container">
                <div class="flink-apply-bar">
                    <span>想交换友链?</span>
                    <a href="/apply.php" target="_blank" rel="noopener">申请友链 →</a>
                </div>
                <div class="flink">
                    <?php foreach ($flinks as $cat => $items): ?>
                    <h2><?= e($cat) ?></h2>
                    <?php
                    // 不同分类用不同样式结构(还原原站)
                    $listClass = 'flexcard-flink-list';
                    if ($cat === '项目') $listClass = 'telescopic-flink-list';
                    if ($cat === '小伙伴') $listClass = 'anzhiyu-flink-list';
                    ?>
                    <div class="<?= $listClass ?>">
                        <?php foreach ($items as $l): ?>
                        <a class="site-card" href="<?= e($l['url']) ?>" target="_blank" rel="noopener noreferrer">
                            <?php if ($cat !== '小伙伴' && $l['cover']): ?>
                            <div class="wrapper cover">
                                <img class="cover fadeIn" src="<?= e($l['cover']) ?>"/>
                                <?php if ($cat === '推荐网站'): ?>
                                <div class="cover-overlay">
                                    <div class="cover-info">
                                        <img class="overlay-avatar" src="<?= e($l['icon']) ?>"/>
                                        <span class="site-name"><?= e($l['name']) ?></span>
                                        <span class="site-desc"><?= e($l['description']) ?></span>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                            <div class="info">
                                <?php if ($l['icon']): ?><img class="flink-avatar" src="<?= e($l['icon']) ?>"/><?php endif; ?>
                                <?php if ($cat === '项目'): ?>
                                <span class="site-descr"><?= e($l['description']) ?></span>
                                <?php else: ?>
                                <span class="site-title"><?= e($l['name']) ?></span>
                                <?php endif; ?>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>
    </main>

    <!-- 页脚区域 -->
    <footer>
        <!-- 波浪动画效果 -->
        <div class="footer-waves">
            <svg class="waves" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 24 150 28" preserveAspectRatio="none">
                <defs>
                    <path id="wave-path" d="M-160 44c30 0 58-18 88-18s 58 18 88 18 58-18 88-18 58 18 88 18 v44h-352z"></path>
                </defs>
                <g class="wave-parallax">
                    <use xlink:href="#wave-path" x="50" y="3" fill="rgba(52, 152, 219, 0.1)"></use>
                </g>
                <g class="wave-parallax" opacity="0.5">
                    <use xlink:href="#wave-path" x="50" y="0" fill="rgba(46, 204, 113, 0.1)"></use>
                </g>
                <g class="wave-parallax" opacity="0.3">
                    <use xlink:href="#wave-path" x="50" y="6" fill="rgba(155, 89, 182, 0.1)"></use>
                </g>
            </svg>
        </div>
        <!-- 页脚内容区域 -->
        <div class="footer-content">
            <div class="footer-main">
                <!-- 页脚左侧信息 -->
                <div class="footer-info">
                    <div class="footer-logo">
                        <span class="logo-text">江南安</span>
                        <span class="logo-dot"></span>
                    </div>
                    <p class="footer-copyright"><?= e($footer) ?></p>
                </div>

                <!-- 添加访问统计 -->
                <div class="footer-stats">
                    <div class="stats-item">
                        <i class="fas fa-eye"></i>
                        <span>总访问量</span>
                        <span id="busuanzi_value_site_pv">0</span>
                    </div>
                    <div class="stats-item">
                        <i class="fas fa-user"></i>
                        <span>访客数</span>
                        <span id="busuanzi_value_site_uv">0</span>
                    </div>
                </div>

                <!-- 页脚右侧链接 -->
                <div class="footer-links">
                    <?php if ($qqUrl): ?>
                    <a href="<?= e($qqUrl) ?>" target="_blank" rel="noopener noreferrer" class="footer-link">
                        <i class="fab fa-qq"></i>
                        QQ群 1028010826
                    </a>
                    <?php endif; ?>
                    <?php if ($tgUrl): ?>
                    <a href="<?= e($tgUrl) ?>" target="_blank" rel="noopener noreferrer" class="footer-link">
                        <i class="fab fa-telegram"></i>
                        Telegram 频道
                    </a>
                    <?php endif; ?>
                    <?php if ($icp): ?>
                    <a href="https://beian.miit.gov.cn/" target="_blank" class="footer-link">
                        <i class="fas fa-shield-alt"></i>
                        <?= e($icp) ?>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </footer>

    <!-- JavaScript脚本引入 -->
    <?php if ($typing): ?>
    <script>window.JJ_TYPING = <?= json_encode($typing, JSON_UNESCAPED_UNICODE) ?>;</script>
    <?php endif; ?>
    <script src="script.js"></script>
</body>
</html>
