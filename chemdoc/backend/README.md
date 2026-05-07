# 化工单据管理系统 - ThinkPHP 8.1 后端API

## 项目简介

本项目是化工单据管理系统的后端API服务，基于ThinkPHP 8.1框架开发，提供RESTful风格的API接口。

## 技术栈

- **PHP**: 8.2+
- **框架**: ThinkPHP 8.1
- **数据库**: MySQL 8.0
- **认证**: JWT

## 目录结构

```
backend/
├── app/
│   ├── controller/api/     # API控制器
│   ├── model/              # 数据模型
│   ├── service/             # 业务逻辑
│   ├── middleware/         # 中间件
│   ├── validate/            # 验证器
│   └── exception/           # 异常处理
├── config/                  # 配置文件
├── public/                  # 入口文件
├── route/                   # 路由配置
├── .env                     # 环境配置
└── composer.json           # 依赖配置
```

## 环境要求

- PHP 8.1 或更高版本
- MySQL 8.0
- Composer

## 安装步骤

```bash
# 1. 进入后端目录
cd backend

# 2. 安装依赖
composer install

# 3. 配置环境变量
cp .env.example .env
# 编辑 .env 文件，配置数据库连接

# 4. 导入数据库
mysql -u root -p < ../database/init.sql

# 5. 启动开发服务器
php think run
```

## 默认账号

- 用户名: admin
- 密码: 123456

## API接口文档

### 认证接口

| 接口 | 方法 | 说明 | 鉴权 |
|------|------|------|------|
| /api/login | POST | 用户登录 | 否 |
| /api/logout | POST | 用户退出 | 是 |
| /api/login/userInfo | GET | 获取用户信息 | 是 |

### 仪表盘接口

| 接口 | 方法 | 说明 | 鉴权 |
|------|------|------|------|
| /api/dashboard | GET | 获取仪表盘数据 | 是 |

### 客户管理接口

| 接口 | 方法 | 说明 | 鉴权 |
|------|------|------|------|
| /api/customer | GET | 获取客户列表 | 是 |
| /api/customer/:id | GET | 获取客户详情 | 是 |
| /api/customer | POST | 新增客户 | 是 |
| /api/customer/:id | PUT | 编辑客户 | 是 |
| /api/customer/:id | DELETE | 删除客户 | 是 |

### 联系人管理接口

| 接口 | 方法 | 说明 | 鉴权 |
|------|------|------|------|
| /api/contact | GET | 获取联系人列表 | 是 |
| /api/contact/:id | GET | 获取联系人详情 | 是 |
| /api/contact | POST | 新增联系人 | 是 |
| /api/contact/:id | PUT | 编辑联系人 | 是 |
| /api/contact/:id | DELETE | 删除联系人 | 是 |

### 订单管理接口

| 接口 | 方法 | 说明 | 鉴权 |
|------|------|------|------|
| /api/order | GET | 获取订单列表 | 是 |
| /api/order/:id | GET | 获取订单详情 | 是 |
| /api/order | POST | 新增订单 | 是 |
| /api/order/:id | PUT | 编辑订单 | 是 |
| /api/order/:id | DELETE | 删除订单 | 是 |
| /api/order/status | POST | 更新订单状态 | 是 |

### 供应商管理接口

| 接口 | 方法 | 说明 | 鉴权 |
|------|------|------|------|
| /api/supplier | GET | 获取供应商列表 | 是 |
| /api/supplier/:id | GET | 获取供应商详情 | 是 |
| /api/supplier | POST | 新增供应商 | 是 |
| /api/supplier/:id | PUT | 编辑供应商 | 是 |
| /api/supplier/:id | DELETE | 删除供应商 | 是 |

### 产品管理接口

| 接口 | 方法 | 说明 | 鉴权 |
|------|------|------|------|
| /api/product | GET | 获取产品列表 | 是 |
| /api/product/:id | GET | 获取产品详情 | 是 |
| /api/product | POST | 新增产品 | 是 |
| /api/product/:id | PUT | 编辑产品 | 是 |
| /api/product/:id | DELETE | 删除产品 | 是 |
| /api/product/categories | GET | 获取产品分类 | 是 |
| /api/product/category | POST | 新增分类 | 是 |
| /api/product/category/:id | PUT | 编辑分类 | 是 |
| /api/product/category/:id | DELETE | 删除分类 | 是 |

### 跟进记录接口

| 接口 | 方法 | 说明 | 鉴权 |
|------|------|------|------|
| /api/follow | GET | 获取跟进记录列表 | 是 |
| /api/follow | POST | 新增跟进记录 | 是 |
| /api/follow/:id | DELETE | 删除跟进记录 | 是 |

### 统计接口

| 接口 | 方法 | 说明 | 鉴权 |
|------|------|------|------|
| /api/statistics/customer | GET | 客户统计 | 是 |
| /api/statistics/order | GET | 订单统计 | 是 |

### 系统管理接口

| 接口 | 方法 | 说明 | 鉴权 |
|------|------|------|------|
| /api/system/users | GET | 用户列表 | 是 |
| /api/system/user | POST | 创建用户 | 是 |
| /api/system/user/:id | PUT | 编辑用户 | 是 |
| /api/system/user/:id | DELETE | 删除用户 | 是 |
| /api/system/groups | GET | 角色列表 | 是 |
| /api/system/group | POST | 创建角色 | 是 |
| /api/system/group/:id | PUT | 编辑角色 | 是 |
| /api/system/group/:id | DELETE | 删除角色 | 是 |
| /api/system/rules | GET | 权限列表 | 是 |

### 文件上传接口

| 接口 | 方法 | 说明 | 鉴权 |
|------|------|------|------|
| /api/upload/image | POST | 上传图片 | 是 |
| /api/upload/file | POST | 上传文件 | 是 |
| /api/upload/delete | POST | 删除文件 | 是 |

### 操作日志接口

| 接口 | 方法 | 说明 | 鉴权 |
|------|------|------|------|
| /api/log | GET | 日志列表 | 是 |

## 接口调用示例

### 1. 用户登录

```bash
curl -X POST http://localhost:8080/api/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"123456"}'
```

响应示例：
```json
{
    "code": 1,
    "msg": "登录成功",
    "data": {
        "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
        "user_info": {
            "user_id": 1,
            "username": "admin",
            "realname": "系统管理员",
            "groups": [1]
        }
    },
    "time": 1707123456
}
```

### 2. 获取客户列表

```bash
curl -X GET "http://localhost:8080/api/customer?page=1&page_size=20" \
  -H "Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
```

响应示例：
```json
{
    "code": 1,
    "msg": "操作成功",
    "data": {
        "total": 128,
        "list": [
            {
                "customer_id": 1,
                "name": "上海化工贸易有限公司",
                "industry": "石油化工",
                "owner_name": "张三",
                "level_text": "核心客户",
                "status_text": "正常"
            }
        ],
        "page": 1,
        "page_size": 20,
        "total_pages": 7
    },
    "time": 1707123456
}
```

### 3. 新增客户

```bash
curl -X POST http://localhost:8080/api/customer \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..." \
  -d '{
      "name": "测试客户",
      "industry": "精细化工",
      "owner_user_id": 2,
      "level": 2
  }'
```

## 错误码说明

| 错误码 | 说明 |
|--------|------|
| 1 | 成功 |
| 0 | 失败 |
| 401 | 未授权 |
| 403 | 无权限访问 |
| 404 | 资源不存在 |
| 422 | 参数验证失败 |
| 500 | 服务器错误 |

## 许可证

Apache-2.0
