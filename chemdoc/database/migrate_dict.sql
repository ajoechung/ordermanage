-- ================================================
-- 字典数据表
-- ================================================
DROP TABLE IF EXISTS `sys_dict_type`;
CREATE TABLE `sys_dict_type` (
    `dict_id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '字典类型ID',
    `dict_name` VARCHAR(100) NOT NULL COMMENT '字典类型名称',
    `dict_code` VARCHAR(50) NOT NULL COMMENT '字典类型编码',
    `description` VARCHAR(500) DEFAULT NULL COMMENT '字典描述',
    `status` TINYINT(3) NOT NULL DEFAULT 1 COMMENT '状态：1-启用，0-禁用',
    `sort` INT NOT NULL DEFAULT 0 COMMENT '排序',
    `create_user_id` INT UNSIGNED DEFAULT NULL COMMENT '创建人ID',
    `create_time` DATETIME DEFAULT NULL COMMENT '创建时间',
    `update_time` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    PRIMARY KEY (`dict_id`),
    UNIQUE KEY `uk_dict_code` (`dict_code`),
    KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='字典类型表';

DROP TABLE IF EXISTS `sys_dict_data`;
CREATE TABLE `sys_dict_data` (
    `data_id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '字典数据ID',
    `dict_id` INT UNSIGNED NOT NULL COMMENT '关联字典类型ID',
    `dict_label` VARCHAR(100) NOT NULL COMMENT '字典标签',
    `dict_value` VARCHAR(100) NOT NULL COMMENT '字典值',
    `description` VARCHAR(500) DEFAULT NULL COMMENT '描述',
    `status` TINYINT(3) NOT NULL DEFAULT 1 COMMENT '状态：1-启用，0-禁用',
    `sort` INT NOT NULL DEFAULT 0 COMMENT '排序',
    `create_user_id` INT UNSIGNED DEFAULT NULL COMMENT '创建人ID',
    `create_time` DATETIME DEFAULT NULL COMMENT '创建时间',
    `update_time` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    PRIMARY KEY (`data_id`),
    KEY `idx_dict_id` (`dict_id`),
    KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='字典数据表';

-- 初始化字典类型数据
INSERT INTO `sys_dict_type` (`dict_name`, `dict_code`, `description`, `status`, `sort`, `create_time`) VALUES
('产品单位', 'product_unit', '产品使用的单位（如：吨、桶、公斤等）', 1, 1, NOW()),
('客户行业', 'customer_industry', '客户所属行业分类', 1, 2, NOW()),
('客户来源', 'customer_source', '客户来源渠道', 1, 3, NOW()),
('客户规模', 'customer_scale', '客户规模分类', 1, 4, NOW()),
('跟进方式', 'follow_method', '客户跟进方式', 1, 5, NOW()),
('合作状态', 'cooperation_status', '供应商合作状态', 1, 6, NOW());

-- 初始化字典数据
INSERT INTO `sys_dict_data` (`dict_id`, `dict_label`, `dict_value`, `description`, `status`, `sort`, `create_time`) VALUES
-- 产品单位
(1, '吨', '吨', '重量单位', 1, 1, NOW()),
(1, '桶', '桶', '容量单位', 1, 2, NOW()),
(1, '公斤', '公斤', '重量单位', 1, 3, NOW()),
(1, '千克', '千克', '重量单位', 1, 4, NOW()),
(1, '克', '克', '重量单位', 1, 5, NOW()),
(1, '升', '升', '容量单位', 1, 6, NOW()),
(1, '立方米', '立方米', '体积单位', 1, 7, NOW()),
-- 客户行业
(2, '石油化工', '石油化工', '石油化工行业', 1, 1, NOW()),
(2, '精细化工', '精细化工', '精细化工行业', 1, 2, NOW()),
(2, '塑料橡胶', '塑料橡胶', '塑料橡胶行业', 1, 3, NOW()),
(2, '涂料油墨', '涂料油墨', '涂料油墨行业', 1, 4, NOW()),
(2, '医药化工', '医药化工', '医药化工行业', 1, 5, NOW()),
(2, '农药化工', '农药化工', '农药化工行业', 1, 6, NOW()),
(2, '日用化工', '日用化工', '日用化工行业', 1, 7, NOW()),
-- 客户来源
(3, '网络推广', '网络推广', '通过网络推广获取', 1, 1, NOW()),
(3, '老客户介绍', '老客户介绍', '老客户介绍获取', 1, 2, NOW()),
(3, '行业展会', '行业展会', '通过行业展会获取', 1, 3, NOW()),
(3, '电话营销', '电话营销', '通过电话营销获取', 1, 4, NOW()),
(3, '朋友介绍', '朋友介绍', '朋友介绍获取', 1, 5, NOW()),
(3, '其他', '其他', '其他渠道', 1, 6, NOW()),
-- 客户规模
(4, '大型', '大型', '大型企业', 1, 1, NOW()),
(4, '中型', '中型', '中型企业', 1, 2, NOW()),
(4, '小型', '小型', '小型企业', 1, 3, NOW()),
(4, '微型', '微型', '微型企业', 1, 4, NOW()),
-- 跟进方式
(5, '电话', '电话', '电话跟进', 1, 1, NOW()),
(5, '微信', '微信', '微信跟进', 1, 2, NOW()),
(5, '邮件', '邮件', '邮件跟进', 1, 3, NOW()),
(5, '拜访', '拜访', '上门拜访', 1, 4, NOW()),
(5, '面谈', '面谈', '当面洽谈', 1, 5, NOW()),
(5, '其他', '其他', '其他方式', 1, 6, NOW()),
-- 合作状态
(6, '已合作', 'active', '已建立合作关系', 1, 1, NOW()),
(6, '待合作', 'pending', '待建立合作关系', 1, 2, NOW()),
(6, '已终止', 'terminated', '已终止合作', 1, 3, NOW());
