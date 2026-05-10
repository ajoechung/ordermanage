# 化工单据管理系统 - 部署指南

## 问题说明

您报告的以下接口返回404错误：
- `http://szy.ajoe.cn/api/statistics/order`
- `http://szy.ajoe.cn/api/statistics/customer`
- `http://szy.ajoe.cn/api/system/users`

## 根本原因

服务器上运行的代码不是最新版本，需要重新部署。

## 部署步骤

### 方式一：使用宝塔面板部署

1. **登录宝塔面板**
2. **打开网站根目录**
   - 找到 `szy.ajoe.cn` 或 `blog.ajoe.cn` 对应的站点
   - 点击进入网站根目录

3. **删除旧代码**
   ```bash
   # 进入后端目录
   cd backend

   # 删除所有旧文件（保留 .git 目录）
   find . -type f ! -path './.git/*' -delete
   find . -type d ! -path './.git' -empty -delete
   ```

4. **拉取最新代码**
   ```bash
   cd /www/wwwroot/chemdoc/backend
   git pull origin main
   # 或者如果是分支
   # git pull origin trae/solo-agent-5Q8scU
   ```

5. **安装Composer依赖**
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

6. **清除ThinkPHP缓存**
   ```bash
   # 清除所有缓存
   rm -rf runtime/cache/*
   rm -rf runtime/log/*
   rm -rf runtime/temp/*

   # 重新生成路由
   php think route:clear
   php think route:build
   ```

7. **设置权限**
   ```bash
   chmod -R 755 runtime/
   chmod -R 755 public/
   ```

8. **部署前端代码**
   ```bash
   # 进入前端部署目录
   cd /www/wwwroot/chemdoc/frontend-dist

   # 删除旧文件
   find . -type f ! -path './.git/*' -delete

   # 拉取最新前端代码
   git pull origin main
   ```

### 方式二：使用命令行完整部署

SSH登录到服务器，执行以下命令：

```bash
# 1. 进入后端目录
cd /www/wwwroot/chemdoc/backend

# 2. 拉取最新代码
git pull origin main

# 3. 安装依赖
composer install --no-dev --optimize-autoloader

# 4. 清除缓存
rm -rf runtime/cache/*
rm -rf runtime/log/*
rm -rf runtime/temp/*

# 5. 重新生成路由
php think route:clear
php think route:build

# 6. 设置权限
chmod -R 755 runtime/
chmod -R 755 public/

# 7. 部署前端
cd /www/wwwroot/chemdoc/frontend-dist
git pull origin main

# 8. 重启PHP-FPM
systemctl restart php-fpm
```

### 方式三：手动上传文件

如果您无法使用git，可以：

1. 在本地克隆仓库
2. 压缩 `chemdoc/backend` 目录
3. 通过宝塔面板上传并解压
4. 同样处理 `frontend-dist` 目录

## 验证部署

部署完成后，测试以下接口：

```bash
# 测试统计订单接口
curl http://szy.ajoe.cn/api/statistics/order

# 测试统计客户接口
curl http://szy.ajoe.cn/api/statistics/customer

# 测试系统用户接口
curl http://szy.ajoe.cn/api/system/users

# 测试仪表盘接口（应该已经可用）
curl http://szy.ajoe.cn/api/dashboard
```

## 注意事项

1. **确保Git分支正确**
   - 当前修复在分支：`trae/solo-agent-5Q8scU`
   - 如果需要合并到main：`git merge trae/solo-agent-5Q8scU`

2. **检查.env配置**
   - 确保数据库连接正确
   - 确保JWT密钥已配置

3. **检查Nginx配置**
   - 确保所有请求都正确路由到 `public/index.php`

4. **查看错误日志**
   ```bash
   tail -f /www/wwwroot/chemdoc/backend/runtime/log/*.log
   ```

## Git仓库信息

- 仓库地址：https://github.com/ajoechung/ordermanage
- 当前分支：`trae/solo-agent-5Q8scU`
- 已修复的接口：
  - `/api/system/users` - 系统用户列表
  - `/api/statistics/order` - 订单统计
  - `/api/statistics/customer` - 客户统计
  - `/api/log` - 操作日志列表
  - `/api/product/categories` - 产品分类列表

## 联系支持

如果部署后仍有问题，请提供：
1. 服务器错误日志
2. Nginx错误日志
3. PHP错误日志
