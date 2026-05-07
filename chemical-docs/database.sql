-- 化工单据管理系统数据库脚本
-- MySQL 5.5+
-- 执行方式: mysql -u root -p < database.sql

-- 创建数据库
CREATE DATABASE IF NOT EXISTS chemical_management DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE chemical_management;

-- ----------------------------
-- 1. 管理员用户表
-- ----------------------------
DROP TABLE IF EXISTS `admin_user`;
CREATE TABLE `admin_user` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `username` VARCHAR(50) NOT NULL COMMENT '用户名',
  `password` VARCHAR(255) NOT NULL COMMENT '密码',
  `realname` VARCHAR(50) NOT NULL COMMENT '真实姓名',
  `phone` VARCHAR(20) DEFAULT NULL COMMENT '手机号码',
  `email` VARCHAR(100) DEFAULT NULL COMMENT '邮箱',
  `status` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '状态：1-启用 0-禁用',
  `last_login_time` DATETIME DEFAULT NULL COMMENT '最后登录时间',
  `last_login_ip` VARCHAR(50) DEFAULT NULL COMMENT '最后登录IP',
  `create_time` DATETIME NOT NULL COMMENT '创建时间',
  `update_time` DATETIME NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='管理员用户表';

-- ----------------------------
-- 2. 角色表
-- ----------------------------
DROP TABLE IF EXISTS `admin_role`;
CREATE TABLE `admin_role` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '角色ID',
  `role_name` VARCHAR(50) NOT NULL COMMENT '角色名称',
  `role_code` VARCHAR(50) NOT NULL COMMENT '角色标识',
  `description` TEXT COMMENT '角色描述',
  `create_time` DATETIME NOT NULL COMMENT '创建时间',
  `update_time` DATETIME NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_role_code` (`role_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='角色表';

-- ----------------------------
-- 3. 权限表
-- ----------------------------
DROP TABLE IF EXISTS `admin_auth`;
CREATE TABLE `admin_auth` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '权限ID',
  `auth_name` VARCHAR(50) NOT NULL COMMENT '权限名称',
  `auth_code` VARCHAR(100) NOT NULL COMMENT '权限标识',
  `parent_id` INT UNSIGNED DEFAULT 0 COMMENT '上级权限ID',
  `auth_type` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '权限类型：1-目录 2-控制器 3-方法',
  `sort` INT NOT NULL DEFAULT 0 COMMENT '排序',
  `is_show` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '是否显示：1-显示 0-隐藏',
  `create_time` DATETIME NOT NULL COMMENT '创建时间',
  `update_time` DATETIME NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_auth_code` (`auth_code`),
  KEY `idx_parent_id` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='权限表';

-- ----------------------------
-- 4. 用户角色关联表
-- ----------------------------
DROP TABLE IF EXISTS `admin_user_role`;
CREATE TABLE `admin_user_role` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` INT UNSIGNED NOT NULL COMMENT '用户ID',
  `role_id` INT UNSIGNED NOT NULL COMMENT '角色ID',
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_role_id` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户角色关联表';

-- ----------------------------
-- 5. 角色权限关联表
-- ----------------------------
DROP TABLE IF EXISTS `admin_role_auth`;
CREATE TABLE `admin_role_auth` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `role_id` INT UNSIGNED NOT NULL COMMENT '角色ID',
  `auth_id` INT UNSIGNED NOT NULL COMMENT '权限ID',
  PRIMARY KEY (`id`),
  KEY `idx_role_id` (`role_id`),
  KEY `idx_auth_id` (`auth_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='角色权限关联表';

-- ----------------------------
-- 6. 产品分类表
-- ----------------------------
DROP TABLE IF EXISTS `product_category`;
CREATE TABLE `product_category` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '分类ID',
  `name` VARCHAR(100) NOT NULL COMMENT '分类名称',
  `parent_id` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '上级分类ID',
  `sort` INT NOT NULL DEFAULT 0 COMMENT '排序',
  `is_show` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '是否显示：1-显示 0-隐藏',
  `create_time` DATETIME NOT NULL COMMENT '创建时间',
  `update_time` DATETIME NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_parent_id` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='产品分类表';

-- ----------------------------
-- 7. 产品信息表
-- ----------------------------
DROP TABLE IF EXISTS `product`;
CREATE TABLE `product` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '产品ID',
  `name` VARCHAR(100) NOT NULL COMMENT '产品名称',
  `category_id` INT UNSIGNED NOT NULL COMMENT '所属分类ID',
  `description` TEXT COMMENT '产品描述',
  `spec` VARCHAR(255) DEFAULT NULL COMMENT '产品规格',
  `price` DECIMAL(10,2) DEFAULT NULL COMMENT '产品单价',
  `status` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '状态：1-启用 0-禁用',
  `attachment` VARCHAR(255) DEFAULT NULL COMMENT '附件',
  `create_time` DATETIME NOT NULL COMMENT '创建时间',
  `update_time` DATETIME NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_category_id` (`category_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='产品信息表';

-- ----------------------------
-- 8. 客户表
-- ----------------------------
DROP TABLE IF EXISTS `customer`;
CREATE TABLE `customer` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '客户ID',
  `customer_name` VARCHAR(100) NOT NULL COMMENT '客户名称',
  `industry` VARCHAR(50) DEFAULT NULL COMMENT '行业',
  `source` VARCHAR(50) DEFAULT NULL COMMENT '来源',
  `user_id` INT UNSIGNED NOT NULL COMMENT '负责人/所属业务员ID',
  `address` VARCHAR(255) DEFAULT NULL COMMENT '地址',
  `description` TEXT COMMENT '客户描述',
  `phone` VARCHAR(20) DEFAULT NULL COMMENT '联系电话',
  `email` VARCHAR(100) DEFAULT NULL COMMENT '邮箱',
  `status` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '状态：1-正常 2-潜在 3-流失',
  `attachment` VARCHAR(255) DEFAULT NULL COMMENT '附件',
  `create_time` DATETIME NOT NULL COMMENT '创建时间',
  `update_time` DATETIME NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_create_time` (`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='客户表';

-- ----------------------------
-- 9. 联系人表
-- ----------------------------
DROP TABLE IF EXISTS `linkman`;
CREATE TABLE `linkman` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '联系人ID',
  `name` VARCHAR(50) NOT NULL COMMENT '联系人姓名',
  `phone` VARCHAR(20) NOT NULL COMMENT '联系人电话',
  `position` VARCHAR(50) DEFAULT NULL COMMENT '职位',
  `customer_id` INT UNSIGNED NOT NULL COMMENT '所属客户ID',
  `email` VARCHAR(100) DEFAULT NULL COMMENT '邮箱',
  `remark` TEXT COMMENT '备注',
  `status` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '状态：1-正常 2-离职',
  `create_time` DATETIME NOT NULL COMMENT '创建时间',
  `update_time` DATETIME NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_customer_id` (`customer_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='联系人表';

-- ----------------------------
-- 10. 供应商表
-- ----------------------------
DROP TABLE IF EXISTS `supplier`;
CREATE TABLE `supplier` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '供应商ID',
  `name` VARCHAR(100) NOT NULL COMMENT '供应商名称',
  `contact` VARCHAR(50) DEFAULT NULL COMMENT '联系人',
  `phone` VARCHAR(20) DEFAULT NULL COMMENT '联系电话',
  `address` VARCHAR(255) DEFAULT NULL COMMENT '地址',
  `description` TEXT COMMENT '描述',
  `user_id` INT UNSIGNED DEFAULT NULL COMMENT '所属业务员ID',
  `status` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '状态：1-正常 2-合作中 3-已终止',
  `attachment` VARCHAR(255) DEFAULT NULL COMMENT '附件',
  `create_time` DATETIME NOT NULL COMMENT '创建时间',
  `update_time` DATETIME NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='供应商表';

-- ----------------------------
-- 11. 订单表
-- ----------------------------
DROP TABLE IF EXISTS `order`;
CREATE TABLE `orders` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '订单ID',
  `order_no` VARCHAR(50) NOT NULL COMMENT '订单编号',
  `customer_id` INT UNSIGNED NOT NULL COMMENT '客户ID',
  `amount` DECIMAL(10,2) NOT NULL COMMENT '订单金额',
  `order_time` DATETIME NOT NULL COMMENT '下单时间',
  `fulfill_status` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '履约状态：1-待确认 2-已确认 3-生产中 4-已发货 5-已完成 6-已取消',
  `invoice_status` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '发票状态：1-未开 2-已申请 3-已开具 4-已寄出',
  `invoice_file` VARCHAR(255) DEFAULT NULL COMMENT '发票附件',
  `purchase_id` INT UNSIGNED DEFAULT NULL COMMENT '关联采购单ID',
  `remark` TEXT COMMENT '备注',
  `user_id` INT UNSIGNED NOT NULL COMMENT '创建人ID',
  `create_time` DATETIME NOT NULL COMMENT '创建时间',
  `update_time` DATETIME NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_order_no` (`order_no`),
  KEY `idx_customer_id` (`customer_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_purchase_id` (`purchase_id`),
  KEY `idx_fulfill_status` (`fulfill_status`),
  KEY `idx_order_time` (`order_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='订单表';

-- ----------------------------
-- 12. 订单明细表
-- ----------------------------
DROP TABLE IF EXISTS `order_item`;
CREATE TABLE `order_item` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '明细ID',
  `order_id` INT UNSIGNED NOT NULL COMMENT '订单ID',
  `product_id` INT UNSIGNED NOT NULL COMMENT '产品ID',
  `product_name` VARCHAR(100) NOT NULL COMMENT '产品名称（冗余存储）',
  `quantity` DECIMAL(10,2) NOT NULL COMMENT '数量',
  `price` DECIMAL(10,2) NOT NULL COMMENT '单价',
  `amount` DECIMAL(10,2) NOT NULL COMMENT '金额',
  `remark` VARCHAR(255) DEFAULT NULL COMMENT '备注',
  `create_time` DATETIME NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_order_id` (`order_id`),
  KEY `idx_product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='订单明细表';

-- ----------------------------
-- 13. 采购单表
-- ----------------------------
DROP TABLE IF EXISTS `purchase`;
CREATE TABLE `purchase` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '采购单ID',
  `purchase_no` VARCHAR(50) NOT NULL COMMENT '采购单号',
  `supplier_id` INT UNSIGNED NOT NULL COMMENT '供应商ID',
  `amount` DECIMAL(10,2) NOT NULL COMMENT '采购金额',
  `purchase_time` DATETIME NOT NULL COMMENT '下单时间',
  `fulfill_status` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '履约状态：1-待确认 2-已确认 3-采购中 4-已到货 5-已完成 6-已取消',
  `invoice_status` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '发票状态：1-未开 2-已申请 3-已开具 4-已寄出',
  `invoice_file` VARCHAR(255) DEFAULT NULL COMMENT '发票附件',
  `order_id` INT UNSIGNED DEFAULT NULL COMMENT '关联订单ID',
  `remark` TEXT COMMENT '备注',
  `user_id` INT UNSIGNED NOT NULL COMMENT '创建人ID',
  `create_time` DATETIME NOT NULL COMMENT '创建时间',
  `update_time` DATETIME NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_purchase_no` (`purchase_no`),
  KEY `idx_supplier_id` (`supplier_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_order_id` (`order_id`),
  KEY `idx_fulfill_status` (`fulfill_status`),
  KEY `idx_purchase_time` (`purchase_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='采购单表';

-- ----------------------------
-- 14. 采购明细表
-- ----------------------------
DROP TABLE IF EXISTS `purchase_item`;
CREATE TABLE `purchase_item` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '明细ID',
  `purchase_id` INT UNSIGNED NOT NULL COMMENT '采购单ID',
  `product_id` INT UNSIGNED NOT NULL COMMENT '产品ID',
  `product_name` VARCHAR(100) NOT NULL COMMENT '产品名称（冗余存储）',
  `quantity` DECIMAL(10,2) NOT NULL COMMENT '数量',
  `price` DECIMAL(10,2) NOT NULL COMMENT '单价',
  `amount` DECIMAL(10,2) NOT NULL COMMENT '金额',
  `remark` VARCHAR(255) DEFAULT NULL COMMENT '备注',
  `create_time` DATETIME NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_purchase_id` (`purchase_id`),
  KEY `idx_product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='采购明细表';

-- ----------------------------
-- 15. 客户跟进记录表
-- ----------------------------
DROP TABLE IF EXISTS `customer_follow`;
CREATE TABLE `customer_follow` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '跟进ID',
  `customer_id` INT UNSIGNED NOT NULL COMMENT '客户ID',
  `user_id` INT UNSIGNED NOT NULL COMMENT '跟进人ID',
  `follow_type` VARCHAR(50) DEFAULT NULL COMMENT '跟进方式',
  `follow_content` TEXT NOT NULL COMMENT '跟进内容',
  `follow_time` DATETIME NOT NULL COMMENT '跟进时间',
  `next_follow_time` DATETIME DEFAULT NULL COMMENT '下次跟进时间',
  `create_time` DATETIME NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_customer_id` (`customer_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_follow_time` (`follow_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='客户跟进记录表';

-- ----------------------------
-- 16. 操作日志表
-- ----------------------------
DROP TABLE IF EXISTS `operation_log`;
CREATE TABLE `operation_log` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '日志ID',
  `user_id` INT UNSIGNED NOT NULL COMMENT '操作人ID',
  `username` VARCHAR(50) NOT NULL COMMENT '操作人用户名',
  `module` VARCHAR(50) NOT NULL COMMENT '操作模块',
  `action` VARCHAR(50) NOT NULL COMMENT '操作内容',
  `action_type` VARCHAR(20) NOT NULL COMMENT '操作类型：新增/编辑/删除/查询',
  `ip_address` VARCHAR(50) DEFAULT NULL COMMENT 'IP地址',
  `user_agent` VARCHAR(255) DEFAULT NULL COMMENT '浏览器信息',
  `request_params` TEXT COMMENT '请求参数',
  `create_time` DATETIME NOT NULL COMMENT '操作时间',
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_module` (`module`),
  KEY `idx_create_time` (`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='操作日志表';

-- ----------------------------
-- 17. 系统配置表
-- ----------------------------
DROP TABLE IF EXISTS `system_config`;
CREATE TABLE `system_config` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '配置ID',
  `config_key` VARCHAR(100) NOT NULL COMMENT '配置键',
  `config_value` TEXT COMMENT '配置值',
  `config_name` VARCHAR(100) DEFAULT NULL COMMENT '配置名称',
  `config_type` VARCHAR(50) DEFAULT NULL COMMENT '配置类型',
  `description` VARCHAR(255) DEFAULT NULL COMMENT '描述',
  `create_time` DATETIME NOT NULL COMMENT '创建时间',
  `update_time` DATETIME NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_config_key` (`config_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='系统配置表';

-- ========================================
-- 初始化数据
-- ========================================

-- 管理员用户（密码：admin，MD5加密）
INSERT INTO `admin_user` (`username`, `password`, `realname`, `phone`, `email`, `status`, `create_time`, `update_time`) VALUES
('admin', '21232f297a57a5a743894a0e4a801fc3', '系统管理员', '13800138000', 'admin@example.com', 1, NOW(), NOW()),
('sales01', '21232f297a57a5a743894a0e4a801fc3', '张三', '13800138001', 'sales01@example.com', 1, NOW(), NOW()),
('sales02', '21232f297a57a5a743894a0e4a801fc3', '李四', '13800138002', 'sales02@example.com', 1, NOW(), NOW()),
('purchase01', '21232f297a57a5a743894a0e4a801fc3', '王五', '13800138003', 'purchase01@example.com', 1, NOW(), NOW());

-- 角色
INSERT INTO `admin_role` (`role_name`, `role_code`, `description`, `create_time`, `update_time`) VALUES
('系统管理员', 'admin', '系统最高权限管理者', NOW(), NOW()),
('销售人员', 'sales', '负责客户开发和订单管理', NOW(), NOW()),
('采购人员', 'purchase', '负责供应商和采购单管理', NOW(), NOW());

-- 用户角色关联
INSERT INTO `admin_user_role` (`user_id`, `role_id`) VALUES
(1, 1),  -- admin -> 系统管理员
(2, 2),  -- sales01 -> 销售人员
(3, 2),  -- sales02 -> 销售人员
(4, 3);  -- purchase01 -> 采购人员

-- 权限数据
INSERT INTO `admin_auth` (`auth_name`, `auth_code`, `parent_id`, `auth_type`, `sort`, `is_show`, `create_time`, `update_time`) VALUES
-- 系统管理（顶级目录）
('系统管理', 'system', 0, 1, 100, 1, NOW(), NOW()),
('首页', 'index', 0, 1, 1, 1, NOW(), NOW()),
('仪表盘', 'index/index', 2, 2, 1, 1, NOW(), NOW()),
('产品管理', 'product', 0, 1, 2, 1, NOW(), NOW()),
('产品分类', 'product/category', 4, 2, 1, 1, NOW(), NOW()),
('产品信息', 'product/index', 4, 2, 2, 1, NOW(), NOW()),
('客户管理', 'customer', 0, 1, 3, 1, NOW(), NOW()),
('客户列表', 'customer/index', 7, 2, 1, 1, NOW(), NOW()),
('联系人列表', 'customer/linkman', 7, 2, 2, 1, NOW(), NOW()),
('客户跟进', 'customer/follow', 7, 2, 3, 1, NOW(), NOW()),
('供应商管理', 'supplier', 0, 1, 4, 1, NOW(), NOW()),
('供应商列表', 'supplier/index', 10, 2, 1, 1, NOW(), NOW()),
('订单管理', 'order', 0, 1, 5, 1, NOW(), NOW()),
('订单列表', 'order/index', 12, 2, 1, 1, NOW(), NOW()),
('采购单管理', 'purchase', 0, 1, 6, 1, NOW(), NOW()),
('采购单列表', 'purchase/index', 14, 2, 1, 1, NOW(), NOW()),
('数据统计', 'statistics', 0, 1, 7, 1, NOW(), NOW()),
('客户统计', 'statistics/customer', 16, 2, 1, 1, NOW(), NOW()),
('订单统计', 'statistics/order', 16, 2, 2, 1, NOW(), NOW()),
('操作日志', 'log', 0, 1, 8, 1, NOW(), NOW()),
('日志列表', 'log/index', 19, 2, 1, 1, NOW(), NOW()),
('用户管理', 'system/user', 1, 2, 1, 1, NOW(), NOW()),
('角色管理', 'system/role', 1, 2, 2, 1, NOW(), NOW()),
('权限配置', 'system/auth', 1, 2, 3, 1, NOW(), NOW());

-- 管理员角色拥有所有权限
INSERT INTO `admin_role_auth` (`role_id`, `auth_id`)
SELECT 1, id FROM `admin_auth`;

-- 销售角色权限（产品、客户、订单、统计）
INSERT INTO `admin_role_auth` (`role_id`, `auth_id`) VALUES
(2, 2), (2, 3), (2, 4), (2, 5), (2, 6), (2, 7), (2, 8), (2, 9), (2, 10), (2, 12), (2, 13), (2, 14), (2, 16), (2, 17), (2, 18), (2, 19), (2, 20);

-- 采购角色权限（产品、供应商、采购单、统计）
INSERT INTO `admin_role_auth` (`role_id`, `auth_id`) VALUES
(3, 2), (3, 3), (3, 4), (3, 5), (3, 6), (3, 10), (3, 11), (3, 14), (3, 15), (3, 16), (3, 17), (3, 18), (3, 19), (3, 20);

-- 产品分类示例数据
INSERT INTO `product_category` (`name`, `parent_id`, `sort`, `is_show`, `create_time`, `update_time`) VALUES
('化工原料', 0, 1, 1, NOW(), NOW()),
('有机化工', 1, 1, 1, NOW(), NOW()),
('甲醇系列', 2, 1, 1, NOW(), NOW()),
('乙醇系列', 2, 2, 1, NOW(), NOW()),
('无机化工', 1, 2, 1, NOW(), NOW()),
('硫酸类', 5, 1, 1, NOW(), NOW()),
('盐酸类', 5, 2, 1, NOW(), NOW()),
('塑料制品', 0, 2, 1, NOW(), NOW()),
('塑料颗粒', 8, 1, 1, NOW(), NOW()),
('塑料制品', 8, 2, 1, NOW(), NOW());

-- 产品示例数据
INSERT INTO `product` (`name`, `category_id`, `description`, `spec`, `price`, `status`, `create_time`, `update_time`) VALUES
('甲醇 AR级', 3, '分析纯甲醇，用于实验室和工业', '500ml/瓶', 35.00, 1, NOW(), NOW()),
('甲醇 GR级', 3, '优级纯甲醇，高纯度要求', '500ml/瓶', 58.00, 1, NOW(), NOW()),
('乙醇 AR级', 4, '分析纯乙醇', '500ml/瓶', 32.00, 1, NOW(), NOW()),
('乙醇 GR级', 4, '优级纯乙醇', '500ml/瓶', 55.00, 1, NOW(), NOW()),
('硫酸 CP级', 6, '化学纯硫酸', '500ml/瓶', 28.00, 1, NOW(), NOW()),
('盐酸 AR级', 7, '分析纯盐酸', '500ml/瓶', 25.00, 1, NOW(), NOW()),
('PVC颗粒', 9, '聚氯乙烯颗粒', '25kg/袋', 12.50, 1, NOW(), NOW()),
('PE颗粒', 9, '聚乙烯颗粒', '25kg/袋', 15.80, 1, NOW(), NOW());

-- 客户示例数据
INSERT INTO `customer` (`customer_name`, `industry`, `source`, `user_id`, `address`, `description`, `phone`, `email`, `status`, `create_time`, `update_time`) VALUES
('华东化工有限公司', '化工', '网络推广', 2, '上海市浦东新区化工路88号', '大型化工企业客户', '021-12345678', 'huadong@email.com', 1, NOW(), NOW()),
('华南塑料制品厂', '塑料', '老客户介绍', 2, '广州市天河区工业大道120号', '塑料制品生产企业', '020-87654321', 'huanan@email.com', 1, NOW(), NOW()),
('北方材料科技有限公司', '材料', '展会', 3, '北京市朝阳区科技园区', '新材料研发企业', '010-65432100', 'beifang@email.com', 1, NOW(), NOW()),
('华东制药有限公司', '制药', '网络推广', 2, '上海市静安区医药路200号', '制药企业', '021-33334444', 'huadong_pharma@email.com', 2, NOW(), NOW());

-- 联系人示例数据
INSERT INTO `linkman` (`name`, `phone`, `position`, `customer_id`, `email`, `remark`, `status`, `create_time`, `update_time`) VALUES
('张采购', '13812340001', '采购经理', 1, 'zhangcg@huadong.com', '负责化工原料采购', 1, NOW(), NOW()),
('李技术', '13812340002', '技术总监', 1, 'lijs@huadong.com', '技术对接人', 1, NOW(), NOW()),
('王经理', '13812340003', '厂长', 2, 'wangjl@huanan.com', '全面负责人', 1, NOW(), NOW()),
('赵采购', '13812340004', '采购主管', 3, 'zhaocg@beifang.com', '采购负责人', 1, NOW(), NOW());

-- 供应商示例数据
INSERT INTO `supplier` (`name`, `contact`, `phone`, `address`, `description`, `user_id`, `status`, `create_time`, `update_time`) VALUES
('江苏化工原料厂', '刘经理', '13912340001', '江苏省南京市化工园区', '专业化工原料供应商', 4, 2, NOW(), NOW()),
('浙江塑料原料公司', '陈经理', '13912340002', '浙江省杭州市工业区', '塑料原料一级供应商', 4, 2, NOW(), NOW()),
('上海化学试剂公司', '周经理', '13912340003', '上海市宝山区化工路', '实验室试剂供应商', 4, 1, NOW(), NOW());

-- 订单示例数据
INSERT INTO `orders` (`order_no`, `customer_id`, `amount`, `order_time`, `fulfill_status`, `invoice_status`, `remark`, `user_id`, `create_time`, `update_time`) VALUES
('ORD20240115001', 1, 50000.00, '2024-01-15 10:30:00', 5, 4, '大批量订单', 2, NOW(), NOW()),
('ORD20240114002', 2, 30000.00, '2024-01-14 14:20:00', 3, 2, '生产中订单', 2, NOW(), NOW()),
('ORD20240113003', 3, 25000.00, '2024-01-13 09:15:00', 4, 3, '已发货', 3, NOW(), NOW());

-- 采购单示例数据
INSERT INTO `purchase` (`purchase_no`, `supplier_id`, `amount`, `purchase_time`, `fulfill_status`, `invoice_status`, `remark`, `user_id`, `create_time`, `update_time`) VALUES
('PUR20240115001', 1, 45000.00, '2024-01-15 11:00:00', 5, 4, '原材料采购', 4, NOW(), NOW()),
('PUR20240114002', 2, 28000.00, '2024-01-14 15:30:00', 3, 2, '塑料原料采购', 4, NOW(), NOW()),
('PUR20240113003', 3, 20000.00, '2024-01-13 10:00:00', 4, 3, '试剂采购', 4, NOW(), NOW());

-- 系统配置示例数据
INSERT INTO `system_config` (`config_key`, `config_value`, `config_name`, `config_type`, `description`, `create_time`, `update_time`) VALUES
('company_name', '化工单据管理系统', '公司名称', 'text', '系统显示的公司名称', NOW(), NOW()),
('page_size', '20', '分页大小', 'text', '列表每页显示的记录数', NOW(), NOW());
