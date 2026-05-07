# 目录结构说明
├─ application      应用目录
├─ public          WEB 公共目录
├─ runtime         运行时目录
├─ thinkphp        ThinkPHP框架目录
├─ composer.json   Composer配置文件
├─ SPEC.md         项目规格文档
└─ README.md       项目说明文档

# 环境要求
PHP >= 5.6.0
MySQL >= 5.5.0

# 安装步骤
1. 创建数据库 `chemical_management`
2. 导入数据库脚本 `/workspace/chemical-docs/database.sql`
3. 配置数据库连接信息
4. 设置 runtime 目录可写权限

# 默认账号
- 用户名: admin
- 密码: admin

# URL访问
- 后台登录: http://your-domain/login
- 后台首页: http://your-domain/admin

# 主要功能
- 用户权限管理 (RBAC)
- 客户管理
- 联系人管理
- 供应商管理
- 产品管理
- 订单管理
- 采购单管理
- 数据统计
- 操作日志
