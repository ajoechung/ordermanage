-- 产品表字段迁移脚本
-- 执行前请先备份数据库

USE `chemdoc`;

-- 检查并添加 origin 字段（产地）
SET @column_exists = (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
    AND table_name = 'product'
    AND column_name = 'origin'
);

SET @sql = IF(@column_exists = 0,
    'ALTER TABLE `product` ADD COLUMN `origin` VARCHAR(100) DEFAULT NULL COMMENT ''产地'' AFTER `description`',
    'SELECT ''Column origin already exists'' AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 检查并添加 msds 字段
SET @column_exists = (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
    AND table_name = 'product'
    AND column_name = 'msds'
);

SET @sql = IF(@column_exists = 0,
    'ALTER TABLE `product` ADD COLUMN `msds` JSON DEFAULT NULL COMMENT ''MSDS附件列表'' AFTER `attachment`',
    'SELECT ''Column msds already exists'' AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 检查并添加 coa 字段
SET @column_exists = (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
    AND table_name = 'product'
    AND column_name = 'coa'
);

SET @sql = IF(@column_exists = 0,
    'ALTER TABLE `product` ADD COLUMN `coa` JSON DEFAULT NULL COMMENT ''COA附件列表'' AFTER `msds`',
    'SELECT ''Column coa already exists'' AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 检查并删除 price 字段（单价）
SET @column_exists = (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
    AND table_name = 'product'
    AND column_name = 'price'
);

SET @sql = IF(@column_exists = 1,
    'ALTER TABLE `product` DROP COLUMN `price`',
    'SELECT ''Column price already removed or does not exist'' AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SELECT 'Database migration completed successfully!' AS message;
