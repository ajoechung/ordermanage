-- 修复数据库表缺少 update_time 字段的问题
-- 执行前请先备份数据库

USE `chemdoc`;

-- ==============================================
-- 1. 检查并修复 order 表
-- ==============================================
SET @column_exists = (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
    AND table_name = 'order'
    AND column_name = 'update_time'
);

SET @sql = IF(@column_exists = 0,
    'ALTER TABLE `order` ADD COLUMN `update_time` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT ''更新时间'' AFTER `create_time`',
    'SELECT "Column update_time already exists in order table" AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ==============================================
-- 2. 检查并修复 order_item 表
-- ==============================================
SET @column_exists = (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
    AND table_name = 'order_item'
    AND column_name = 'update_time'
);

SET @sql = IF(@column_exists = 0,
    'ALTER TABLE `order_item` ADD COLUMN `update_time` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT ''更新时间'' AFTER `create_time`',
    'SELECT "Column update_time already exists in order_item table" AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ==============================================
-- 3. 检查并修复 purchase_order 表
-- ==============================================
SET @column_exists = (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
    AND table_name = 'purchase_order'
    AND column_name = 'update_time'
);

SET @sql = IF(@column_exists = 0,
    'ALTER TABLE `purchase_order` ADD COLUMN `update_time` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT ''更新时间'' AFTER `create_time`',
    'SELECT "Column update_time already exists in purchase_order table" AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ==============================================
-- 4. 检查并修复 purchase_item 表
-- ==============================================
SET @column_exists = (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
    AND table_name = 'purchase_item'
    AND column_name = 'update_time'
);

SET @sql = IF(@column_exists = 0,
    'ALTER TABLE `purchase_item` ADD COLUMN `update_time` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT ''更新时间'' AFTER `create_time`',
    'SELECT "Column update_time already exists in purchase_item table" AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SELECT 'Database fix completed successfully!' AS message;
