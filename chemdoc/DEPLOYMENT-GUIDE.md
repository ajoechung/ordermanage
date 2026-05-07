# 化工单据管理系统 - 宝塔面板部署指南

## 一、环境要求

### 服务器环境
- **操作系统**: CentOS 7+ / Ubuntu 20+ / Debian 11+
- **内存**: 最低 2GB，推荐 4GB+
- **磁盘**: 最低 20GB

### 软件版本
- **PHP**: 8.2+ (必须安装扩展: pdo_mysql, json, gd, mbstring, openssl)
- **MySQL**: 8.0+
- **Nginx**: 1.18+ / Apache 2.4+
- **Node.js**: 18+ (用于前端构建)

---

## 二、项目目录结构

```
/www/wwwroot/chemdoc/
├── backend/           # PHP后端
│   ├── app/          # 应用目录
│   ├── config/       # 配置文件
│   ├── route/       # 路由文件
│   ├── public/       # 网站入口
│   ├── vendor/       # Composer依赖
│   ├── .env          # 环境配置
│   └── think         # 命令行入口
│
├── frontend/         # 前端静态文件
│   ├── index.html
│   └── assets/
│
└── database/         # 数据库脚本
    └── init.sql
```

---

## 三、部署步骤

### 第一步：创建网站

1. 登录宝塔面板
2. 点击 **网站** → **添加站点**
3. 配置：
   - 域名：填写你的域名（如 `chemdoc.yourdomain.com`）
   - 根目录：`/www/wwwroot/chemdoc/backend/public`
   - PHP版本：选择 PHP-8.2
   - 备注：化工单据管理系统

### 第二步：上传代码

1. 将代码上传到 `/www/wwwroot/chemdoc/` 目录
2. 后端代码放在 `backend/` 目录
3. 前端代码放在 `frontend/` 目录

```bash
# 目录结构
/www/wwwroot/chemdoc/
├── backend/
└── frontend/
```

### 第三步：配置后端

1. 修改 `.env` 文件：

```env
APP_DEBUG = false

[DATABASE]
TYPE = mysql
HOSTNAME = 127.0.0.1
DATABASE = chemdoc
USERNAME = chemdoc_user
PASSWORD = your_password_here
HOSTPORT = 3306
PREFIX =

[JWT]
SECRET = your_jwt_secret_key_here
EXPIRE = 7200
```

2. 修改 `config/database.php` 确保连接配置正确

### 第四步：配置前端

前端已构建完成，直接访问即可。

修改 `frontend/assets/index-*.js` 中的 API 地址（如果需要）

### 第五步：创建数据库

1. 宝塔面板 → **数据库** → **添加数据库**
2. 配置：
   - 数据库名：`chemdoc`
   - 用户名：`chemdoc_user`
   - 密码：设置强密码
   - 编码：`utf8mb4`

3. 导入数据：
   - 选择数据库 → **导入** → **从本地导入**
   - 选择 `/www/wwwroot/chemdoc/database/init.sql`

### 第六步：配置Nginx反向代理

宝塔面板 → 网站 → 找到后端站点 → **设置** → **反向代理** → **添加反向代理**

```
代理名称：API代理
目标URL：http://127.0.0.1:8080
发送域名：$host
```

### 第七步：配置静态文件服务

新建一个站点用于前端静态文件：

1. 添加站点：
   - 域名：`static.yourdomain.com`
   - 根目录：`/www/wwwroot/chemdoc/frontend`

2. 配置Nginx处理Vue路由：

```nginx
location / {
    try_files $uri $uri/ /index.html;
}
```

### 第八步：配置ThinkPHP

1. 设置目录权限：
```bash
chmod -R 755 /www/wwwroot/chemdoc/backend
chmod -R 777 /www/wwwroot/chemdoc/backend/runtime
chmod -R 777 /www/wwwroot/chemdoc/backend/public/uploads
```

2. 安装Composer依赖（如需要）：
```bash
cd /www/wwwroot/chemdoc/backend
composer install --no-dev
```

---

## 四、启动后端服务

### 方法一：使用PHP内置服务器（仅开发环境）

```bash
cd /www/wwwroot/chemdoc/backend
php think run -H 0.0.0.0 -p 8080
```

### 方法二：使用Supervisor（推荐生产环境）

1. 宝塔面板 → **软件商店** → 搜索 **Supervisor** → 安装

2. 添加守护进程：
   - 名称：`chemdoc-api`
   - 启动用户：`www`
   - 启动命令：`php /www/wwwroot/chemdoc/backend/think run`
   - 运行目录：`/www/wwwroot/chemdoc/backend`

3. 启动服务

### 方法三：使用Systemd服务

创建 `/etc/systemd/system/chemdoc.service`：

```ini
[Unit]
Description=ChemDoc API Service
After=network.target

[Service]
Type=simple
User=www
Group=www
WorkingDirectory=/www/wwwroot/chemdoc/backend
ExecStart=/usr/bin/php /www/wwwroot/chemdoc/backend/think run -H 127.0.0.1 -p 8080
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
```

```bash
systemctl enable chemdoc
systemctl start chemdoc
```

---

## 五、Nginx配置示例

### 后端站点配置

```nginx
server {
    listen 80;
    server_name chemdoc-api.yourdomain.com;

    root /www/wwwroot/chemdoc/backend/public;
    index index.php index.html;

    # ThinkPHP隐藏入口文件
    location / {
        if (!-e $request_filename) {
            rewrite ^/(.*)$ /index.php/$1 last;
            break;
        }
    }

    # PHP配置
    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 300;
        fastcgi_buffer_size 128k;
        fastcgi_buffers 256 16k;
        fastcgi_busy_buffers_size 256k;
    }

    # 禁止访问隐藏文件
    location ~ /\.(?!well-known).* {
        deny all;
    }

    # 日志配置
    access_log /www/wwwlogs/chemdoc_access.log;
    error_log /www/wwwlogs/chemdoc_error.log;
}
```

### 前端站点配置

```nginx
server {
    listen 80;
    server_name chemdoc.yourdomain.com;

    root /www/wwwroot/chemdoc/frontend;
    index index.html;

    # Vue History模式路由支持
    location / {
        try_files $uri $uri/ /index.html;
    }

    # 静态资源缓存
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }

    # 禁止访问隐藏文件
    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Gzip压缩
    gzip on;
    gzip_vary on;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml;

    access_log /www/wwwlogs/chemdoc_frontend_access.log;
    error_log /www/wwwlogs/chemdoc_frontend_error.log;
}
```

---

## 六、SSL证书配置（可选）

1. 宝塔面板 → 网站 → 找到站点 → **设置** → **SSL**
2. 选择 **Let's Encrypt** 免费证书
3. 开启 **强制HTTPS**

---

## 七、安全配置

### 1. 修改默认密钥

编辑 `/www/wwwroot/chemdoc/backend/.env`：

```env
[JWT]
SECRET = 修改为32位以上的随机字符串
```

### 2. 关闭调试模式

```env
APP_DEBUG = false
```

### 3. 配置防火墙

```bash
# 开放必要端口
firewall-cmd --permanent --add-port=8080/tcp  # 后端API
firewall-cmd --reload
```

### 4. 定期备份

设置数据库和文件备份任务

---

## 八、初始化数据

执行完SQL后，系统会自动创建测试账号：

| 角色 | 用户名 | 密码 |
|------|--------|------|
| 管理员 | admin | 123456 |
| 销售 | sales | 123456 |
| 财务 | finance | 123456 |

**建议首次登录后立即修改密码！**

---

## 九、常见问题

### 1. 验证码无法显示

检查PHP GD扩展是否安装：
```bash
php -m | grep gd
```

### 2. API返回500错误

检查日志：
```bash
tail -f /www/wwwlogs/chemdoc_error.log
```

### 3. 验证码错误

检查缓存驱动配置：
```env
[CACHE]
TYPE = file
```

### 4. 跨域问题

确保已配置CORS中间件，后端会自动添加CORS头。

### 5. 数据库连接失败

检查 `.env` 数据库配置是否正确，确保MySQL用户权限足够。

---

## 十、后续维护

### 更新代码

1. 备份旧代码
2. 上传新代码
3. 清理缓存：`php think clear`
4. 重启服务

### 清理缓存

```bash
cd /www/wwwroot/chemdoc/backend
php think clear
```

### 查看日志

```bash
# 后端日志
tail -f runtime/log/*.log

# Nginx日志
tail -f /www/wwwlogs/chemdoc_access.log
```
