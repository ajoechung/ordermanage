-- 为客户表添加负责人和创建人姓名字段
ALTER TABLE `customer` 
ADD COLUMN `owner_user_name` VARCHAR(100) NULL COMMENT '负责人姓名' AFTER `owner_user_id`,
ADD COLUMN `create_user_name` VARCHAR(100) NULL COMMENT '创建人姓名' AFTER `create_user_id`;

-- 为供应商表添加负责人和创建人姓名字段
ALTER TABLE `supplier` 
ADD COLUMN `owner_user_name` VARCHAR(100) NULL COMMENT '负责人姓名' AFTER `owner_user_id`,
ADD COLUMN `create_user_name` VARCHAR(100) NULL COMMENT '创建人姓名' AFTER `create_user_id`;
