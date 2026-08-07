document.addEventListener('DOMContentLoaded', () => {
    // 外链安全属性兜底
    document.querySelectorAll('a[target="_blank"]').forEach((link) => {
        const rel = link.getAttribute('rel') || '';
        const tokens = rel.split(/\s+/).filter(Boolean);

        if (!tokens.includes('noopener')) tokens.push('noopener');
        if (!tokens.includes('noreferrer')) tokens.push('noreferrer');

        link.setAttribute('rel', tokens.join(' '));
    });

    // 加载动画与首屏衔接
    const loader = document.querySelector('.loader-wrapper');
    const loaderProgress = document.querySelector('.loader-progress-inner');
    const loaderPercent = document.querySelector('.loader-percent');
    const loaderTip = document.querySelector('.loader-tip');
    const loadingTips = [
        '今天也要开心写代码',
        '灵感加载中，请稍等片刻',
        '愿你打开页面就有好心情',
        '生活有光，代码也有光'
    ];
    const minLoaderDuration = 800;
    const loadStartedAt = performance.now();
    let currentProgress = 0;
    let targetProgress = 12;
    let hasRevealed = false;

    document.body.classList.add('is-loading');

    // 首页站点卡片入场（瀑布式）与分组入场
    const setupHomeReveals = () => {
        const featuredCards = Array.from(document.querySelectorAll('.featured-links .site-card'));
        const groupTitle = document.querySelector('.more-sites-title');
        const linkGroups = Array.from(document.querySelectorAll('.link-groups .link-group'));

        const reducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (reducedMotion) return;

        const baseDelay = 140;
        const cardStep = 80;

        featuredCards.forEach((card, idx) => {
            card.classList.add('reveal-item');
            card.style.setProperty('--reveal-delay', `${baseDelay + idx * cardStep}ms`);
        });

        let nextDelay = baseDelay + featuredCards.length * cardStep + 140;

        if (groupTitle) {
            groupTitle.classList.add('reveal-item');
            groupTitle.style.setProperty('--reveal-delay', `${nextDelay}ms`);
            nextDelay += 120;
        }

        linkGroups.forEach((group, idx) => {
            group.classList.add('reveal-group');
            group.style.setProperty('--reveal-delay', `${nextDelay + idx * 120}ms`);
        });
    };

    setupHomeReveals();

    if (loaderTip) {
        const randomTip = loadingTips[Math.floor(Math.random() * loadingTips.length)];
        loaderTip.textContent = randomTip;
    }

    const setProgress = (value) => {
        if (!loaderProgress) return;
        const safeValue = Math.min(100, Math.max(0, value));
        loaderProgress.style.width = `${safeValue}%`;
        if (loaderPercent) {
            loaderPercent.textContent = `${Math.round(safeValue)}%`;
        }
    };

    const animateProgress = () => {
        if (currentProgress >= targetProgress - 0.2) return;
        currentProgress += (targetProgress - currentProgress) * 0.2 + 0.12;
        setProgress(currentProgress);
    };

    const progressTimer = setInterval(animateProgress, 50);

    const trackResourceProgress = () => {
        const resources = [];
        const pushUnique = (elements) => {
            elements.forEach((el) => {
                if (!resources.includes(el)) {
                    resources.push(el);
                }
            });
        };

        pushUnique(Array.from(document.images));
        pushUnique(Array.from(document.querySelectorAll('link[rel="stylesheet"]')));
        pushUnique(Array.from(document.querySelectorAll('script[src]')));

        if (!resources.length) {
            targetProgress = 92;
            return;
        }

        let loadedCount = 0;
        const totalCount = resources.length + (document.fonts ? 1 : 0);

        const updateTargetByLoaded = () => {
            const ratio = loadedCount / totalCount;
            targetProgress = 12 + ratio * 80;
        };

        const markLoaded = () => {
            loadedCount += 1;
            updateTargetByLoaded();
        };

        resources.forEach((resource) => {
            const tag = resource.tagName;

            if (tag === 'IMG' && resource.complete) {
                markLoaded();
                return;
            }

            if (tag === 'LINK' && resource.sheet) {
                markLoaded();
                return;
            }

            if (tag === 'SCRIPT' && (resource.readyState === 'loaded' || resource.readyState === 'complete')) {
                markLoaded();
                return;
            }

            const done = () => {
                resource.removeEventListener('load', done);
                resource.removeEventListener('error', done);
                markLoaded();
            };

            resource.addEventListener('load', done, { once: true });
            resource.addEventListener('error', done, { once: true });
        });

        if (document.fonts) {
            document.fonts.ready.then(markLoaded).catch(markLoaded);
        }

        updateTargetByLoaded();
    };

    trackResourceProgress();

    const revealContent = () => {
        if (hasRevealed) return;
        hasRevealed = true;

        clearInterval(progressTimer);
        setProgress(100);

        setTimeout(() => {
            if (loader) {
                loader.classList.add('hidden');
            }
            document.body.classList.remove('is-loading');
            document.body.classList.add('page-ready');

            // 进站弹窗：从中间向上下展开，3 秒后自动收起
            const entryNotice = document.querySelector('.entry-notice');
            if (entryNotice) {
                const closeBtn = entryNotice.querySelector('.entry-notice-close');
                const inner = entryNotice.querySelector('.entry-notice-inner');
                let autoCloseTimer = null;
                let didClose = false;

                const noticeId = entryNotice.getAttribute('data-entry-notice-id') || 'default';
                const storageKey = `jj_entry_notice_last_shown_${noticeId}`;
                const getLocalDateKey = () => {
                    const now = new Date();
                    const y = now.getFullYear();
                    const m = String(now.getMonth() + 1).padStart(2, '0');
                    const d = String(now.getDate()).padStart(2, '0');
                    return `${y}-${m}-${d}`;
                };

                const markShownToday = () => {
                    try {
                        localStorage.setItem(storageKey, getLocalDateKey());
                    } catch (e) {
                        // 忽略 localStorage 不可用的情况
                    }
                };

                // 每天只展示一次
                try {
                    const lastShown = localStorage.getItem(storageKey);
                    if (lastShown === getLocalDateKey()) {
                        entryNotice.classList.add('is-hidden');
                        return;
                    }
                } catch (e) {
                    // 忽略
                }

                const hardHide = () => {
                    entryNotice.classList.remove('is-open', 'is-closing');
                    entryNotice.classList.add('is-hidden');
                };

                const closeNotice = () => {
                    if (didClose) return;
                    didClose = true;
                    if (autoCloseTimer) window.clearTimeout(autoCloseTimer);

                    // 记为今日已展示（无论手动/自动关闭）
                    markShownToday();

                    const reducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                    if (reducedMotion) {
                        hardHide();
                        return;
                    }

                    entryNotice.classList.remove('is-open');
                    entryNotice.classList.add('is-closing');

                    const onEnd = () => {
                        if (inner) inner.removeEventListener('animationend', onEnd);
                        hardHide();
                    };

                    if (inner) {
                        inner.addEventListener('animationend', onEnd);
                        // 兜底：极端情况下 animationend 不触发
                        window.setTimeout(onEnd, 900);
                    } else {
                        window.setTimeout(hardHide, 650);
                    }
                };

                // 显示
                const showNotice = () => {
                    entryNotice.classList.remove('is-hidden', 'is-closing', 'is-open');
                    // 强制回流，确保展开动画稳定触发
                    void entryNotice.offsetHeight;
                    entryNotice.classList.add('is-open');

                    // 一出现就记为今天已展示，避免用户刷新刷出多次
                    markShownToday();

                    // 自动关闭（从显示开始计时）
                    autoCloseTimer = window.setTimeout(closeNotice, 3000);

                    // 手动关闭
                    if (closeBtn) {
                        closeBtn.addEventListener('click', closeNotice, { once: true });
                    }
                };

                // 展示时机：让主页先稳定出现再弹出
                window.setTimeout(showNotice, 480);
            }
        }, 220);
    };

    const finishWhenReady = () => {
        const elapsed = performance.now() - loadStartedAt;
        const waitTime = Math.max(0, minLoaderDuration - elapsed);
        setTimeout(revealContent, waitTime);
    };

    if (document.readyState === 'complete') {
        finishWhenReady();
    } else {
        window.addEventListener('load', finishWhenReady, { once: true });
    }

    // 打字效果(文字由后台配置,通过 window.JJ_TYPING 注入)
    const roles = (window.JJ_TYPING && window.JJ_TYPING.length) ? window.JJ_TYPING : ['编程', '公益', '创新', '分享'];
    let roleIndex = 0;
    let charIndex = 0;
    let isDeleting = false;
    const typedElement = document.querySelector('.typed');
    const typingDelay = 100;
    const erasingDelay = 50;
    const newTextDelay = 2000;

    function type() {
        const currentRole = roles[roleIndex];
        
        if (isDeleting) {
            typedElement.textContent = currentRole.substring(0, charIndex - 1);
            charIndex--;
        } else {
            typedElement.textContent = currentRole.substring(0, charIndex + 1);
            charIndex++;
        }

        if (!isDeleting && charIndex === currentRole.length) {
            isDeleting = true;
            setTimeout(type, newTextDelay);
        } else if (isDeleting && charIndex === 0) {
            isDeleting = false;
            roleIndex = (roleIndex + 1) % roles.length;
            setTimeout(type, typingDelay);
        } else {
            setTimeout(type, isDeleting ? erasingDelay : typingDelay);
        }
    }

    // 启动打字效果
    type();

    // 公告：可关闭 + 本地记忆
    const announcement = document.querySelector('.site-announcement');
    if (announcement) {
        const announcementId = announcement.getAttribute('data-announcement-id') || 'default';
        const storageKey = `jj_announcement_dismissed_${announcementId}`;
        const closeBtn = announcement.querySelector('.announcement-close');

        try {
            const dismissedAt = localStorage.getItem(storageKey);
            if (dismissedAt) {
                announcement.classList.add('is-hidden');
            }
        } catch (e) {
            // 忽略 localStorage 不可用的情况
        }

        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                announcement.classList.add('is-hidden');
                try {
                    localStorage.setItem(storageKey, String(Date.now()));
                } catch (e) {
                    // 忽略
                }
            });
        }
    }

    // 添加友链部分的显示逻辑
    const sections = document.querySelectorAll('.section');
    
    function showSection(sectionId) {
        sections.forEach(section => {
            section.classList.remove('active');
            if (section.id === sectionId) {
                section.classList.add('active');
            }
        });
    }

    // 如果有导航链接，添加点击事件
    document.querySelectorAll('nav a').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const targetId = link.getAttribute('href').substring(1);
            showSection(targetId);
        });
    });


}); 
