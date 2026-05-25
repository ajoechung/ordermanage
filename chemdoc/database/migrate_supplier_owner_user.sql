-- ================================================
-- 供应商表增加负责人字段
-- ================================================

ALTER TABLE `supplier` 
ADD COLUMN `owner_user_id` INT UNSIGNED DEFAULT NULL COMMENT '负责人ID' AFTER `create_user_id`,
ADD KEY `idx_owner_user` (`owner_user_id`);
