-- 为供应商表添加缺失字段
ALTER TABLE `supplier` 
ADD COLUMN `contact` VARCHAR(100) NULL COMMENT '联系人' AFTER `type`,
ADD COLUMN `phone` VARCHAR(50) NULL COMMENT '联系电话' AFTER `contact`,
ADD COLUMN `cooperation_status` VARCHAR(20) NULL COMMENT '合作状态' AFTER `phone`,
ADD COLUMN `remark` TEXT NULL COMMENT '备注' AFTER `description`;
