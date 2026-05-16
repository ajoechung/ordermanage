-- 化工单据管理系统 - 订单发票和采购单关联订单迁移脚本
-- 执行前请先备份数据库

USE `chemdoc`;

-- ==============================================
-- 1. 创建 order_invoice 表
-- ==============================================
SET @table_exists = (
    SELECT COUNT(*)
    FROM information_schema.tables
    WHERE table_schema = DATABASE()
    AND table_name = 'order_invoice'
);

SET @sql = IF(@table_exists = 0,
    'CREATE TABLE `order_invoice` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `order_id` int(11) DEFAULT 0 COMMENT "订单ID",
        `file_name` varchar(255) DEFAULT "" COMMENT "文件名",
        `file_path` varchar(500) DEFAULT "" COMMENT "文件路径",
        `file_size` int(11) DEFAULT 0 COMMENT "文件大小",
        `file_type` varchar(100) DEFAULT "" COMMENT "文件类型",
        `create_time` datetime DEFAULT NULL COMMENT "创建时间",
        PRIMARY KEY (`id`),
        KEY `idx_order_id` (`order_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT="订单发票表"',
    'SELECT "Table order_invoice already exists" AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ==============================================
-- 2. 给 purchase_order 表添加 order_id 字段
-- ==============================================
SET @column_exists = (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
    AND table_name = 'purchase_order'
    AND column_name = 'order_id'
);

SET @sql = IF(@column_exists = 0,
    'ALTER TABLE `purchase_order` ADD COLUMN `order_id` int(11) NULL DEFAULT 0 COMMENT "关联订单ID"',
    'SELECT "Column order_id already exists in purchase_order" AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SELECT 'Database migration completed successfully!' AS message;
