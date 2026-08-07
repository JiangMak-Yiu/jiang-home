# 江江.com — 个人导航站

🎉 江江个人导航站源码。**PHP 8.2 + SQLite,零配置,开箱即用**。

原站为纯静态 HTML,现已全部数据动态化:前台展示、后台管理、友链申请审核,一个文件不装依赖即可跑起来。

## ✨ 功能特性

- 🌐 **前台**:Hero 区(头像/打字机文字/角色标签)、常用入口、站点索引(分组)、友链墙、公告弹窗、波浪页脚
- 🔐 **后台管理**(`/admin/`):
  - 📊 仪表盘(统计概览)
  - 🧭 导航管理(常用入口 + 站点索引增删改)
  - 🔗 友链管理(增删改,支持封面图)
  - 📝 友链申请(前台提交 → 后台审核)
  - ⚙️ 站点设置(Hero 文案、公告、QQ/TG、ICP 备案等)
  - 💾 数据备份(一键导出 SQLite 数据)
  - 🔑 修改密码
- 🛡️ **安全**:CSRF 防护、登录失败 5 次锁 15 分钟、会话 30 分钟超时、友链申请蜜罐 + 60 秒限频

## 🚀 部署

**环境要求**:PHP 8.2+(`pdo_sqlite`、`curl`、`mbstring` 扩展),Nginx/Apache 均可。

```bash
# 1. 克隆代码到站点目录
git clone https://github.com/JiangMak-Yiu/jiangjiang-site.git /www/wwwroot/江江.com

# 2. 确保 data/ 目录可写
mkdir -p data && chown -R www:www data

# 3. 完成!访问首页
# 首次访问自动创建 SQLite 数据库,并生成后台初始密码:
#   后台地址: https://你的域名/admin/
#   初始密码: data/初始密码.txt(登录后请立即修改)
```

> Nginx 站点请配置 PHP 解析(`location ~ \.php$` 转发到 php-fpm);宝塔面板直接在站点设置里选择 PHP 版本即可。

## 📁 目录结构

```
├── index.php          # 前台首页(数据全部来自数据库)
├── apply.php          # 友链申请页
├── 404.html
├── assets/
│   ├── style.css      # 前台样式(原站设计保留)
│   ├── admin.css      # 后台样式
│   └── fonts/
├── includes/
│   ├── db.php         # SQLite 连接与建表
│   └── functions.php  # 公共函数(设置/CSRF/登录锁等)
├── admin/             # 管理后台
│   ├── index.php      # 登录
│   ├── dashboard.php  # 仪表盘
│   ├── nav.php        # 导航管理
│   ├── links.php      # 友链管理
│   ├── requests.php   # 友链申请审核
│   ├── settings.php   # 站点设置
│   ├── backup.php     # 数据备份
│   └── password.php   # 修改密码
└── data/              # SQLite 数据(已 gitignore,部署时自动生成)
```

## 🔒 安全说明

- `data/` 目录存放数据库与初始密码文件,**请确保该目录不被 Web 直接访问**(Nginx 下默认隐藏 `.` 开头文件,`data/` 建议在站点配置中单独禁止访问)
- 后台所有写操作均有 CSRF 校验;登录有防暴力破解锁定
- 初始密码随机生成,登录后请立即修改

## 📄 License

[MIT](LICENSE)
