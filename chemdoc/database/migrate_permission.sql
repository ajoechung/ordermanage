-- ================================================
-- 权限表数据迁移脚本
-- 文件: database/migrate_permission.sql
-- 用途: 更新权限规则表和角色表数据，与菜单栏保持一致
-- ================================================

-- ----------------------------
-- 1. 删除旧的权限规则数据
-- ----------------------------
DELETE FROM `auth_rule`;
ALTER TABLE `auth_rule` AUTO_INCREMENT = 1;

-- ----------------------------
-- 2. 插入新的权限规则数据（两级结构，与菜单栏一致）
-- ----------------------------
INSERT INTO `auth_rule` (`name`, `title`, `type`, `pid`, `status`, `is_menu`, `sort`, `create_time`) VALUES
-- 一级菜单
('dashboard', '首页', 1, 0, 1, 1, 1, NOW()),
('customer', '客户管理', 1, 0, 1, 1, 2, NOW()),
('product', '产品管理', 1, 0, 1, 1, 3, NOW()),
('supplier', '供应商管理', 1, 0, 1, 1, 4, NOW()),
('order', '订单管理', 1, 0, 1, 1, 5, NOW()),
('purchase', '采购单管理', 1, 0, 1, 1, 6, NOW()),
('statistics', '数据统计', 1, 0, 1, 1, 7, NOW()),
('system', '系统管理', 1, 0, 1, 1, 8, NOW()),
('dict', '字典管理', 1, 0, 1, 1, 9, NOW()),
('log', '操作日志', 1, 0, 1, 1, 10, NOW()),

-- 二级菜单：客户管理 (pid=2)
('customer/list', '客户列表', 1, 2, 1, 1, 1, NOW()),
('customer/detail', '客户详情', 1, 2, 1, 1, 2, NOW()),
('customer/contact', '联系人列表', 1, 2, 1, 1, 3, NOW()),
('customer/follow', '跟进记录', 1, 2, 1, 1, 4, NOW()),

-- 二级菜单：产品管理 (pid=3)
('product/category', '产品分类', 1, 3, 1, 1, 1, NOW()),
('product/list', '产品信息', 1, 3, 1, 1, 2, NOW()),

-- 二级菜单：供应商管理 (pid=4)
('supplier/list', '供应商列表', 1, 4, 1, 1, 1, NOW()),

-- 二级菜单：订单管理 (pid=5)
('order/list', '订单列表', 1, 5, 1, 1, 1, NOW()),

-- 二级菜单：采购单管理 (pid=6)
('purchase/list', '采购单列表', 1, 6, 1, 1, 1, NOW()),

-- 二级菜单：数据统计 (pid=7)
('statistics/customer', '客户统计', 1, 7, 1, 1, 1, NOW()),
('statistics/order', '订单统计', 1, 7, 1, 1, 2, NOW()),

-- 二级菜单：系统管理 (pid=8)
('system/user', '用户管理', 1, 8, 1, 1, 1, NOW()),
('system/role', '角色管理', 1, 8, 1, 1, 2, NOW()),
('system/permission', '权限管理', 1, 8, 1, 1, 3, NOW()),

-- 二级菜单：字典管理 (pid=9)
('dict/type', '字典类型', 1, 9, 1, 1, 1, NOW()),
('dict/data', '字典数据', 1, 9, 1, 1, 2, NOW()),

-- 二级菜单：操作日志 (pid=10)
('log/list', '日志列表', 1, 10, 1, 1, 1, NOW());

-- ----------------------------
-- 3. 更新角色权限数据
-- ----------------------------
-- 超级管理员：拥有全部权限
UPDATE `auth_group` SET `rules` = '*' WHERE `code` = 'admin';

-- 销售人员：客户管理 + 订单管理
UPDATE `auth_group` SET `rules` = '2,11,12,13,14,5,19' WHERE `code` = 'sales';

-- 采购人员：供应商管理 + 采购单管理
UPDATE `auth_group` SET `rules` = '4,16,6,18' WHERE `code` = 'procurement';

-- ----------------------------
-- 4. 如果角色不存在，则插入角色数据
-- ----------------------------
INSERT IGNORE INTO `auth_group` (`name`, `code`, `description`, `status`, `rules`, `create_time`) VALUES
('超级管理员', 'admin', '拥有系统全部权限', 1, '*', NOW()),
('销售人员', 'sales', '负责客户开发和订单管理', 1, '2,11,12,13,14,5,19', NOW()),
('采购人员', 'procurement', '负责供应商管理和采购执行', 1, '4,16,6,18', NOW());

-- ----------------------------
-- 5. 更新用户角色关联（确保admin用户拥有超级管理员角色）
-- ----------------------------
INSERT IGNORE INTO `auth_group_access` (`uid`, `group_id`) VALUES
(1, 1);

-- ----------------------------
-- 执行完成提示
-- ----------------------------
SELECT '权限数据迁移完成！' AS result;
SELECT COUNT(*) AS total_rules FROM `auth_rule`;
SELECT COUNT(*) AS total_groups FROM `auth_group`;