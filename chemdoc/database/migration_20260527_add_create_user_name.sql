-- ================================================
-- 添加 create_user_name 字段到 contact 和 customer_follow 表
-- ================================================

ALTER TABLE `contact` 
ADD COLUMN `create_user_name` VARCHAR(100) DEFAULT NULL COMMENT '创建人姓名' AFTER `create_user_id`;

ALTER TABLE `customer_follow` 
ADD COLUMN `create_user_name` VARCHAR(100) DEFAULT NULL COMMENT '创建人姓名' AFTER `create_user_id`;
