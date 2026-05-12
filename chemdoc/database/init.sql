-- ================================================
-- 化工单据管理系统 - 数据库初始化脚本
-- 数据库版本：MySQL 8.0
-- 字符集：utf8mb4
-- 排序规则：utf8mb4_unicode_ci
-- ================================================

-- 创建数据库
DROP DATABASE IF EXISTS `chemdoc`;
CREATE DATABASE `chemdoc`
    DEFAULT CHARACTER SET utf8mb4
    DEFAULT COLLATE utf8mb4_unicode_ci;

USE `chemdoc`;

-- ================================================
-- 1. 管理员用户表
-- ================================================
DROP TABLE IF EXISTS `admin_user`;
CREATE TABLE `admin_user` (
    `user_id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '用户ID',
    `username` VARCHAR(50) NOT NULL COMMENT '用户名',
    `password` VARCHAR(255) NOT NULL COMMENT '密码（bcrypt加密）',
    `salt` VARCHAR(32) NOT NULL COMMENT '密码盐值',
    `realname` VARCHAR(50) NOT NULL COMMENT '真实姓名',
    `mobile` VARCHAR(20) DEFAULT NULL COMMENT '手机号',
    `email` VARCHAR(100) DEFAULT NULL COMMENT '电子邮箱',
    `avatar` VARCHAR(255) DEFAULT NULL COMMENT '头像URL',
    `status` TINYINT(3) NOT NULL DEFAULT 1 COMMENT '状态：1-启用，0-禁用',
    `last_login_time` DATETIME DEFAULT NULL COMMENT '最后登录时间',
    `last_login_ip` VARCHAR(50) DEFAULT NULL COMMENT '最后登录IP',
    `create_user_id` INT UNSIGNED DEFAULT NULL COMMENT '创建人ID',
    `create_time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `update_time` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    `delete_time` DATETIME DEFAULT NULL COMMENT '删除时间',
    PRIMARY KEY (`user_id`),
    UNIQUE KEY `uk_username` (`username`),
    KEY `idx_status` (`status`),
    KEY `idx_create_user` (`create_user_id`),
    KEY `idx_delete_time` (`delete_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='管理员用户表';

-- ================================================
-- 2. 权限规则表
-- ================================================
DROP TABLE IF EXISTS `auth_rule`;
CREATE TABLE `auth_rule` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '规则ID',
    `name` VARCHAR(100) NOT NULL COMMENT '规则标识（控制器/方法）',
    `title` VARCHAR(50) NOT NULL COMMENT '规则名称（中文）',
    `type` TINYINT(3) NOT NULL DEFAULT 1 COMMENT '类型：1-规则',
    `pid` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '父级ID',
    `status` TINYINT(3) NOT NULL DEFAULT 1 COMMENT '状态：1-启用，0-禁用',
    `condition` VARCHAR(255) DEFAULT NULL COMMENT '条件表达式',
    `remark` VARCHAR(255) DEFAULT NULL COMMENT '备注',
    `is_menu` TINYINT(3) NOT NULL DEFAULT 0 COMMENT '是否菜单：1-是，0-否',
    `sort` INT NOT NULL DEFAULT 0 COMMENT '排序',
    `create_time` DATETIME DEFAULT NULL COMMENT '创建时间',
    `update_time` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_name` (`name`),
    KEY `idx_pid` (`pid`),
    KEY `idx_status` (`status`),
    KEY `idx_is_menu` (`is_menu`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='权限规则表';

-- ================================================
-- 3. 角色表
-- ================================================
DROP TABLE IF EXISTS `auth_group`;
CREATE TABLE `auth_group` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '角色ID',
    `name` VARCHAR(100) NOT NULL COMMENT '角色名称',
    `code` VARCHAR(50) DEFAULT NULL COMMENT '角色编码',
    `description` VARCHAR(500) DEFAULT NULL COMMENT '角色描述',
    `status` TINYINT(3) NOT NULL DEFAULT 1 COMMENT '状态：1-启用，0-禁用',
    `rules` VARCHAR(1000) DEFAULT NULL COMMENT '权限规则ID列表，逗号分隔',
    `create_time` DATETIME DEFAULT NULL COMMENT '创建时间',
    `update_time` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    PRIMARY KEY (`id`),
    KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='角色表';

-- ================================================
-- 4. 用户角色关联表
-- ================================================
DROP TABLE IF EXISTS `auth_group_access`;
CREATE TABLE `auth_group_access` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `uid` INT UNSIGNED NOT NULL COMMENT '用户ID',
    `group_id` INT UNSIGNED NOT NULL COMMENT '角色ID',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_uid_group` (`uid`, `group_id`),
    KEY `idx_uid` (`uid`),
    KEY `idx_group_id` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户角色关联表';

-- ================================================
-- 5. 产品分类表
-- ================================================
DROP TABLE IF EXISTS `product_category`;
CREATE TABLE `product_category` (
    `category_id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '分类ID',
    `name` VARCHAR(100) NOT NULL COMMENT '分类名称',
    `parent_id` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '父级分类ID，0为顶级',
    `level` TINYINT(3) NOT NULL DEFAULT 1 COMMENT '分类层级：1-一级，2-二级，3-三级',
    `sort` INT NOT NULL DEFAULT 0 COMMENT '排序，数值越小越靠前',
    `is_show` TINYINT(3) NOT NULL DEFAULT 1 COMMENT '是否显示：1-显示，0-隐藏',
    `create_user_id` INT UNSIGNED DEFAULT NULL COMMENT '创建人ID',
    `create_time` DATETIME DEFAULT NULL COMMENT '创建时间',
    `update_time` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    PRIMARY KEY (`category_id`),
    KEY `idx_parent_id` (`parent_id`),
    KEY `idx_sort` (`sort`),
    KEY `idx_is_show` (`is_show`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='产品分类表';

-- ================================================
-- 6. 产品信息表
-- ================================================
DROP TABLE IF EXISTS `product`;
CREATE TABLE `product` (
    `product_id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '产品ID',
    `name` VARCHAR(200) NOT NULL COMMENT '产品名称',
    `category_id` INT UNSIGNED DEFAULT NULL COMMENT '所属分类ID',
    `code` VARCHAR(50) DEFAULT NULL COMMENT '产品编码',
    `spec` VARCHAR(100) DEFAULT NULL COMMENT '规格型号',
    `unit` VARCHAR(20) DEFAULT NULL COMMENT '单位',
    `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT '单价',
    `description` TEXT COMMENT '产品描述',
    `origin` VARCHAR(100) DEFAULT NULL COMMENT '产地',
    `attachment` JSON DEFAULT NULL COMMENT '附件URL列表',
    `msds` JSON DEFAULT NULL COMMENT 'MSDS附件列表',
    `coa` JSON DEFAULT NULL COMMENT 'COA附件列表',
    `status` TINYINT(3) NOT NULL DEFAULT 1 COMMENT '状态：1-上架，0-下架',
    `create_user_id` INT UNSIGNED DEFAULT NULL COMMENT '创建人ID',
    `create_time` DATETIME DEFAULT NULL COMMENT '创建时间',
    `update_time` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    `delete_time` DATETIME DEFAULT NULL COMMENT '删除时间（软删除）',
    PRIMARY KEY (`product_id`),
    UNIQUE KEY `uk_code` (`code`),
    KEY `idx_category` (`category_id`),
    KEY `idx_status` (`status`),
    KEY `idx_create_user` (`create_user_id`),
    KEY `idx_delete_time` (`delete_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='产品信息表';

-- ================================================
-- 7. 客户表
-- ================================================
DROP TABLE IF EXISTS `customer`;
CREATE TABLE `customer` (
    `customer_id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '客户ID',
    `name` VARCHAR(200) NOT NULL COMMENT '客户名称',
    `code` VARCHAR(50) DEFAULT NULL COMMENT '客户编码',
    `industry` VARCHAR(50) DEFAULT NULL COMMENT '行业分类',
    `source` VARCHAR(50) DEFAULT NULL COMMENT '客户来源',
    `owner_user_id` INT UNSIGNED DEFAULT NULL COMMENT '所属业务员ID',
    `address` VARCHAR(500) DEFAULT NULL COMMENT '客户地址',
    `scale` VARCHAR(20) DEFAULT NULL COMMENT '客户规模',
    `annual_revenue` DECIMAL(12,2) DEFAULT NULL COMMENT '年营业额',
    `description` TEXT COMMENT '客户描述',
    `attachment` JSON DEFAULT NULL COMMENT '附件列表',
    `status` TINYINT(3) NOT NULL DEFAULT 1 COMMENT '状态：1-正常，0-禁用',
    `level` TINYINT(3) NOT NULL DEFAULT 1 COMMENT '客户等级：1-普通，2-重要，3-核心',
    `create_user_id` INT UNSIGNED DEFAULT NULL COMMENT '创建人ID',
    `create_time` DATETIME DEFAULT NULL COMMENT '创建时间',
    `update_time` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    `delete_time` DATETIME DEFAULT NULL COMMENT '删除时间（软删除）',
    PRIMARY KEY (`customer_id`),
    UNIQUE KEY `uk_name` (`name`),
    UNIQUE KEY `uk_code` (`code`),
    KEY `idx_owner` (`owner_user_id`),
    KEY `idx_industry` (`industry`),
    KEY `idx_status` (`status`),
    KEY `idx_level` (`level`),
    KEY `idx_create_user` (`create_user_id`),
    KEY `idx_create_time` (`create_time`),
    KEY `idx_delete_time` (`delete_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='客户表';

-- ================================================
-- 8. 联系人表
-- ================================================
DROP TABLE IF EXISTS `contact`;
CREATE TABLE `contact` (
    `contact_id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '联系人ID',
    `customer_id` INT UNSIGNED NOT NULL COMMENT '所属客户ID',
    `name` VARCHAR(100) NOT NULL COMMENT '联系人姓名',
    `gender` TINYINT(3) DEFAULT NULL COMMENT '性别：1-男，0-女',
    `position` VARCHAR(100) DEFAULT NULL COMMENT '职位',
    `mobile` VARCHAR(20) NOT NULL COMMENT '手机号',
    `phone` VARCHAR(20) DEFAULT NULL COMMENT '座机电话',
    `email` VARCHAR(100) DEFAULT NULL COMMENT '电子邮箱',
    `wechat` VARCHAR(50) DEFAULT NULL COMMENT '微信号',
    `birthday` DATE DEFAULT NULL COMMENT '生日',
    `remark` TEXT COMMENT '备注',
    `is_default` TINYINT(3) NOT NULL DEFAULT 0 COMMENT '是否默认联系人：1-是，0-否',
    `create_user_id` INT UNSIGNED DEFAULT NULL COMMENT '创建人ID',
    `create_time` DATETIME DEFAULT NULL COMMENT '创建时间',
    `update_time` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    PRIMARY KEY (`contact_id`),
    KEY `idx_customer` (`customer_id`),
    KEY `idx_mobile` (`mobile`),
    KEY `idx_is_default` (`is_default`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='联系人表';

-- ================================================
-- 9. 客户跟进记录表
-- ================================================
DROP TABLE IF EXISTS `customer_follow`;
CREATE TABLE `customer_follow` (
    `follow_id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '跟进ID',
    `customer_id` INT UNSIGNED NOT NULL COMMENT '所属客户ID',
    `follow_user_id` INT UNSIGNED NOT NULL COMMENT '跟进人ID',
    `method` VARCHAR(20) NOT NULL COMMENT '跟进方式：电话、拜访、邮件、其他',
    `content` TEXT NOT NULL COMMENT '跟进内容',
    `follow_time` DATETIME NOT NULL COMMENT '跟进时间',
    `next_follow_time` DATETIME DEFAULT NULL COMMENT '下次跟进时间',
    `result` TINYINT(3) DEFAULT NULL COMMENT '跟进结果：1-有意向，2-考虑中，3-无意向',
    `create_user_id` INT UNSIGNED DEFAULT NULL COMMENT '创建人ID',
    `create_time` DATETIME DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (`follow_id`),
    KEY `idx_customer` (`customer_id`),
    KEY `idx_follow_user` (`follow_user_id`),
    KEY `idx_follow_time` (`follow_time`),
    KEY `idx_next_follow_time` (`next_follow_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='客户跟进记录表';

-- ================================================
-- 10. 供应商表
-- ================================================
DROP TABLE IF EXISTS `supplier`;
CREATE TABLE `supplier` (
    `supplier_id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '供应商ID',
    `name` VARCHAR(200) NOT NULL COMMENT '供应商名称',
    `code` VARCHAR(50) DEFAULT NULL COMMENT '供应商编码',
    `type` VARCHAR(50) DEFAULT NULL COMMENT '供应商类型',
    `main_products` TEXT COMMENT '主营产品',
    `address` VARCHAR(500) DEFAULT NULL COMMENT '供应商地址',
    `cooperation_start` DATE DEFAULT NULL COMMENT '合作开始日期',
    `rating` TINYINT(3) DEFAULT NULL COMMENT '供应商评级：1-5星',
    `cert_expire_date` DATE DEFAULT NULL COMMENT '资质到期日期',
    `description` TEXT COMMENT '供应商描述',
    `attachment` JSON DEFAULT NULL COMMENT '附件列表',
    `status` TINYINT(3) NOT NULL DEFAULT 1 COMMENT '状态：1-正常，0-禁用',
    `create_user_id` INT UNSIGNED DEFAULT NULL COMMENT '创建人ID',
    `create_time` DATETIME DEFAULT NULL COMMENT '创建时间',
    `update_time` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    `delete_time` DATETIME DEFAULT NULL COMMENT '删除时间（软删除）',
    PRIMARY KEY (`supplier_id`),
    UNIQUE KEY `uk_name` (`name`),
    UNIQUE KEY `uk_code` (`code`),
    KEY `idx_type` (`type`),
    KEY `idx_status` (`status`),
    KEY `idx_rating` (`rating`),
    KEY `idx_create_user` (`create_user_id`),
    KEY `idx_delete_time` (`delete_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='供应商表';

-- ================================================
-- 11. 订单主表
-- ================================================
DROP TABLE IF EXISTS `order`;
CREATE TABLE `order` (
    `order_id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '订单ID',
    `order_no` VARCHAR(50) NOT NULL COMMENT '订单编号',
    `customer_id` INT UNSIGNED NOT NULL COMMENT '关联客户ID',
    `contact_id` INT UNSIGNED DEFAULT NULL COMMENT '关联联系人ID',
    `total_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT '订单总金额',
    `discount_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT '优惠金额',
    `actual_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT '实际金额',
    `order_time` DATETIME NOT NULL COMMENT '下单时间',
    `expect_delivery_date` DATE DEFAULT NULL COMMENT '预计交付日期',
    `actual_delivery_date` DATETIME DEFAULT NULL COMMENT '实际交付日期',
    `order_status` TINYINT(3) NOT NULL DEFAULT 1 COMMENT '订单状态',
    `invoice_status` TINYINT(3) NOT NULL DEFAULT 1 COMMENT '发票状态',
    `invoice_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT '已开票金额',
    `invoice_attachment` JSON DEFAULT NULL COMMENT '发票附件',
    `delivery_address` VARCHAR(500) DEFAULT NULL COMMENT '收货地址',
    `remark` TEXT COMMENT '订单备注',
    `purchase_order_id` INT UNSIGNED DEFAULT NULL COMMENT '关联采购单ID',
    `create_user_id` INT UNSIGNED DEFAULT NULL COMMENT '创建人ID',
    `create_time` DATETIME DEFAULT NULL COMMENT '创建时间',
    `update_time` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    `delete_time` DATETIME DEFAULT NULL COMMENT '删除时间（软删除）',
    PRIMARY KEY (`order_id`),
    UNIQUE KEY `uk_order_no` (`order_no`),
    KEY `idx_customer` (`customer_id`),
    KEY `idx_contact` (`contact_id`),
    KEY `idx_order_status` (`order_status`),
    KEY `idx_invoice_status` (`invoice_status`),
    KEY `idx_order_time` (`order_time`),
    KEY `idx_create_user` (`create_user_id`),
    KEY `idx_purchase_order` (`purchase_order_id`),
    KEY `idx_delete_time` (`delete_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='订单主表';

-- ================================================
-- 12. 订单明细表
-- ================================================
DROP TABLE IF EXISTS `order_item`;
CREATE TABLE `order_item` (
    `order_item_id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '订单明细ID',
    `order_id` INT UNSIGNED NOT NULL COMMENT '订单ID',
    `product_id` INT UNSIGNED NOT NULL COMMENT '产品ID',
    `product_name` VARCHAR(200) NOT NULL COMMENT '产品名称（冗余）',
    `product_spec` VARCHAR(100) DEFAULT NULL COMMENT '产品规格（冗余）',
    `product_unit` VARCHAR(20) DEFAULT NULL COMMENT '产品单位（冗余）',
    `unit_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT '单价',
    `quantity` DECIMAL(10,3) NOT NULL DEFAULT 0.000 COMMENT '订购数量',
    `shipped_quantity` DECIMAL(10,3) NOT NULL DEFAULT 0.000 COMMENT '已发货数量',
    `subtotal` DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT '小计金额',
    `create_time` DATETIME DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (`order_item_id`),
    KEY `idx_order` (`order_id`),
    KEY `idx_product` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='订单明细表';

-- ================================================
-- 13. 采购单主表
-- ================================================
DROP TABLE IF EXISTS `purchase_order`;
CREATE TABLE `purchase_order` (
    `purchase_id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '采购单ID',
    `order_no` VARCHAR(50) NOT NULL COMMENT '采购单编号',
    `supplier_id` INT UNSIGNED NOT NULL COMMENT '供应商ID',
    `contact` VARCHAR(100) DEFAULT NULL COMMENT '联系人',
    `phone` VARCHAR(20) DEFAULT NULL COMMENT '联系电话',
    `total_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT '采购单总金额',
    `expected_date` DATE DEFAULT NULL COMMENT '预计到货日期',
    `receive_date` DATETIME DEFAULT NULL COMMENT '实际到货日期',
    `status` TINYINT(3) NOT NULL DEFAULT 1 COMMENT '状态：1-草稿，2-已提交，3-已确认，4-已入库，5-已完成，6-已取消',
    `remark` TEXT COMMENT '采购单备注',
    `create_user_id` INT UNSIGNED DEFAULT NULL COMMENT '创建人ID',
    `create_time` DATETIME DEFAULT NULL COMMENT '创建时间',
    `update_time` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    `delete_time` DATETIME DEFAULT NULL COMMENT '删除时间（软删除）',
    PRIMARY KEY (`purchase_id`),
    UNIQUE KEY `uk_order_no` (`order_no`),
    KEY `idx_supplier` (`supplier_id`),
    KEY `idx_status` (`status`),
    KEY `idx_create_user` (`create_user_id`),
    KEY `idx_delete_time` (`delete_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='采购单主表';

-- ================================================
-- 14. 采购单明细表
-- ================================================
DROP TABLE IF EXISTS `purchase_item`;
CREATE TABLE `purchase_item` (
    `item_id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '采购明细ID',
    `purchase_id` INT UNSIGNED NOT NULL COMMENT '采购单ID',
    `product_id` INT UNSIGNED NOT NULL COMMENT '产品ID',
    `product_name` VARCHAR(200) NOT NULL COMMENT '产品名称（冗余）',
    `product_spec` VARCHAR(100) DEFAULT NULL COMMENT '产品规格（冗余）',
    `product_unit` VARCHAR(20) DEFAULT NULL COMMENT '产品单位（冗余）',
    `unit_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT '采购单价',
    `quantity` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT '采购数量',
    `subtotal` DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT '小计金额',
    `create_time` DATETIME DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (`item_id`),
    KEY `idx_purchase` (`purchase_id`),
    KEY `idx_product` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='采购单明细表';

-- 添加外键约束
ALTER TABLE `purchase_item` ADD CONSTRAINT `fk_purchase_item_purchase` FOREIGN KEY (`purchase_id`) REFERENCES `purchase_order` (`purchase_id`) ON DELETE CASCADE;
ALTER TABLE `purchase_item` ADD CONSTRAINT `fk_purchase_item_product` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`) ON DELETE RESTRICT;

-- ================================================
-- 15. 操作日志表
-- ================================================
DROP TABLE IF EXISTS `operation_log`;
CREATE TABLE `operation_log` (
    `log_id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '日志ID',
    `user_id` INT UNSIGNED NOT NULL COMMENT '操作用户ID',
    `username` VARCHAR(50) NOT NULL COMMENT '操作用户名',
    `module` VARCHAR(50) NOT NULL COMMENT '操作模块',
    `action` VARCHAR(20) NOT NULL COMMENT '操作类型',
    `description` VARCHAR(255) DEFAULT NULL COMMENT '操作描述',
    `request_method` VARCHAR(10) DEFAULT NULL COMMENT 'HTTP请求方法',
    `request_url` VARCHAR(500) DEFAULT NULL COMMENT '请求地址',
    `request_params` TEXT COMMENT '请求参数JSON',
    `response_code` INT DEFAULT NULL COMMENT '响应状态码',
    `client_ip` VARCHAR(50) DEFAULT NULL COMMENT '客户端IP',
    `user_agent` VARCHAR(500) DEFAULT NULL COMMENT '浏览器UA',
    `execution_time` DECIMAL(10,3) DEFAULT NULL COMMENT '执行时间（秒）',
    `create_time` DATETIME DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (`log_id`),
    KEY `idx_user` (`user_id`),
    KEY `idx_module` (`module`),
    KEY `idx_action` (`action`),
    KEY `idx_create_time` (`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='操作日志表';

-- ================================================
-- 初始化数据
-- ================================================

-- 管理员用户
INSERT INTO `admin_user` (`username`, `password`, `salt`, `realname`, `mobile`, `email`, `status`, `create_time`) VALUES
('admin', '$2y$10$F95j4zpjbNdhdWRTBdfWNeW9KeGP.ipzGdYgEHH7PuAmKyg6nFR82', 'random_salt_001', '系统管理员', '13800138000', 'admin@chemdoc.com', 1, NOW()),
('zhangsan', '$2y$10$F95j4zpjbNdhdWRTBdfWNeW9KeGP.ipzGdYgEHH7PuAmKyg6nFR82', 'random_salt_002', '张三', '13800138001', 'zhangsan@chemdoc.com', 1, NOW()),
('lisi', '$2y$10$F95j4zpjbNdhdWRTBdfWNeW9KeGP.ipzGdYgEHH7PuAmKyg6nFR82', 'random_salt_003', '李四', '13800138002', 'lisi@chemdoc.com', 1, NOW());

-- 权限规则
INSERT INTO `auth_rule` (`name`, `title`, `type`, `pid`, `status`, `is_menu`, `sort`, `create_time`) VALUES
('dashboard', '首页', 1, 0, 1, 1, 1, NOW()),
('product', '产品管理', 1, 0, 1, 1, 2, NOW()),
('customer', '客户管理', 1, 0, 1, 1, 3, NOW()),
('supplier', '供应商管理', 1, 0, 1, 1, 4, NOW()),
('order', '订单管理', 1, 0, 1, 1, 5, NOW()),
('purchase', '采购单管理', 1, 0, 1, 1, 6, NOW()),
('statistics', '数据统计', 1, 0, 1, 1, 7, NOW()),
('system', '系统管理', 1, 0, 1, 1, 8, NOW()),
('operation', '操作日志', 1, 0, 1, 1, 9, NOW());

-- 角色
INSERT INTO `auth_group` (`name`, `code`, `description`, `status`, `rules`, `create_time`) VALUES
('超级管理员', 'admin', '拥有系统全部权限', 1, '*', NOW()),
('销售人员', 'sales', '负责客户开发和订单管理', 1, '2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32', NOW()),
('采购人员', 'procurement', '负责供应商管理和采购执行', 1, '4,6,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32', NOW());

-- 用户角色关联
INSERT INTO `auth_group_access` (`uid`, `group_id`) VALUES
(1, 1),
(2, 2),
(3, 3);

-- 产品分类
INSERT INTO `product_category` (`name`, `parent_id`, `level`, `sort`, `is_show`, `create_time`) VALUES
('有机化工原料', 0, 1, 1, 1, NOW()),
('无机化工原料', 0, 1, 2, 1, NOW()),
('溶剂类', 0, 1, 3, 1, NOW()),
('助剂类', 0, 1, 4, 1, NOW()),
('塑料原料', 0, 1, 5, 1, NOW()),
('橡胶原料', 0, 1, 6, 1, NOW());

-- 产品
INSERT INTO `product` (`name`, `category_id`, `code`, `spec`, `unit`, `price`, `description`, `status`, `create_user_id`, `create_time`) VALUES
('乙醇（无水乙醇）', 1, 'P001', '工业级，99.5%', '吨', 8500.00, '工业用无水乙醇', 1, 1, NOW()),
('甲醇（工业级）', 1, 'P002', '工业级，99.5%', '吨', 3800.00, '工业用甲醇', 1, 1, NOW()),
('甲苯', 1, 'P003', '工业级', '吨', 7200.00, '有机化工原料', 1, 1, NOW()),
('丙酮', 3, 'P004', '分析纯', '桶', 680.00, '常用有机溶剂', 1, 1, NOW()),
('乙酸乙酯', 3, 'P005', '工业级', '吨', 7800.00, '常用有机溶剂', 1, 1, NOW());

-- 客户
INSERT INTO `customer` (`name`, `code`, `industry`, `source`, `owner_user_id`, `address`, `scale`, `annual_revenue`, `description`, `status`, `level`, `create_user_id`, `create_time`) VALUES
('上海化工贸易有限公司', 'C001', '石油化工', '网络推广', 2, '上海市浦东新区', '大型', 50000000.00, '专业从事化工原料贸易', 1, 3, 1, NOW()),
('杭州精细化工有限公司', 'C002', '精细化工', '老客户介绍', 2, '浙江省杭州市', '中型', 20000000.00, '专注于精细化工产品生产', 1, 2, 1, NOW()),
('苏州塑料制品厂', 'C003', '塑料橡胶', '行业展会', 2, '江苏省苏州市', '中型', 15000000.00, '塑料制品加工企业', 1, 2, 1, NOW());

-- 联系人
INSERT INTO `contact` (`customer_id`, `name`, `gender`, `position`, `mobile`, `email`, `is_default`, `create_user_id`, `create_time`) VALUES
(1, '王经理', 1, '采购经理', '13900139001', 'wang@shhg.com', 1, 1, NOW()),
(1, '李主管', 0, '采购主管', '13900139002', 'li@shhg.com', 0, 1, NOW()),
(2, '张总', 1, '总经理', '13900139003', 'zhang@hzjx.com', 1, 1, NOW()),
(3, '刘厂长', 1, '厂长', '13900139005', 'liu@szsl.com', 1, 1, NOW());

-- 供应商
INSERT INTO `supplier` (`name`, `code`, `type`, `main_products`, `address`, `cooperation_start`, `rating`, `cert_expire_date`, `status`, `create_user_id`, `create_time`) VALUES
('中石化化工销售有限公司', 'S001', '原料供应商', '乙烯、丙烯、甲醇', '北京市朝阳区', '2020-01-01', 5, '2027-06-30', 1, 1, NOW()),
('万华化学集团股份有限公司', 'S002', '原料供应商', '聚氨酯、MDI、TDI', '山东省烟台市', '2019-06-15', 5, '2027-12-31', 1, 1, NOW()),
('浙江化工原料贸易商', 'S003', '贸易商', '各类有机化工原料、溶剂', '浙江省杭州市', '2021-03-20', 4, '2026-09-30', 1, 1, NOW());

-- 订单
INSERT INTO `order` (`order_no`, `customer_id`, `contact_id`, `total_amount`, `discount_amount`, `actual_amount`, `order_time`, `expect_delivery_date`, `order_status`, `invoice_status`, `delivery_address`, `create_user_id`, `create_time`) VALUES
('ORD2026050001', 1, 1, 68000.00, 0.00, 68000.00, '2026-05-01 09:30:00', '2026-05-15', 5, 3, '上海市浦东新区', 2, '2026-05-01 09:30:00'),
('ORD2026050002', 2, 3, 42500.00, 2500.00, 40000.00, '2026-05-03 14:20:00', '2026-05-18', 4, 1, '浙江省杭州市', 2, '2026-05-03 14:20:00'),
('ORD2026050003', 3, 4, 98000.00, 0.00, 98000.00, '2026-05-05 10:00:00', '2026-05-20', 3, 1, '江苏省苏州市', 2, '2026-05-05 10:00:00');

-- 订单明细
INSERT INTO `order_item` (`order_id`, `product_id`, `product_name`, `product_spec`, `product_unit`, `unit_price`, `quantity`, `shipped_quantity`, `subtotal`, `create_time`) VALUES
(1, 1, '乙醇（无水乙醇）', '工业级，99.5%', '吨', 8500.00, 5.000, 5.000, 42500.00, NOW()),
(1, 3, '甲苯', '工业级', '吨', 7200.00, 2.000, 2.000, 14400.00, NOW()),
(1, 4, '丙酮', '分析纯', '桶', 680.00, 30.000, 30.000, 11100.00, NOW()),
(2, 2, '甲醇（工业级）', '工业级，99.5%', '吨', 3800.00, 8.000, 8.000, 30400.00, NOW()),
(3, 5, '乙酸乙酯', '工业级', '吨', 7800.00, 10.000, 0.000, 98000.00, NOW());
