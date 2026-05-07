# 化工单据管理系统 - 宝塔部署指南

**适用环境：** Linux + 宝塔面板 + PHP 5.6 + MySQL 5.5 + Nginx 1.26.3  
**文档版本：** v1.0  
**日期：** 2026-05-07

---

## 一、环境准备

### 1.1 服务器要求
- Linux 服务器（CentOS 7/8、Ubuntu 16.04+）
- 宝塔面板 7.x 版本
- 至少 1GB 内存，2GB 以上推荐

### 1.2 宝塔软件安装

登录宝塔面板 → 软件商店 → 安装以下软件：

| 软件名称 | 版本 | 安装方式 |
|----------|------|----------|
| Nginx | 1.26.3 | 编译安装 |
| MySQL | 5.5.x | 编译安装 |
| PHP | 5.6.x | 编译安装 |
| phpMyAdmin | 5.x | 一键安装 |

### 1.3 PHP 5.6 配置

1. 进入 PHP 5.6 设置
2. **安装扩展**：确保以下扩展已启用
   - mysqli
   - pdo_mysql
   - gd
   - curl
   - zip
   - iconv
3. **修改配置**：
   - `upload_max_filesize` = 10M
   - `post_max_size` = 10M
   - `max_execution_time` = 300

---

## 二、创建网站

### 2.1 添加站点

1. 登录宝塔面板 → 网站 → 添加站点
2. 填写信息：
   - **域名**：输入您的域名（如：admin.yourdomain.com）
   - **根目录**：选择 `/www/wwwroot/chemical/public`（关键！必须指向public目录）
   - **PHP版本**：选择 PHP 5.6
   - **数据库**：MySQL 5.5，创建新数据库

### 2.2 数据库信息记录

创建数据库后，记录以下信息：
- **数据库名**：例如 `chemical_db`
- **用户名**：例如 `chemical_user`
- **密码**：您设置的密码

---

## 三、上传代码

### 3.1 上传文件

1. 通过宝塔面板 → 网站 → 找到您的站点 → 点击「文件」
2. 进入 `/www/wwwroot/` 目录
3. 上传 `chemical-management-system.zip`（整个项目压缩包）
4. 解压后将文件夹重命名为 `chemical`

### 3.2 目录结构验证

解压后目录结构应如下：
```
/www/wwwroot/chemical/
├── application/           # 应用目录
├── public/               # 公共目录（网站根目录）
├── thinkphp/             # ThinkPHP框架
├── runtime/              # 运行时目录
├── composer.json
├── README.md
└── SPEC.md
```

### 3.3 设置目录权限

1. 右键点击 `chemical` 目录 → 权限
2. 设置权限为 `www:www`
3. 确保 `runtime/` 目录有写入权限（755）

---

## 四、配置数据库连接

### 4.1 修改数据库配置

编辑 `/www/wwwroot/chemical/application/database.php`：

```php
return [
    // 数据库类型
    'type'        => 'mysql',
    // 服务器地址
    'hostname'    => '127.0.0.1',
    // 数据库名
    'database'    => 'chemical_db',  // 替换为您的数据库名
    // 用户名
    'username'    => 'chemical_user', // 替换为您的用户名
    // 密码
    'password'    => 'your_password', // 替换为您的密码
    // 端口
    'hostport'    => '3306',
    // 数据库编码
    'charset'     => 'utf8',
    // 数据库表前缀
    'prefix'      => '',
];
```

### 4.2 导入数据库

1. 登录 phpMyAdmin（宝塔面板 → 数据库 → 点击phpMyAdmin）
2. 选择您创建的数据库 `chemical_db`
3. 点击「导入」→ 选择 `/www/wwwroot/chemical/chemical-docs/database.sql`
4. 点击「执行」完成导入

---

## 五、配置 Nginx

### 5.1 配置文件

进入宝塔面板 → 网站 → 找到您的站点 → 点击「设置」→「配置文件」

添加以下配置（替换原有内容）：

```nginx
server {
    listen 80;
    server_name admin.yourdomain.com;  # 替换为您的域名
    root /www/wwwroot/chemical/public;
    index index.php index.html;
    
    # URL重写（关键！ThinkPHP伪静态）
    location / {
        if (!-e $request_filename) {
            rewrite ^/(.*)$ /index.php?s=$1 last;
            break;
        }
    }
    
    # PHP配置
    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    # 禁止访问敏感文件
    location ~ /\.ht {
        deny all;
    }
    
    # 日志配置
    access_log /www/wwwlogs/chemical_access.log;
    error_log /www/wwwlogs/chemical_error.log;
}
```

### 5.2 启用 HTTPS（可选但推荐）

1. 进入宝塔面板 → 网站 → 找到您的站点 → 点击「SSL」
2. 选择「Let's Encrypt」
3. 勾选「强制HTTPS」
4. 点击「申请」

---

## 六、测试访问

### 6.1 访问系统

打开浏览器，访问您的域名：
```
http://admin.yourdomain.com
```

### 6.2 登录验证

- **用户名**：admin
- **密码**：admin

### 6.3 功能测试

1. 登录系统后，检查仪表盘是否正常显示
2. 测试客户管理、订单管理等功能
3. 测试文件上传功能

---

## 七、常见问题排查

### 7.1 500 错误

**原因**：PHP错误或权限问题

**解决方法**：
1. 检查 `runtime/` 目录权限（确保755）
2. 查看错误日志：`/www/wwwlogs/chemical_error.log`
3. 确保 PHP 5.6 扩展已正确安装

### 7.2 404 错误

**原因**：伪静态配置问题

**解决方法**：
1. 检查 Nginx 配置中的 rewrite 规则
2. 确保网站根目录指向 `public/` 目录

### 7.3 数据库连接失败

**原因**：数据库配置错误

**解决方法**：
1. 检查 `application/database.php` 配置
2. 确保数据库用户名、密码正确
3. 确保 MySQL 服务正常运行

### 7.4 页面样式错乱

**原因**：静态资源路径问题

**解决方法**：
1. 检查 `public/admin/css/` 和 `public/admin/js/` 目录是否存在
2. 清除浏览器缓存后重试

---

## 八、安全建议

1. **修改默认密码**：登录后立即修改 admin 用户密码
2. **限制后台访问**：在 Nginx 配置中添加 IP 白名单
3. **定期备份**：宝塔面板 → 计划任务 → 添加数据库和网站备份
4. **更新系统**：定期更新宝塔面板和服务器系统

---

## 九、部署成功验证清单

✅ 环境安装完成（PHP 5.6 + MySQL 5.5 + Nginx 1.26.3）  
✅ 网站创建并配置完成  
✅ 代码上传并解压完成  
✅ 数据库配置并导入完成  
✅ Nginx 伪静态配置完成  
✅ 系统可正常访问  
✅ 默认账号可登录  
✅ 核心功能测试通过  

---

## 十、技术支持

如遇问题，请检查：
1. 宝塔面板错误日志
2. Nginx 错误日志
3. PHP 错误日志

---

**部署完成！** 系统已准备就绪，可以开始使用化工单据管理系统。
