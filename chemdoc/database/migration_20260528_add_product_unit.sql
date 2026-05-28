-- 添加产品单位字段到订单明细表和采购明细表
-- 执行时间：2026-05-28

-- 检查并添加 order_item 表的 product_unit 字段
ALTER TABLE `order_item` 
ADD COLUMN IF NOT EXISTS `product_unit` VARCHAR(20) DEFAULT NULL COMMENT '产品单位（冗余）' AFTER `product_spec`;

-- 检查并添加 purchase_item 表的 product_unit 字段
ALTER TABLE `purchase_item` 
ADD COLUMN IF NOT EXISTS `product_unit` VARCHAR(20) DEFAULT NULL COMMENT '产品单位（冗余）' AFTER `product_spec`;
