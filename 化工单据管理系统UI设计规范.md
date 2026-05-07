# 化工单据管理系统 UI设计规范

## 文档信息

| 属性 | 内容 |
|------|------|
| 项目名称 | 化工单据管理系统 |
| 设计版本 | V1.0 |
| 创建日期 | 2026-05-07 |
| 设计风格 | 简约企业后台风（CRM Style） |
| 前端框架 | Vue3 + Element Plus |
| 文档状态 | 初稿 |

---

## 第一部分：UI视觉风格规范

### 1.1 设计理念

本系统采用**简约企业后台风**设计理念，兼顾专业性与易用性：

- **专业稳重**：深蓝色主调传递企业级应用的可靠感
- **简约高效**：减少视觉干扰，突出业务数据
- **操作友好**：符合CRM业务操作习惯，降低学习成本
- **响应迅速**：轻量级交互动效，提升操作体验

### 1.2 色彩体系

#### 1.2.1 主色调（Primary Colors）

| 色彩名称 | 色值（HEX） | RGB值 | 使用场景 |
|----------|-------------|-------|----------|
| 主色-深蓝 | #1A5C9E | rgb(26, 92, 158) | 主要按钮、选中状态、链接、主导航背景 |
| 主色-浅蓝 | #409EFF | rgb(64, 158, 255) | 辅助强调、hover状态、次要按钮边框 |
| 主色-深蓝 Hover | #1470CC | rgb(20, 112, 204) | 按钮hover状态 |
| 主色-深蓝 按下 | #0D5CAD | rgb(13, 92, 173) | 按钮active状态 |
| 主色-深蓝 禁用 | #A0C4E8 | rgb(160, 196, 232) | 禁用状态 |

#### 1.2.2 辅助色调（Secondary Colors）

| 色彩名称 | 色值（HEX） | RGB值 | 使用场景 |
|----------|-------------|-------|----------|
| 成功-绿色 | #67C23A | rgb(103, 194, 58) | 成功状态、完成状态、正向指标 |
| 警告-橙色 | #E6A23C | rgb(230, 162, 60) | 警告状态、待处理、进行中 |
| 危险-红色 | #F56C6C | rgb(245, 108, 108) | 错误状态、删除操作、负向指标 |
| 信息-青色 | #909399 | rgb(144, 147, 153) | 信息状态、辅助文字、分割线 |
| 紫色-强调 | #8E44AD | rgb(142, 68, 173) | 特殊强调、数据可视化辅助色 |

#### 1.2.3 中性色调（Neutral Colors）

| 色彩名称 | 色值（HEX） | RGB值 | 使用场景 |
|----------|-------------|-------|----------|
| 标题文字 | #303133 | rgb(48, 49, 51) | 一级标题、重要正文 |
| 正文文字 | #606266 | rgb(96, 98, 102) | 正文内容、次要说明 |
| 辅助文字 | #909399 | rgb(144, 147, 153) | 辅助说明、占位符、日期 |
| 边框颜色 | #DCDFE6 | rgb(220, 223, 230) | 输入框边框、表格边框、分隔线 |
| 边框-深色 | #C0C4CC | rgb(192, 196, 204) | hover边框、次要边框 |
| 背景-页面 | #F5F7FA | rgb(245, 247, 250) | 页面背景、表格斑马纹 |
| 背景-白色 | #FFFFFF | rgb(255, 255, 255) | 卡片背景、弹窗背景、输入框背景 |
| 背景-侧边栏 | #304156 | rgb(48, 65, 86) | 侧边栏深色背景 |
| 背景-顶栏 | #FFFFFF | rgb(255, 255, 255) | 顶部导航栏背景 |

#### 1.2.4 语义色彩映射

```css
:root {
  /* Element Plus 主题色覆盖 */
  --el-color-primary: #1A5C9E;
  --el-color-primary-light-3: #3D7AC7;
  --el-color-primary-light-5: #5A94D6;
  --el-color-primary-light-7: #7AADE4;
  --el-color-primary-light-8: #9AC0EB;
  --el-color-primary-light-9: #BAD4F2;
  --el-color-primary-dark-2: #1470CC;
  
  /* 功能色 */
  --el-color-success: #67C23A;
  --el-color-warning: #E6A23C;
  --el-color-danger: #F56C6C;
  --el-color-info: #909399;
  
  /* 文字色 */
  --el-text-color-primary: #303133;
  --el-text-color-regular: #606266;
  --el-text-color-secondary: #909399;
  --el-text-color-placeholder: #C0C4CC;
  
  /* 边框色 */
  --el-border-color-base: #DCDFE6;
  --el-border-color-light: #E4E7ED;
  --el-border-color-lighter: #EBEEF5;
  --el-border-color-extra-light: #F2F6FC;
  
  /* 背景色 */
  --el-bg-color: #FFFFFF;
  --el-bg-color-page: #F5F7FA;
  --el-bg-color-secondary: #F5F7FA;
}
```

### 1.3 字体规范

#### 1.3.1 字体家族

| 字体类型 | 字体名称 | 备选字体 | 使用场景 |
|----------|----------|----------|----------|
| 中文字体 | "Microsoft YaHei", "微软雅黑" | "PingFang SC", "Hiragino Sans GB" | 中文正文、标题 |
| 英文字体 | "Helvetica Neue", Helvetica | "Arial", sans-serif | 英文正文、数字 |
| 等宽字体 | "Menlo", "Monaco", "Consolas" | monospace | 代码、数值、编号 |

#### 1.3.2 字体层级

| 层级 | 字号 | 字重 | 行高 | 使用场景 | 色值 |
|------|------|------|------|----------|------|
| H1 | 24px | 600 | 32px | 页面大标题 | #303133 |
| H2 | 20px | 600 | 28px | 模块标题、弹窗标题 | #303133 |
| H3 | 16px | 600 | 24px | 卡片标题、表单分组标题 | #303133 |
| H4 | 14px | 600 | 20px | 次级标题 | #303133 |
| 正文 | 14px | 400 | 22px | 主要内容、表单标签 | #606266 |
| 正文-辅助 | 14px | 400 | 22px | 次要说明文字 | #909399 |
| 小字 | 12px | 400 | 18px | 表格内容、按钮文字、提示信息 | #606266 |
| 最小 | 12px | 400 | 16px | 时间戳、版本号 | #909399 |

#### 1.3.3 CSS字体声明

```css
/* 全局字体样式 */
body {
  font-family: "Microsoft YaHei", "PingFang SC", "Hiragino Sans GB", 
               "Helvetica Neue", Helvetica, Arial, sans-serif;
  font-size: 14px;
  line-height: 1.5;
  color: #606266;
  background-color: #F5F7FA;
}

/* 标题字体 */
h1, h2, h3, h4, h5, h6 {
  font-family: "Microsoft YaHei", "PingFang SC", sans-serif;
  font-weight: 600;
  color: #303133;
  margin: 0;
}

h1 { font-size: 24px; line-height: 32px; }
h2 { font-size: 20px; line-height: 28px; }
h3 { font-size: 16px; line-height: 24px; }
h4 { font-size: 14px; line-height: 20px; }

/* 数字字体（用于金额、数据展示） */
.number-font {
  font-family: "DIN Alternate", "Helvetica Neue", Helvetica, Arial, sans-serif;
  font-weight: 500;
  letter-spacing: 0.5px;
}
```

### 1.4 图标规范

#### 1.4.1 图标库

推荐使用 **Element Plus 内置图标** + **阿里巴巴图标库（iconfont）**：

```vue
<!-- Element Plus 内置图标引入 -->
import {
  HomeFilled,
  Goods,
  OfficeBuilding,
  Box,
  Document,
  ShoppingCart,
  DataAnalysis,
  Setting,
  Plus,
  Edit,
  Delete,
  Search,
  Refresh,
  Download,
  Upload,
  View,
  Check,
  Close,
  ArrowLeft,
  ArrowRight,
  ArrowDown,
  ArrowUp,
  User,
  UserFilled,
  Lock,
  Key,
  Message,
  Bell,
  Operation,
  List,
  Grid,
  PieChart,
  LineChart,
  BarChart,
} from '@element-plus/icons-vue'
```

#### 1.4.2 图标尺寸规范

| 场景 | 尺寸 | 颜色 | 说明 |
|------|------|------|------|
| 导航菜单图标 | 18px | 继承文字色 | 侧边栏菜单 |
| 按钮图标 | 14px / 16px | 继承按钮文字色 | 按钮内嵌图标 |
| 表格操作图标 | 16px | #606266 / #1A5C9E | 编辑、删除、查看等 |
| 状态图标 | 16px | 语义对应色 | 成功、警告、错误等 |
| 空状态图标 | 64px | #C0C4CC | 无数据插画 |

---

## 第二部分：全局组件样式规范

### 2.1 按钮组件（Button）

#### 2.1.1 按钮类型

| 按钮类型 | 背景色 | 边框色 | 文字色 | Hover背景 | 使用场景 |
|----------|--------|--------|--------|-----------|----------|
| 主要按钮 | #1A5C9E | #1A5C9E | #FFFFFF | #1470CC | 关键操作：提交、保存、确认 |
| 默认按钮 | #FFFFFF | #DCDFE6 | #606266 | #F5F7FA | 普通操作：取消、返回 |
| 文字按钮 | 透明 | 透明 | #1A5C9E | 下划线 | 次要操作：重置、辅助链接 |
| 成功按钮 | #67C23A | #67C23A | #FFFFFF | #5DAF34 | 成功操作：启用、通过 |
| 警告按钮 | #E6A23C | #E6A23C | #FFFFFF | #D8901F | 警告操作：待处理、进行中 |
| 危险按钮 | #F56C6C | #F56C6C | #FFFFFF | #E04040 | 危险操作：删除、禁用 |

#### 2.1.2 按钮尺寸

| 尺寸 | 高度 | 内边距 | 字号 | 圆角 | 使用场景 |
|------|------|--------|------|------|----------|
| 大型 | 40px | 0 20px | 14px | 4px | 主要页面操作按钮 |
| 中型（默认） | 36px | 0 16px | 14px | 4px | 表单内按钮、列表页操作 |
| 小型 | 28px | 0 12px | 12px | 4px | 表格内操作、辅助按钮 |
| 最小 | 24px | 0 8px | 12px | 2px | 标签内嵌按钮 |

#### 2.1.3 按钮样式代码

```css
/* 按钮基础样式 */
.el-button {
  font-family: "Microsoft YaHei", sans-serif;
  font-weight: 500;
  border-radius: 4px;
  transition: all 0.3s ease;
}

/* 主要按钮 */
.el-button--primary {
  background-color: #1A5C9E;
  border-color: #1A5C9E;
}

.el-button--primary:hover {
  background-color: #1470CC;
  border-color: #1470CC;
}

.el-button--primary:active {
  background-color: #0D5CAD;
  border-color: #0D5CAD;
}

/* 圆角优化 */
.el-button {
  border-radius: 4px;
}

/* 按钮hover动画 */
.el-button:not(.is-disabled):hover {
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(26, 92, 158, 0.25);
}

.el-button:not(.is-disabled):active {
  transform: translateY(0);
  box-shadow: none;
}

/* 按钮组间距 */
.el-button-group .el-button {
  margin-right: 0;
}

.el-button-group .el-button:not(:last-child) {
  margin-right: -1px;
}
```

### 2.2 表格组件（Table）

#### 2.2.1 表格样式规范

| 元素 | 样式规范 | 色值 |
|------|----------|------|
| 表格容器 | 白色背景，圆角8px，阴影0 2px 12px rgba(0,0,0,0.1) | #FFFFFF |
| 表头背景 | 浅灰色背景 | #F5F7FA |
| 表头文字 | 14px，加粗，灰色 | #909399 |
| 表格行高 | 54px | - |
| 单元格内边距 | 12px 16px | - |
| 表格边框 | 1px实线 | #EBEEF5 |
| 斑马纹 | 交替浅灰背景（可选） | #FAFBFC / #FFFFFF |
| 行hover | 浅蓝色背景 | rgba(26, 92, 158, 0.05) |
| 行选中 | 深蓝色背景 | rgba(26, 92, 158, 0.1) |

#### 2.2.2 表格交互样式

```css
/* 表格容器 */
.el-table {
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
}

/* 表头样式 */
.el-table__header th {
  background-color: #F5F7FA !important;
  font-weight: 600;
  color: #909399;
  font-size: 14px;
  padding: 16px 0 !important;
}

/* 单元格样式 */
.el-table__body td {
  color: #606266;
  font-size: 14px;
  border-bottom: 1px solid #EBEEF5;
}

/* 行hover效果 */
.el-table__body tr:hover > td {
  background-color: rgba(26, 92, 158, 0.05) !important;
}

/* 行选中效果 */
.el-table__body tr.current-row > td {
  background-color: rgba(26, 92, 158, 0.1) !important;
}

/* 表格圆角 */
.el-table__header-wrapper {
  border-radius: 8px 8px 0 0;
}

/* 固定列样式 */
.el-table__fixed {
  box-shadow: 2px 0 8px rgba(0, 0, 0, 0.06);
}

/* 排序图标 */
.el-table .ascending .sort-caret.ascending {
  border-bottom-color: #1A5C9E;
}

.el-table .descending .sort-caret.descending {
  border-top-color: #1A5C9E;
}

/* 多选框样式 */
.el-table th.el-table__cell:nth-child(1) .cell,
.el-table td.el-table__cell:nth-child(1) .cell {
  padding-left: 16px;
  padding-right: 8px;
}

/* 操作列按钮 */
.table-action-btn {
  font-size: 14px;
  padding: 4px 8px;
  color: #1A5C9E;
}

.table-action-btn:hover {
  color: #1470CC;
  background-color: rgba(26, 92, 158, 0.1);
}

.table-action-btn.is-danger {
  color: #F56C6C;
}

.table-action-btn.is-danger:hover {
  color: #E04040;
  background-color: rgba(245, 108, 108, 0.1);
}
```

#### 2.2.3 表格分页样式

```css
/* 分页组件容器 */
.el-pagination {
  margin-top: 20px;
  padding: 16px 0;
  display: flex;
  justify-content: flex-end;
  align-items: center;
}

/* 分页按钮 */
.el-pagination button {
  border-radius: 4px;
  transition: all 0.3s;
}

.el-pagination button:hover {
  background-color: rgba(26, 92, 158, 0.1);
}

/* 页码按钮 */
.el-pagination .el-pager li {
  border-radius: 4px;
  margin: 0 2px;
  min-width: 32px;
  height: 32px;
  line-height: 32px;
  font-size: 14px;
}

.el-pagination .el-pager li:hover {
  color: #1A5C9E;
  background-color: rgba(26, 92, 158, 0.1);
}

.el-pagination .el-pager li.is-active {
  background-color: #1A5C9E;
  color: #FFFFFF;
}

/* 每页条数选择器 */
.el-pagination__sizes {
  margin-right: 16px;
}

.el-pagination__sizes .el-input__wrapper {
  border-radius: 4px;
}

/* 总条数显示 */
.el-pagination__total {
  margin-right: 16px;
  color: #909399;
  font-size: 14px;
}

/* 跳转到某页 */
.el-pagination__jump {
  margin-left: 16px;
}
```

### 2.3 表单组件（Form）

#### 2.3.1 输入框样式

| 元素 | 样式规范 | 色值 |
|------|----------|------|
| 输入框高度 | 36px | - |
| 边框圆角 | 4px | - |
| 边框色 | 1px solid #DCDFE6 | #DCDFE6 |
| 边框hover | 1px solid #C0C4CC | #C0C4CC |
| 边框focus | 1px solid #1A5C9E | #1A5C9E |
| 背景色 | #FFFFFF | #FFFFFF |
| 文字色 | #606266 | #606266 |
| placeholder色 | #C0C4CC | #C0C4CC |
| 禁用背景 | #F5F7FA | #F5F7FA |
| 禁用文字 | #C0C4CC | #C0C4CC |

```css
/* 输入框基础样式 */
.el-input__wrapper {
  border-radius: 4px;
  box-shadow: none;
  transition: all 0.3s ease;
}

.el-input__wrapper:hover {
  box-shadow: none;
}

.el-input__wrapper.is-focus {
  box-shadow: 0 0 0 1px #1A5C9E inset;
}

.el-input__inner {
  font-family: "Microsoft YaHei", sans-serif;
  color: #606266;
}

.el-input__inner::placeholder {
  color: #C0C4CC;
}

/* 输入框禁用状态 */
.el-input.is-disabled .el-input__wrapper {
  background-color: #F5F7FA;
  cursor: not-allowed;
}

.el-input.is-disabled .el-input__inner {
  color: #C0C4CC;
  cursor: not-allowed;
}

/* 带前缀/后缀图标 */
.el-input__prefix,
.el-input__suffix {
  color: #909399;
}

/* 文本域 */
.el-textarea__inner {
  font-family: "Microsoft YaHei", sans-serif;
  border-radius: 4px;
  resize: vertical;
  min-height: 80px !important;
}

.el-textarea__inner:focus {
  box-shadow: 0 0 0 1px #1A5C9E inset;
}
```

#### 2.3.2 下拉选择器样式

| 元素 | 样式规范 | 色值 |
|------|----------|------|
| 选择器高度 | 36px | - |
| 边框样式 | 同输入框 | - |
| 下拉箭头 | #909399 | - |
| 选项hover | 浅蓝背景 | rgba(26, 92, 158, 0.08) |
| 选项选中 | 深蓝背景 | #1A5C9E |
| 下拉面板 | 白色背景，阴影，圆角 | - |

```css
/* 下拉选择器 */
.el-select__wrapper {
  border-radius: 4px;
  box-shadow: none !important;
}

.el-select__wrapper:hover {
  box-shadow: none !important;
}

.el-select__wrapper.is-focused {
  box-shadow: 0 0 0 1px #1A5C9E inset !important;
}

/* 下拉面板 */
.el-select-dropdown__item {
  font-size: 14px;
  padding: 8px 12px;
}

.el-select-dropdown__item:hover {
  background-color: rgba(26, 92, 158, 0.08);
}

.el-select-dropdown__item.is-selected {
  color: #1A5C9E;
  font-weight: 600;
  background-color: rgba(26, 92, 158, 0.12);
}

/* 多选标签 */
.el-select__tags {
  flex-wrap: nowrap;
  overflow-x: auto;
}

.el-select__tags .el-tag {
  margin: 2px 4px 2px 0;
  border-radius: 4px;
}
```

#### 2.3.3 日期选择器样式

```css
/* 日期选择器 */
.el-date-editor {
  border-radius: 4px;
}

.el-date-editor:hover {
  box-shadow: none;
}

.el-date-editor.is-active {
  box-shadow: 0 0 0 1px #1A5C9E inset;
}

/* 日期面板 */
.el-date-picker {
  border-radius: 8px;
  box-shadow: 0 4px 24px rgba(0, 0, 0, 0.15);
}

/* 日期范围选择器 */
.el-range-separator {
  color: #909399;
}

/* 当前日期高亮 */
.el-date-table td.today .el-date-table-cell__text {
  color: #1A5C9E;
  font-weight: 600;
}

/* 选中日期 */
.el-date-table td.current:not(.disabled) .el-date-table-cell__text {
  background-color: #1A5C9E;
}

/* 周标题 */
.el-date-table th {
  color: #909399;
  font-weight: 500;
}
```

#### 2.3.4 表单标签布局

```css
/* 表单容器 */
.el-form {
  padding: 20px;
}

/* 表单项 */
.el-form-item {
  margin-bottom: 22px;
}

/* 表单标签 */
.el-form-item__label {
  font-size: 14px;
  font-weight: 500;
  color: #606266;
  line-height: 36px;
  padding-right: 12px;
}

/* 必填星号 */
.el-form-item__label::before {
  color: #F56C6C;
  margin-right: 4px;
}

/* 表单验证错误 */
.el-form-item.is-error .el-input__wrapper {
  box-shadow: 0 0 0 1px #F56C6C inset;
}

.el-form-item__error {
  color: #F56C6C;
  font-size: 12px;
  padding-top: 4px;
}

/* 行内表单 */
.el-form--inline .el-form-item {
  display: inline-block;
  margin-right: 16px;
  vertical-align: top;
}

/* 表单分组标题 */
.form-section-title {
  font-size: 16px;
  font-weight: 600;
  color: #303133;
  margin-bottom: 16px;
  padding-bottom: 8px;
  border-bottom: 2px solid #1A5C9E;
}
```

### 2.4 弹窗组件（Dialog）

#### 2.4.1 弹窗基础样式

| 元素 | 样式规范 | 色值 |
|------|----------|------|
| 弹窗宽度 | 宽度自适应，最大800px | - |
| 圆角 | 8px | - |
| 标题背景 | 白色 | #FFFFFF |
| 标题文字 | 18px，加粗 | #303133 |
| 标题高度 | 56px | - |
| 关闭按钮 | 右上角，20px | #909399 |
| 关闭按钮hover | #303133 | - |
| 内容区背景 | 白色 | #FFFFFF |
| 按钮区背景 | 浅灰 | #FAFAFA |
| 遮罩层 | 半透明深灰 | rgba(0, 0, 0, 0.5) |

```css
/* 弹窗容器 */
.el-dialog {
  border-radius: 8px;
  overflow: hidden;
}

/* 标题区域 */
.el-dialog__header {
  padding: 20px 24px 16px;
  border-bottom: 1px solid #EBEEF5;
  margin-right: 0;
}

.el-dialog__title {
  font-size: 18px;
  font-weight: 600;
  color: #303133;
}

/* 关闭按钮 */
.el-dialog__headerbtn {
  top: 20px;
  right: 20px;
  width: 24px;
  height: 24px;
}

.el-dialog__headerbtn .el-dialog__close {
  color: #909399;
  font-size: 18px;
  transition: color 0.3s;
}

.el-dialog__headerbtn:hover .el-dialog__close {
  color: #303133;
}

/* 内容区域 */
.el-dialog__body {
  padding: 24px;
  max-height: 60vh;
  overflow-y: auto;
}

/* 底部按钮区域 */
.el-dialog__footer {
  padding: 16px 24px;
  background-color: #FAFAFA;
  border-top: 1px solid #EBEEF5;
  text-align: right;
}

.el-dialog__footer .el-button {
  margin-left: 12px;
}

.el-dialog__footer .el-button:first-child {
  margin-left: 0;
}

/* 遮罩层 */
.el-overlay {
  background-color: rgba(0, 0, 0, 0.5);
}

/* 弹窗动画 */
.el-dialog {
  animation: dialog-fade-in 0.3s ease-out;
}

@keyframes dialog-fade-in {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* 不同尺寸弹窗 */
.el-dialog--small {
  width: 400px;
}

.el-dialog--medium {
  width: 600px;
}

.el-dialog--large {
  width: 800px;
}

.el-dialog--full {
  width: 90%;
  max-width: 1200px;
}
```

### 2.5 搜索栏组件

#### 2.5.1 搜索栏布局样式

```css
/* 搜索栏容器 */
.search-bar {
  background: #FFFFFF;
  padding: 20px;
  border-radius: 8px;
  margin-bottom: 16px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
}

/* 搜索表单布局 */
.search-form {
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
  align-items: flex-end;
}

/* 搜索表单项 */
.search-form .el-form-item {
  margin-bottom: 0;
  min-width: 200px;
}

.search-form .el-form-item__label {
  font-size: 14px;
  color: #909399;
}

/* 搜索按钮组 */
.search-actions {
  display: flex;
  gap: 8px;
  flex-shrink: 0;
}

/* 重置按钮 */
.search-actions .el-button--default {
  color: #909399;
  border-color: #DCDFE6;
}

.search-actions .el-button--default:hover {
  color: #606266;
  border-color: #C0C4CC;
  background-color: #F5F7FA;
}

/* 收起/展开搜索 */
.search-toggle {
  text-align: center;
  margin-top: 12px;
  padding-top: 12px;
  border-top: 1px dashed #EBEEF5;
}

.search-toggle-link {
  color: #1A5C9E;
  font-size: 14px;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 4px;
}

.search-toggle-link:hover {
  color: #1470CC;
}

.search-toggle-link .el-icon {
  transition: transform 0.3s;
}

.search-toggle-link.is-expand .el-icon {
  transform: rotate(180deg);
}
```

### 2.6 侧边栏组件（Sidebar）

#### 2.6.1 侧边栏基础样式

| 元素 | 样式规范 | 色值 |
|------|----------|------|
| 背景色 | 深蓝灰色 | #304156 |
| 宽度 | 240px（展开）/ 64px（折叠） | - |
| Logo区高度 | 60px | - |
| 菜单项高度 | 56px | - |
| 一级菜单文字 | 16px | #FFFFFF |
| 二级菜单文字 | 14px | rgba(255,255,255,0.7) |
| 选中菜单背景 | 深色强调 | rgba(255,255,255,0.1) |
| 选中菜单文字 | 白色 | #FFFFFF |
| Hover背景 | 浅色强调 | rgba(255,255,255,0.08) |
| 图标颜色 | 继承文字色 | - |

#### 2.6.2 侧边栏样式代码

```css
/* 侧边栏容器 */
.app-sidebar {
  width: 240px;
  height: 100vh;
  background-color: #304156;
  transition: width 0.3s ease;
  overflow-x: hidden;
  overflow-y: auto;
  position: fixed;
  left: 0;
  top: 0;
  bottom: 0;
  z-index: 1001;
}

.app-sidebar.is-collapse {
  width: 64px;
}

/* Logo区域 */
.sidebar-logo {
  height: 60px;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #263445;
  padding: 0 16px;
}

.sidebar-logo img {
  height: 32px;
  margin-right: 8px;
}

.sidebar-logo span {
  font-size: 18px;
  font-weight: 600;
  color: #FFFFFF;
  white-space: nowrap;
}

/* 菜单容器 */
.el-menu {
  border-right: none;
  background-color: #304156;
}

/* 一级菜单项 */
.el-menu-item,
.el-sub-menu__title {
  height: 56px;
  line-height: 56px;
  color: rgba(255, 255, 255, 0.7);
  transition: all 0.3s;
}

.el-menu-item:hover,
.el-sub-menu__title:hover {
  background-color: rgba(255, 255, 255, 0.08) !important;
  color: #FFFFFF;
}

/* 选中菜单项 */
.el-menu-item.is-active {
  background-color: rgba(255, 255, 255, 0.12) !important;
  color: #FFFFFF;
  border-right: 3px solid #1A5C9E;
}

.el-menu-item.is-active::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 3px;
  background-color: #1A5C9E;
}

/* 菜单图标 */
.el-menu-item .el-icon,
.el-sub-menu__title .el-icon {
  font-size: 18px;
  margin-right: 12px;
  color: inherit;
}

/* 二级菜单 */
.el-menu .el-menu {
  background-color: rgba(0, 0, 0, 0.2);
}

.el-menu .el-menu-item {
  height: 50px;
  line-height: 50px;
  font-size: 14px;
  padding-left: 54px !important;
}

.el-menu .el-menu-item:hover {
  background-color: rgba(255, 255, 255, 0.06) !important;
}

.el-menu .el-menu-item.is-active {
  background-color: rgba(26, 92, 158, 0.3) !important;
  border-right: none;
}

.el-menu .el-menu-item.is-active::before {
  display: none;
}

/* 折叠状态样式 */
.app-sidebar.is-collapse .sidebar-logo span,
.app-sidebar.is-collapse .el-menu-item span,
.app-sidebar.is-collapse .el-sub-menu__title span {
  display: none;
}

.app-sidebar.is-collapse .el-menu-item,
.app-sidebar.is-collapse .el-sub-menu__title {
  text-align: center;
  padding-left: 0 !important;
  padding-right: 0 !important;
}

.app-sidebar.is-collapse .el-menu-item .el-icon,
.app-sidebar.is-collapse .el-sub-menu__title .el-icon {
  margin-right: 0;
  font-size: 20px;
}

/* 折叠时二级菜单悬浮显示 */
.el-menu--vertical.is-collapse .el-sub-menu__title {
  height: 56px;
  line-height: 56px;
}
```

### 2.7 卡片组件（Card）

```css
/* 卡片容器 */
.el-card {
  border-radius: 8px;
  border: none;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
  transition: all 0.3s ease;
}

.el-card:hover {
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
}

/* 卡片头部 */
.el-card__header {
  padding: 16px 20px;
  border-bottom: 1px solid #EBEEF5;
  font-size: 16px;
  font-weight: 600;
  color: #303133;
}

/* 卡片内容 */
.el-card__body {
  padding: 20px;
}

/* 统计卡片 */
.stat-card {
  background: #FFFFFF;
  border-radius: 8px;
  padding: 20px 24px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
  transition: all 0.3s ease;
  border-left: 4px solid #1A5C9E;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
}

.stat-card__title {
  font-size: 14px;
  color: #909399;
  margin-bottom: 12px;
}

.stat-card__value {
  font-size: 28px;
  font-weight: 600;
  color: #303133;
  font-family: "DIN Alternate", "Helvetica Neue", Helvetica, Arial, sans-serif;
}

.stat-card__trend {
  margin-top: 8px;
  font-size: 12px;
  display: flex;
  align-items: center;
  gap: 4px;
}

.stat-card__trend.is-up {
  color: #67C23A;
}

.stat-card__trend.is-down {
  color: #F56C6C;
}

.stat-card__icon {
  position: absolute;
  right: 20px;
  top: 50%;
  transform: translateY(-50%);
  font-size: 48px;
  opacity: 0.1;
  color: #1A5C9E;
}
```

### 2.8 标签组件（Tag）

```css
/* 状态标签基础样式 */
.el-tag {
  border-radius: 4px;
  padding: 0 10px;
  height: 26px;
  line-height: 24px;
  font-size: 12px;
  border: 1px solid;
}

/* 成功状态 */
.el-tag--success {
  background-color: rgba(103, 194, 58, 0.1);
  border-color: rgba(103, 194, 58, 0.3);
  color: #67C23A;
}

/* 警告状态 */
.el-tag--warning {
  background-color: rgba(230, 162, 60, 0.1);
  border-color: rgba(230, 162, 60, 0.3);
  color: #E6A23C;
}

/* 危险状态 */
.el-tag--danger {
  background-color: rgba(245, 108, 108, 0.1);
  border-color: rgba(245, 108, 108, 0.3);
  color: #F56C6C;
}

/* 信息状态 */
.el-tag--info {
  background-color: rgba(144, 147, 153, 0.1);
  border-color: rgba(144, 147, 153, 0.3);
  color: #909399;
}

/* 主色调状态 */
.el-tag--primary {
  background-color: rgba(26, 92, 158, 0.1);
  border-color: rgba(26, 92, 158, 0.3);
  color: #1A5C9E;
}
```

---

## 第三部分：核心页面UI设计方案

### 3.1 登录页（Login Page）

#### 3.1.1 页面布局

```
┌──────────────────────────────────────────────────────────────────┐
│                                                                  │
│                    ┌──────────────────────────┐                 │
│                    │                          │                 │
│                    │     [ 系统 Logo ]         │                 │
│                    │                          │                 │
│                    │   化工单据管理系统          │                 │
│                    │                          │                 │
│                    │   用户名：[____________] │                 │
│                    │                          │                 │
│                    │   密  码：[____________] │                 │
│                    │                          │                 │
│                    │   ☐ 记住密码              │                 │
│                    │                          │                 │
│                    │   [    登  录    ]       │                 │
│                    │                          │                 │
│                    └──────────────────────────┘                 │
│                                                                  │
│                         © 2026 化工单据管理系统                   │
└──────────────────────────────────────────────────────────────────┘
```

#### 3.1.2 视觉设计规范

| 元素 | 样式说明 | 规范值 |
|------|----------|--------|
| 页面背景 | 渐变色背景，从深蓝到浅蓝 | 背景渐变：linear-gradient(135deg, #1A5C9E 0%, #409EFF 100%) |
| 登录卡片 | 白色背景，居中显示 | 宽度：420px，内边距：48px，圆角：12px，阴影：0 8px 32px rgba(0,0,0,0.3) |
| Logo | 系统标识图 | 尺寸：64px × 64px，居中 |
| 标题文字 | 系统名称 | 字号：24px，字重：600，颜色：#303133 |
| 用户名输入框 | 带图标输入框 | 高度：44px，圆角：8px |
| 密码输入框 | 带图标密码框 | 高度：44px，圆角：8px |
| 登录按钮 | 主色渐变按钮 | 宽度：100%，高度：44px，圆角：8px，背景：#1A5C9E |
| 版权信息 | 底部居中 | 字号：12px，颜色：rgba(255,255,255,0.6) |

#### 3.1.3 交互样式

| 交互状态 | 样式效果 |
|----------|----------|
| 输入框聚焦 | 边框色变为#1A5C9E，添加光晕效果 |
| 登录按钮hover | 背景色加深至#1470CC，轻微上移2px，阴影加深 |
| 登录按钮点击 | 背景色变为#0D5CAD，缩小至0.98 |
| 登录按钮加载中 | 显示loading图标，文字变为"登录中..." |
| 表单验证失败 | 输入框边框变红，显示错误提示 |

#### 3.1.4 Vue3 + Element Plus代码示例

```vue
<template>
  <div class="login-container">
    <div class="login-bg">
      <div class="login-card">
        <div class="login-header">
          <div class="login-logo">
            <img src="@/assets/logo.svg" alt="logo" />
          </div>
          <h1 class="login-title">化工单据管理系统</h1>
          <p class="login-subtitle">Chemical Document Management System</p>
        </div>
        
        <el-form
          ref="loginFormRef"
          :model="loginForm"
          :rules="loginRules"
          class="login-form"
          @submit.prevent="handleLogin"
        >
          <el-form-item prop="username">
            <el-input
              v-model="loginForm.username"
              placeholder="请输入用户名"
              size="large"
              prefix-icon="User"
              clearable
            />
          </el-form-item>
          
          <el-form-item prop="password">
            <el-input
              v-model="loginForm.password"
              type="password"
              placeholder="请输入密码"
              size="large"
              prefix-icon="Lock"
              show-password
              @keyup.enter="handleLogin"
            />
          </el-form-item>
          
          <el-form-item>
            <div class="login-options">
              <el-checkbox v-model="loginForm.remember">记住密码</el-checkbox>
              <a href="#" class="forgot-link">忘记密码？</a>
            </div>
          </el-form-item>
          
          <el-form-item>
            <el-button
              type="primary"
              size="large"
              :loading="loading"
              class="login-button"
              @click="handleLogin"
            >
              {{ loading ? '登录中...' : '登 录' }}
            </el-button>
          </el-form-item>
        </el-form>
      </div>
      
      <p class="login-footer">© 2026 化工单据管理系统 版权所有</p>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { ElMessage } from 'element-plus'

const loginFormRef = ref(null)
const loading = ref(false)

const loginForm = reactive({
  username: '',
  password: '',
  remember: false
})

const loginRules = {
  username: [
    { required: true, message: '请输入用户名', trigger: 'blur' },
    { min: 3, max: 20, message: '用户名长度为3-20个字符', trigger: 'blur' }
  ],
  password: [
    { required: true, message: '请输入密码', trigger: 'blur' },
    { min: 6, max: 20, message: '密码长度为6-20个字符', trigger: 'blur' }
  ]
}

const handleLogin = async () => {
  if (!loginFormRef.value) return
  
  await loginFormRef.value.validate((valid) => {
    if (valid) {
      loading.value = true
      // 登录逻辑
      setTimeout(() => {
        loading.value = false
        ElMessage.success('登录成功')
      }, 1000)
    }
  })
}
</script>

<style scoped>
.login-container {
  min-height: 100vh;
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.login-bg {
  width: 100%;
  min-height: 100vh;
  background: linear-gradient(135deg, #1A5C9E 0%, #409EFF 100%);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  position: relative;
}

.login-card {
  width: 420px;
  background: #FFFFFF;
  border-radius: 12px;
  padding: 48px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
  animation: card-appear 0.5s ease-out;
}

@keyframes card-appear {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.login-header {
  text-align: center;
  margin-bottom: 32px;
}

.login-logo {
  margin-bottom: 16px;
}

.login-logo img {
  width: 64px;
  height: 64px;
}

.login-title {
  font-size: 24px;
  font-weight: 600;
  color: #303133;
  margin: 0 0 8px 0;
}

.login-subtitle {
  font-size: 14px;
  color: #909399;
  margin: 0;
}

.login-form {
  width: 100%;
}

.login-options {
  width: 100%;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.forgot-link {
  color: #1A5C9E;
  font-size: 14px;
  text-decoration: none;
}

.forgot-link:hover {
  color: #1470CC;
}

.login-button {
  width: 100%;
  font-size: 16px;
  font-weight: 600;
  letter-spacing: 4px;
  height: 44px;
}

.login-footer {
  position: absolute;
  bottom: 24px;
  color: rgba(255, 255, 255, 0.6);
  font-size: 12px;
}
</style>
```

### 3.2 首页工作台（Dashboard）

#### 3.2.1 页面布局

```
┌──────────────────────────────────────────────────────────────────────┐
│ 顶部导航栏  Logo | 系统名称                      用户头像 | 张三 | 退出 │
├────────┬─────────────────────────────────────────────────────────────┤
│        │  面包屑：首页                                                    │
│  侧    ├─────────────────────────────────────────────────────────────┤
│  边    │                                                              │
│  栏    │  欢迎回来，张三！                          2026年5月7日 星期四   │
│        │                                                              │
│  菜    │  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐        │
│  单    │  │ 💰        │ │ 📋        │ │ 👥        │ │ 📈        │        │
│        │  │本月销售额   │ │本月订单数   │ │成交客户数   │ │新增客户数   │        │
│ 首页   │  │ ¥1,280,000│ │    128    │ │    45     │ │    12     │        │
│ ────   │  │ ↑12.5%   │ │ ↑8.2%    │ │ ↑5.3%    │ │ ↑15.0%   │        │
│ 产品   │  └──────────┘ └──────────┘ └──────────┘ └──────────┘        │
│  ├分类 │                                                              │
│  ├产品 │  ┌─────────────────────────────┐ ┌─────────────────────┐  │
│ 客户   │  │       销售趋势图              │ │    订单状态分布        │  │
│  ├列表 │  │       （折线图）              │ │    （饼图）            │  │
│  ├联系 │  │                             │ │                     │  │
│  └跟进 │  └─────────────────────────────┘ └─────────────────────┘  │
│ 供应   │                                                              │
│ 订单   │  ┌─────────────────────────────┐ ┌─────────────────────┐  │
│ 采购   │  │       产品销售排行            │ │    待办事项            │  │
│ 统计   │  │       （柱状图）              │ │  ☐ 待确认订单 5条      │  │
│ 系统   │  │                             │ │  ☐ 待采购订单 3条      │  │
│        │  │                             │ │  ☐ 待跟进客户 8条      │  │
│        │  └─────────────────────────────┘ └─────────────────────┘  │
│        │                                                              │
│        │  ┌──────────────────────────────────────────────────────┐  │
│        │  │                  最新订单列表                           │  │
│        │  │  订单号       客户名称     金额      状态      时间      │  │
│        │  │  ORD001    某某化工    ¥50,000   已完成   2026-05-07  │  │
│        │  │  ORD002    某某科技    ¥28,000   已发货   2026-05-06  │  │
│        │  └──────────────────────────────────────────────────────┘  │
└────────┴─────────────────────────────────────────────────────────────┘
```

#### 3.2.2 视觉设计规范

| 区域 | 元素 | 样式规范 |
|------|------|----------|
| 顶部导航栏 | 高度 | 60px，固定定位 |
| | 背景色 | #FFFFFF，白色 |
| | 阴影 | 0 2px 8px rgba(0,0,0,0.08) |
| | Logo | 左侧，高度32px |
| | 用户信息 | 右侧，头像32px圆形 |
| 侧边栏 | 宽度 | 240px |
| | 背景色 | #304156 |
| | 菜单高度 | 56px |
| | 选中标识 | 左侧3px蓝色条 |
| 统计卡片 | 布局 | 四列网格 |
| | 背景 | #FFFFFF，圆角8px |
| | 阴影 | 0 2px 12px rgba(0,0,0,0.08) |
| | 左侧边框 | 4px主色条 |
| | 图标 | 右上角，48px，透明度0.1 |
| 图表区域 | 布局 | 两列网格 |
| | 高度 | 320px |
| | 背景 | #FFFFFF，圆角8px |
| 待办事项 | 背景 | #FFFFFF，圆角8px |
| | 复选框 | 方形，主色 |
| 订单列表 | 表格样式 | 白色背景，圆角8px |

#### 3.2.3 组件样式代码

```vue
<template>
  <div class="dashboard-container">
    <!-- 欢迎区域 -->
    <div class="welcome-section">
      <div class="welcome-text">
        <h2>欢迎回来，{{ userInfo.realname }}！</h2>
        <p>今天是 {{ currentDate }}，祝您工作顺利！</p>
      </div>
    </div>
    
    <!-- 统计卡片区域 -->
    <el-row :gutter="20" class="stat-cards">
      <el-col :span="6" v-for="(stat, index) in statistics" :key="index">
        <div class="stat-card" :class="'stat-card--' + stat.type">
          <div class="stat-card__icon">
            <el-icon><component :is="stat.icon" /></el-icon>
          </div>
          <div class="stat-card__content">
            <p class="stat-card__title">{{ stat.title }}</p>
            <p class="stat-card__value">{{ stat.value }}</p>
            <p class="stat-card__trend" :class="{ 'is-up': stat.trend > 0 }">
              <el-icon v-if="stat.trend > 0"><ArrowUp /></el-icon>
              <el-icon v-else-if="stat.trend < 0"><ArrowDown /></el-icon>
              {{ Math.abs(stat.trend) }}%
            </p>
          </div>
        </div>
      </el-col>
    </el-row>
    
    <!-- 图表区域 -->
    <el-row :gutter="20" class="chart-section">
      <el-col :span="12">
        <el-card class="chart-card">
          <template #header>
            <div class="chart-header">
              <span>销售趋势</span>
              <el-radio-group v-model="chartPeriod" size="small">
                <el-radio-button label="week">近7天</el-radio-button>
                <el-radio-button label="month">近30天</el-radio-button>
                <el-radio-button label="year">近一年</el-radio-button>
              </el-radio-group>
            </div>
          </template>
          <div class="chart-container" ref="salesChartRef"></div>
        </el-card>
      </el-col>
      
      <el-col :span="12">
        <el-card class="chart-card">
          <template #header>
            <div class="chart-header">
              <span>订单状态分布</span>
            </div>
          </template>
          <div class="chart-container" ref="orderStatusChartRef"></div>
        </el-card>
      </el-col>
    </el-row>
    
    <!-- 待办和最新订单 -->
    <el-row :gutter="20" class="bottom-section">
      <el-col :span="8">
        <el-card class="todo-card">
          <template #header>
            <div class="card-header">
              <span>待办事项</span>
              <el-badge :value="todoList.length" type="primary" />
            </div>
          </template>
          <div class="todo-list">
            <div v-for="(item, index) in todoList" :key="index" class="todo-item">
              <el-checkbox v-model="item.checked">{{ item.title }}</el-checkbox>
              <span class="todo-count">{{ item.count }}条</span>
            </div>
          </div>
        </el-card>
      </el-col>
      
      <el-col :span="16">
        <el-card class="order-card">
          <template #header>
            <div class="card-header">
              <span>最新订单</span>
              <el-button type="primary" link @click="$router.push('/order/list')">
                查看更多 <el-icon><ArrowRight /></el-icon>
              </el-button>
            </div>
          </template>
          <el-table :data="latestOrders" stripe>
            <el-table-column prop="order_no" label="订单号" width="140" />
            <el-table-column prop="customer_name" label="客户名称" min-width="120" />
            <el-table-column prop="amount" label="金额" width="120" align="right">
              <template #default="{ row }">
                <span class="amount-text">¥{{ formatNumber(row.amount) }}</span>
              </template>
            </el-table-column>
            <el-table-column prop="status" label="状态" width="100">
              <template #default="{ row }">
                <el-tag :type="getStatusType(row.status)">{{ row.status_text }}</el-tag>
              </template>
            </el-table-column>
            <el-table-column prop="order_time" label="下单时间" width="120" />
          </el-table>
        </el-card>
      </el-col>
    </el-row>
  </div>
</template>

<style scoped>
.dashboard-container {
  padding: 20px;
  background-color: #F5F7FA;
  min-height: calc(100vh - 60px);
}

.welcome-section {
  margin-bottom: 24px;
}

.welcome-text h2 {
  font-size: 24px;
  font-weight: 600;
  color: #303133;
  margin: 0 0 4px 0;
}

.welcome-text p {
  font-size: 14px;
  color: #909399;
  margin: 0;
}

.stat-cards {
  margin-bottom: 20px;
}

.stat-card {
  background: #FFFFFF;
  border-radius: 8px;
  padding: 20px 24px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
  position: relative;
  overflow: hidden;
  transition: all 0.3s ease;
  border-left: 4px solid #1A5C9E;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
}

.stat-card--success { border-left-color: #67C23A; }
.stat-card--warning { border-left-color: #E6A23C; }
.stat-card--danger { border-left-color: #F56C6C; }

.stat-card__icon {
  position: absolute;
  right: 20px;
  top: 50%;
  transform: translateY(-50%);
  font-size: 48px;
  opacity: 0.1;
  color: #1A5C9E;
}

.stat-card--success .stat-card__icon { color: #67C23A; }
.stat-card--warning .stat-card__icon { color: #E6A23C; }
.stat-card--danger .stat-card__icon { color: #F56C6C; }

.stat-card__content {
  position: relative;
  z-index: 1;
}

.stat-card__title {
  font-size: 14px;
  color: #909399;
  margin: 0 0 8px 0;
}

.stat-card__value {
  font-size: 28px;
  font-weight: 600;
  color: #303133;
  margin: 0 0 8px 0;
  font-family: "DIN Alternate", "Helvetica Neue", Helvetica, Arial, sans-serif;
}

.stat-card__trend {
  font-size: 12px;
  color: #909399;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 4px;
}

.stat-card__trend.is-up {
  color: #67C23A;
}

.chart-section {
  margin-bottom: 20px;
}

.chart-card {
  border-radius: 8px;
}

.chart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.chart-header span {
  font-size: 16px;
  font-weight: 600;
  color: #303133;
}

.chart-container {
  height: 280px;
}

.bottom-section {
  margin-bottom: 20px;
}

.todo-card .card-header,
.order-card .card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.todo-card .card-header span,
.order-card .card-header span {
  font-size: 16px;
  font-weight: 600;
  color: #303133;
}

.todo-list {
  padding: 8px 0;
}

.todo-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 0;
  border-bottom: 1px solid #EBEEF5;
}

.todo-item:last-child {
  border-bottom: none;
}

.todo-count {
  font-size: 12px;
  color: #909399;
  background: #F5F7FA;
  padding: 2px 8px;
  border-radius: 4px;
}

.amount-text {
  font-family: "DIN Alternate", "Helvetica Neue", Helvetica, Arial, sans-serif;
  color: #303133;
  font-weight: 500;
}
</style>
```

### 3.3 列表页通用模板（List Page）

#### 3.3.1 页面布局

```
┌──────────────────────────────────────────────────────────────────────┐
│  面包屑：客户管理 > 客户列表                                             │
├──────────────────────────────────────────────────────────────────────┤
│                                                                      │
│  ┌────────────────────────────────────────────────────────────────┐ │
│  │ 客户名称：[______________]  行业：[全部________▼]  状态：[全部▼] │ │
│  │ 业务员：  [全部________▼]  时间：[____至____]                  │ │
│  │                                                        [重置]  │ │
│  │                                                        [搜索]  │ │
│  └────────────────────────────────────────────────────────────────┘ │
│                                                                      │
│  [ + 新增 ]    [ 导出 ]                                              │
│                                                                      │
│  ┌────────────────────────────────────────────────────────────────┐ │
│  │ ☐ │ 客户名称  │ 行业  │ 业务员 │ 等级 │ 状态 │ 创建时间  │ 操作 │ │
│  ├────────────────────────────────────────────────────────────────┤ │
│  │ ☐ │ 某某化工  │ 石油  │  张三  │ 重要 │ 正常 │2026-05-07│编辑 删除│ │
│  │ ☐ │ 某某科技  │ 精细  │  李四  │ 核心 │ 正常 │2026-05-06│编辑 删除│ │
│  │ ☐ │ 某某材料  │ 塑料  │  王五  │ 普通 │ 禁用 │2026-05-05│编辑 删除│ │
│  │ ☐ │ 某某能源  │ 化工  │  张三  │ 重要 │ 正常 │2026-05-04│编辑 删除│ │
│  └────────────────────────────────────────────────────────────────┘ │
│                                                                      │
│                                     共 128 条  < 1 2 3 4 5 6 >       │
└──────────────────────────────────────────────────────────────────────┘
```

#### 3.3.2 视觉设计规范

| 元素 | 样式说明 | 规范值 |
|------|----------|--------|
| 搜索区域 | 卡片背景，圆角8px，底部间距16px | 背景#FFFFFF，内边距20px，阴影0 2px 12px rgba(0,0,0,0.06) |
| 表单标签 | 灰色文字，14px | 颜色#909399 |
| 表单项 | 最小宽度200px，间距16px | - |
| 操作按钮行 | 顶部间距16px | - |
| 主要操作按钮 | 新增按钮，主色 | 背景#1A5C9E |
| 次要操作按钮 | 导出按钮，默认样式 | 边框#DCDFE6 |
| 数据表格 | 白色背景，圆角8px，顶部间距16px | 阴影0 2px 12px rgba(0,0,0,0.08) |
| 表格操作列 | 文字按钮样式 | 字号14px，主色文字 |
| 分页器 | 右侧对齐，顶部间距20px | - |

#### 3.3.3 Vue3代码示例

```vue
<template>
  <div class="list-page">
    <!-- 面包屑 -->
    <div class="page-breadcrumb">
      <el-breadcrumb separator="/">
        <el-breadcrumb-item :to="{ path: '/' }">首页</el-breadcrumb-item>
        <el-breadcrumb-item>客户管理</el-breadcrumb-item>
        <el-breadcrumb-item>客户列表</el-breadcrumb-item>
      </el-breadcrumb>
    </div>
    
    <!-- 搜索区域 -->
    <el-card class="search-card">
      <el-form :model="searchForm" inline>
        <el-form-item label="客户名称">
          <el-input
            v-model="searchForm.name"
            placeholder="请输入客户名称"
            clearable
            style="width: 200px"
          />
        </el-form-item>
        
        <el-form-item label="行业分类">
          <el-select
            v-model="searchForm.industry"
            placeholder="请选择行业"
            clearable
            style="width: 160px"
          >
            <el-option label="全部" value="" />
            <el-option label="石油化工" value="oil" />
            <el-option label="精细化工" value="fine" />
            <el-option label="塑料橡胶" value="plastic" />
          </el-select>
        </el-form-item>
        
        <el-form-item label="客户状态">
          <el-select
            v-model="searchForm.status"
            placeholder="请选择状态"
            clearable
            style="width: 120px"
          >
            <el-option label="全部" value="" />
            <el-option label="正常" value="1" />
            <el-option label="禁用" value="0" />
          </el-select>
        </el-form-item>
        
        <el-form-item label="所属业务员">
          <el-select
            v-model="searchForm.owner_user_id"
            placeholder="请选择业务员"
            clearable
            style="width: 140px"
          >
            <el-option label="全部" value="" />
            <el-option label="张三" value="1" />
            <el-option label="李四" value="2" />
            <el-option label="王五" value="3" />
          </el-select>
        </el-form-item>
        
        <el-form-item label="创建时间">
          <el-date-picker
            v-model="searchForm.dateRange"
            type="daterange"
            range-separator="至"
            start-placeholder="开始日期"
            end-placeholder="结束日期"
            value-format="YYYY-MM-DD"
            style="width: 240px"
          />
        </el-form-item>
        
        <el-form-item>
          <el-button @click="handleReset">重置</el-button>
          <el-button type="primary" @click="handleSearch">
            <el-icon><Search /></el-icon> 搜索
          </el-button>
        </el-form-item>
      </el-form>
    </el-card>
    
    <!-- 操作按钮 -->
    <div class="table-operations">
      <el-button type="primary" @click="handleAdd">
        <el-icon><Plus /></el-icon> 新增
      </el-button>
      <el-button @click="handleExport">
        <el-icon><Download /></el-icon> 导出
      </el-button>
    </div>
    
    <!-- 数据表格 -->
    <el-card class="table-card">
      <el-table
        ref="tableRef"
        :data="tableData"
        v-loading="loading"
        stripe
        @selection-change="handleSelectionChange"
      >
        <el-table-column type="selection" width="50" align="center" />
        
        <el-table-column
          prop="name"
          label="客户名称"
          min-width="180"
          show-overflow-tooltip
        >
          <template #default="{ row }">
            <div class="customer-name">
              <span class="name-text">{{ row.name }}</span>
              <el-tag v-if="row.level === 3" type="danger" size="small">核心</el-tag>
            </div>
          </template>
        </el-table-column>
        
        <el-table-column
          prop="industry"
          label="行业"
          width="100"
        >
          <template #default="{ row }">
            {{ getIndustryText(row.industry) }}
          </template>
        </el-table-column>
        
        <el-table-column
          prop="owner_name"
          label="业务员"
          width="100"
        />
        
        <el-table-column
          prop="level"
          label="客户等级"
          width="100"
        >
          <template #default="{ row }">
            <el-tag :type="getLevelType(row.level)" size="small">
              {{ getLevelText(row.level) }}
            </el-tag>
          </template>
        </el-table-column>
        
        <el-table-column
          prop="status"
          label="状态"
          width="80"
          align="center"
        >
          <template #default="{ row }">
            <el-switch
              v-model="row.status"
              :active-value="1"
              :inactive-value="0"
              @change="handleStatusChange(row)"
            />
          </template>
        </el-table-column>
        
        <el-table-column
          prop="create_time"
          label="创建时间"
          width="120"
        />
        
        <el-table-column
          label="操作"
          width="140"
          fixed="right"
        >
          <template #default="{ row }">
            <el-button
              type="primary"
              link
              @click="handleEdit(row)"
            >
              <el-icon><Edit /></el-icon> 编辑
            </el-button>
            <el-button
              type="danger"
              link
              @click="handleDelete(row)"
            >
              <el-icon><Delete /></el-icon> 删除
            </el-button>
          </template>
        </el-table-column>
      </el-table>
      
      <!-- 分页 -->
      <div class="table-pagination">
        <el-pagination
          v-model:current-page="pagination.page"
          v-model:page-size="pagination.pageSize"
          :total="pagination.total"
          :page-sizes="[10, 20, 50, 100]"
          layout="total, sizes, prev, pager, next, jumper"
          @size-change="handleSizeChange"
          @current-change="handleCurrentChange"
        />
      </div>
    </el-card>
    
    <!-- 编辑弹窗 -->
    <el-dialog
      v-model="dialogVisible"
      :title="dialogTitle"
      width="600px"
      @close="handleDialogClose"
    >
      <el-form
        ref="formRef"
        :model="formData"
        :rules="formRules"
        label-width="100px"
      >
        <el-form-item label="客户名称" prop="name">
          <el-input v-model="formData.name" placeholder="请输入客户名称" />
        </el-form-item>
        
        <el-form-item label="行业分类" prop="industry">
          <el-select v-model="formData.industry" placeholder="请选择行业">
            <el-option label="石油化工" value="oil" />
            <el-option label="精细化工" value="fine" />
            <el-option label="塑料橡胶" value="plastic" />
          </el-select>
        </el-form-item>
        
        <el-form-item label="所属业务员" prop="owner_user_id">
          <el-select v-model="formData.owner_user_id" placeholder="请选择业务员">
            <el-option label="张三" value="1" />
            <el-option label="李四" value="2" />
            <el-option label="王五" value="3" />
          </el-select>
        </el-form-item>
        
        <el-form-item label="客户描述" prop="description">
          <el-input
            v-model="formData.description"
            type="textarea"
            :rows="3"
            placeholder="请输入客户描述"
          />
        </el-form-item>
      </el-form>
      
      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" @click="handleSubmit">确定</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<style scoped>
.list-page {
  padding: 20px;
  background-color: #F5F7FA;
  min-height: calc(100vh - 60px);
}

.page-breadcrumb {
  margin-bottom: 16px;
}

.search-card {
  margin-bottom: 16px;
}

.search-card :deep(.el-card__body) {
  padding-bottom: 0;
}

.table-operations {
  display: flex;
  gap: 12px;
  margin-bottom: 16px;
}

.table-card {
  border-radius: 8px;
}

.customer-name {
  display: flex;
  align-items: center;
  gap: 8px;
}

.name-text {
  font-weight: 500;
  color: #303133;
}

.table-pagination {
  display: flex;
  justify-content: flex-end;
  margin-top: 20px;
  padding-top: 16px;
  border-top: 1px solid #EBEEF5;
}
</style>
```

### 3.4 新增/编辑表单页（Form Page）

#### 3.4.1 页面布局

```
┌──────────────────────────────────────────────────────────────────────┐
│  面包屑：客户管理 > 客户列表 > 新增客户                    [返回列表]  │
├──────────────────────────────────────────────────────────────────────┤
│                                                                      │
│  ┌──────────────────────────────────────────────────────────────┐   │
│  │  基本信息                                                      │   │
│  │  ───────────────────────────────────────────────────────────│   │
│  │                                                              │   │
│  │  客户名称*：  [___________________________________]           │   │
│  │                                                              │   │
│  │  客户编码：   [___________________________________]           │   │
│  │                                                              │   │
│  │  行业分类*：  [请选择行业分类________________________▼]        │   │
│  │                                                              │   │
│  │  客户来源：   [请选择客户来源________________________▼]        │   │
│  │                                                              │   │
│  │  所属业务员*：[请选择业务员________________________▼]          │   │
│  │                                                              │   │
│  └──────────────────────────────────────────────────────────────┘   │
│                                                                      │
│  ┌──────────────────────────────────────────────────────────────┐   │
│  │  联系信息                                                      │   │
│  │  ───────────────────────────────────────────────────────────│   │
│  │                                                              │   │
│  │  联系人姓名*： [___________________________________]           │   │
│  │                                                              │   │
│  │  手机号*：    [___________________________________]           │   │
│  │                                                              │   │
│  │  职位：       [___________________________________]           │   │
│  │                                                              │   │
│  │  电子邮箱：   [___________________________________]           │   │
│  │                                                              │   │
│  └──────────────────────────────────────────────────────────────┘   │
│                                                                      │
│  ┌──────────────────────────────────────────────────────────────┐   │
│  │  其他信息                                                      │   │
│  │  ───────────────────────────────────────────────────────────│   │
│  │                                                              │   │
│  │  客户描述：   [___________________________________]           │   │
│  │               （可输入多行，最多500字）                        │   │
│  │                                                              │   │
│  │  客户附件：   [点击上传文件]                                   │   │
│  │                                                              │   │
│  │  客户状态*：  ○禁用  ●启用                                    │   │
│  │                                                              │   │
│  └──────────────────────────────────────────────────────────────┘   │
│                                                                      │
├──────────────────────────────────────────────────────────────────────┤
│                              [取消]           [保存]                 │
└──────────────────────────────────────────────────────────────────────┘
```

#### 3.4.2 视觉设计规范

| 元素 | 样式说明 | 规范值 |
|------|----------|--------|
| 页面容器 | 最大宽度1200px，居中 | - |
| 表单卡片 | 白色背景，圆角8px，底部间距20px | 内边距24px |
| 表单分组标题 | 左侧蓝色竖线，16px加粗 | 左边框4px #1A5C9E |
| 表单项布局 | 两列布局，间距20px | - |
| 必填标识 | 红色星号 | 颜色#F56C6C |
| 表单按钮 | 底部居中，间距12px | - |
| 返回按钮 | 默认样式 | - |
| 保存按钮 | 主色样式 | - |

#### 3.4.3 Vue3代码示例

```vue
<template>
  <div class="form-page">
    <div class="page-breadcrumb">
      <el-breadcrumb separator="/">
        <el-breadcrumb-item :to="{ path: '/' }">首页</el-breadcrumb-item>
        <el-breadcrumb-item :to="{ path: '/customer/list' }">客户列表</el-breadcrumb-item>
        <el-breadcrumb-item>{{ isEdit ? '编辑客户' : '新增客户' }}</el-breadcrumb-item>
      </el-breadcrumb>
      <el-button class="back-button" @click="$router.back()">
        <el-icon><ArrowLeft /></el-icon> 返回列表
      </el-button>
    </div>
    
    <el-form
      ref="formRef"
      :model="formData"
      :rules="formRules"
      label-width="120px"
      class="form-container"
    >
      <!-- 基本信息 -->
      <el-card class="form-card">
        <template #header>
          <div class="form-section-header">
            <span class="section-icon"><el-icon><User /></el-icon></span>
            <span>基本信息</span>
          </div>
        </template>
        
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="客户名称" prop="name">
              <el-input
                v-model="formData.name"
                placeholder="请输入客户名称"
                maxlength="200"
                show-word-limit
              />
            </el-form-item>
          </el-col>
          
          <el-col :span="12">
            <el-form-item label="客户编码" prop="code">
              <el-input
                v-model="formData.code"
                placeholder="请输入客户编码，系统将自动生成"
              />
            </el-form-item>
          </el-col>
          
          <el-col :span="12">
            <el-form-item label="行业分类" prop="industry">
              <el-select v-model="formData.industry" placeholder="请选择行业分类">
                <el-option label="石油化工" value="oil" />
                <el-option label="精细化工" value="fine" />
                <el-option label="塑料橡胶" value="plastic" />
                <el-option label="医药化工" value="medical" />
                <el-option label="其他" value="other" />
              </el-select>
            </el-form-item>
          </el-col>
          
          <el-col :span="12">
            <el-form-item label="客户来源" prop="source">
              <el-select v-model="formData.source" placeholder="请选择客户来源">
                <el-option label="网络推广" value="internet" />
                <el-option label="老客户介绍" value="referral" />
                <el-option label="行业展会" value="exhibition" />
                <el-option label="电话营销" value="telemarketing" />
                <el-option label="自主开发" value="self" />
                <el-option label="其他" value="other" />
              </el-select>
            </el-form-item>
          </el-col>
          
          <el-col :span="12">
            <el-form-item label="所属业务员" prop="owner_user_id">
              <el-select v-model="formData.owner_user_id" placeholder="请选择所属业务员">
                <el-option
                  v-for="user in userList"
                  :key="user.id"
                  :label="user.name"
                  :value="user.id"
                />
              </el-select>
            </el-form-item>
          </el-col>
          
          <el-col :span="12">
            <el-form-item label="客户等级" prop="level">
              <el-radio-group v-model="formData.level">
                <el-radio :label="1">普通客户</el-radio>
                <el-radio :label="2">重要客户</el-radio>
                <el-radio :label="3">核心客户</el-radio>
              </el-radio-group>
            </el-form-item>
          </el-col>
          
          <el-col :span="24">
            <el-form-item label="客户地址" prop="address">
              <el-input
                v-model="formData.address"
                type="textarea"
                :rows="2"
                placeholder="请输入详细地址"
                maxlength="500"
              />
            </el-form-item>
          </el-col>
        </el-row>
      </el-card>
      
      <!-- 联系信息 -->
      <el-card class="form-card">
        <template #header>
          <div class="form-section-header">
            <span class="section-icon"><el-icon><Message /></el-icon></span>
            <span>联系信息</span>
          </div>
        </template>
        
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="联系人姓名" prop="contact_name">
              <el-input
                v-model="formData.contact_name"
                placeholder="请输入联系人姓名"
              />
            </el-form-item>
          </el-col>
          
          <el-col :span="12">
            <el-form-item label="手机号码" prop="contact_mobile">
              <el-input
                v-model="formData.contact_mobile"
                placeholder="请输入手机号码"
              />
            </el-form-item>
          </el-col>
          
          <el-col :span="12">
            <el-form-item label="职位" prop="contact_position">
              <el-input
                v-model="formData.contact_position"
                placeholder="请输入职位"
              />
            </el-form-item>
          </el-col>
          
          <el-col :span="12">
            <el-form-item label="电子邮箱" prop="contact_email">
              <el-input
                v-model="formData.contact_email"
                placeholder="请输入电子邮箱"
              />
            </el-form-item>
          </el-col>
        </el-row>
      </el-card>
      
      <!-- 附加信息 -->
      <el-card class="form-card">
        <template #header>
          <div class="form-section-header">
            <span class="section-icon"><el-icon><Document /></el-icon></span>
            <span>附加信息</span>
          </div>
        </template>
        
        <el-row :gutter="20">
          <el-col :span="24">
            <el-form-item label="客户描述" prop="description">
              <el-input
                v-model="formData.description"
                type="textarea"
                :rows="4"
                placeholder="请输入客户描述信息"
                maxlength="500"
                show-word-limit
              />
            </el-form-item>
          </el-col>
          
          <el-col :span="24">
            <el-form-item label="客户附件" prop="attachment">
              <el-upload
                ref="uploadRef"
                action="/api/upload"
                :file-list="fileList"
                :on-change="handleFileChange"
                :on-remove="handleFileRemove"
                :before-remove="beforeFileRemove"
                multiple
                accept=".jpg,.jpeg,.png,.pdf,.doc,.docx"
              >
                <el-button type="default">
                  <el-icon><Upload /></el-icon> 点击上传
                </el-button>
                <template #tip>
                  <div class="upload-tip">
                    支持 jpg、png、pdf、doc 格式，单个文件不超过10MB
                  </div>
                </template>
              </el-upload>
            </el-form-item>
          </el-col>
          
          <el-col :span="24">
            <el-form-item label="客户状态" prop="status">
              <el-switch
                v-model="formData.status"
                :active-value="1"
                :inactive-value="0"
                active-text="启用"
                inactive-text="禁用"
              />
            </el-form-item>
          </el-col>
        </el-row>
      </el-card>
      
      <!-- 提交按钮 -->
      <div class="form-actions">
        <el-button size="large" @click="$router.back()">取消</el-button>
        <el-button type="primary" size="large" :loading="submitting" @click="handleSubmit">
          {{ submitting ? '保存中...' : '保存' }}
        </el-button>
      </div>
    </el-form>
  </div>
</template>

<style scoped>
.form-page {
  padding: 20px;
  background-color: #F5F7FA;
  min-height: calc(100vh - 60px);
}

.page-breadcrumb {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.back-button {
  margin-top: -8px;
}

.form-container {
  max-width: 1000px;
}

.form-card {
  margin-bottom: 20px;
  border-radius: 8px;
}

.form-card :deep(.el-card__header) {
  padding: 16px 20px;
  background-color: #FAFAFA;
}

.form-section-header {
  display: flex;
  align-items: center;
  font-size: 16px;
  font-weight: 600;
  color: #303133;
}

.section-icon {
  margin-right: 8px;
  color: #1A5C9E;
  font-size: 18px;
}

.form-actions {
  display: flex;
  justify-content: center;
  gap: 16px;
  padding: 20px 0;
  background: #FFFFFF;
  border-radius: 8px;
}

.form-actions .el-button {
  min-width: 120px;
}

.upload-tip {
  color: #909399;
  font-size: 12px;
  margin-top: 8px;
}
</style>
```

### 3.5 订单详情页（Order Detail）

#### 3.5.1 页面布局

```
┌──────────────────────────────────────────────────────────────────────┐
│  面包屑：订单管理 > 订单列表 > 订单详情                    [返回列表]  │
├──────────────────────────────────────────────────────────────────────┤
│                                                                      │
│  ┌─────────────────────────────────┐  ┌─────────────────────────┐   │
│  │  订单信息                         │  │  订单状态                 │   │
│  │  ───────────────────────────────│  │  ───────────────────────│   │
│  │                                   │  │                          │   │
│  │  订单编号：ORD202605070001        │  │  ┌──────────────────┐    │   │
│  │  下单时间：2026-05-07 14:30      │  │  │     ● 已完成     │    │   │
│  │  客户名称：某某化工有限公司        │  │  └──────────────────┘    │   │
│  │  收货地址：上海市浦东新区...       │  │                          │   │
│  │  订单金额：¥50,000.00           │  │  当前状态：已完成           │   │
│  │  优惠金额：¥0.00                │  │  完成时间：2026-05-08     │   │
│  │  实际金额：¥50,000.00           │  │                          │   │
│  │                                   │  │  [更新履约状态]            │   │
│  └─────────────────────────────────┘  └─────────────────────────┘   │
│                                                                      │
│  ┌──────────────────────────────────────────────────────────────┐   │
│  │  订单产品明细                                                    │   │
│  │  ───────────────────────────────────────────────────────────│   │
│  │                                                              │   │
│  │  ┌────────────────────────────────────────────────────────┐ │   │
│  │  │ 产品名称    │ 规格      │ 单位 │ 单价     │ 数量 │ 小计    │ │   │
│  │  ├────────────────────────────────────────────────────────┤ │   │
│  │  │ 甲苯       │ 工业级     │ 吨   │ ¥8,000  │  5   │¥40,000 │ │   │
│  │  │ 乙醇       │ 分析纯     │ 桶   │ ¥500    │  20  │¥10,000 │ │   │
│  │  └────────────────────────────────────────────────────────┘ │   │
│  │                                                              │   │
│  │                                        订单总金额：¥50,000.00 │   │
│  └──────────────────────────────────────────────────────────────┘   │
│                                                                      │
│  ┌──────────────────────────────────────────────────────────────┐   │
│  │  关联采购单                                                    │   │
│  │  ───────────────────────────────────────────────────────────│   │
│  │  采购单号：PUR2026050701    供应商：某某供应商    金额：¥35,000│   │
│  └──────────────────────────────────────────────────────────────┘   │
│                                                                      │
│  ┌──────────────────────────────────────────────────────────────┐   │
│  │  操作记录                                                      │   │
│  │  ───────────────────────────────────────────────────────────│   │
│  │  2026-05-08 16:30  张三  订单状态变更为"已完成"                  │   │
│  │  2026-05-07 14:30  张三  订单创建                               │   │
│  └──────────────────────────────────────────────────────────────┘   │
│                                                                      │
└──────────────────────────────────────────────────────────────────────┘
```

---

## 第四部分：全局CSS样式文件

### 4.1 CSS变量定义

```css
/* 文件路径: src/styles/variables.css */

/* ========== 主题色 ========== */
:root {
  /* 主色调 */
  --color-primary: #1A5C9E;
  --color-primary-light-3: #3D7AC7;
  --color-primary-light-5: #5A94D6;
  --color-primary-light-7: #7AADE4;
  --color-primary-light-8: #9AC0EB;
  --color-primary-light-9: #BAD4F2;
  --color-primary-dark-2: #1470CC;
  
  /* 功能色 */
  --color-success: #67C23A;
  --color-warning: #E6A23C;
  --color-danger: #F56C6C;
  --color-info: #909399;
  
  /* 文字色 */
  --color-text-primary: #303133;
  --color-text-regular: #606266;
  --color-text-secondary: #909399;
  --color-text-placeholder: #C0C4CC;
  
  /* 边框色 */
  --color-border-base: #DCDFE6;
  --color-border-light: #E4E7ED;
  --color-border-lighter: #EBEEF5;
  --color-border-extra-light: #F2F6FC;
  
  /* 背景色 */
  --color-bg-white: #FFFFFF;
  --color-bg-page: #F5F7FA;
  --color-bg-secondary: #FAFAFA;
  
  /* 侧边栏 */
  --color-sidebar-bg: #304156;
  --color-sidebar-text: rgba(255, 255, 255, 0.7);
  --color-sidebar-active-bg: rgba(255, 255, 255, 0.12);
  --color-sidebar-hover-bg: rgba(255, 255, 255, 0.08);
  
  /* 圆角 */
  --border-radius-small: 2px;
  --border-radius-base: 4px;
  --border-radius-medium: 8px;
  --border-radius-large: 12px;
  
  /* 阴影 */
  --shadow-light: 0 2px 12px rgba(0, 0, 0, 0.08);
  --shadow-medium: 0 4px 16px rgba(0, 0, 0, 0.12);
  --shadow-dark: 0 8px 32px rgba(0, 0, 0, 0.3);
  
  /* 过渡 */
  --transition-base: all 0.3s ease;
  --transition-fast: all 0.2s ease;
  
  /* 间距 */
  --spacing-xs: 4px;
  --spacing-sm: 8px;
  --spacing-md: 16px;
  --spacing-lg: 24px;
  --spacing-xl: 32px;
  
  /* 字号 */
  --font-size-xs: 12px;
  --font-size-sm: 14px;
  --font-size-base: 14px;
  --font-size-md: 16px;
  --font-size-lg: 20px;
  --font-size-xl: 24px;
}
```

### 4.2 全局样式

```css
/* 文件路径: src/styles/global.css */

/* 引入字体 */
@import url('https://fonts.googleapis.com/css2?family=Microsoft+YaHei&display=swap');

/* 重置样式 */
*,
*::before,
*::after {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

html,
body {
  height: 100%;
  font-family: "Microsoft YaHei", "PingFang SC", "Hiragino Sans GB", 
               "Helvetica Neue", Helvetica, Arial, sans-serif;
  font-size: 14px;
  line-height: 1.5;
  color: var(--color-text-regular);
  background-color: var(--color-bg-page);
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

#app {
  height: 100%;
}

/* 滚动条样式 */
::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}

::-webkit-scrollbar-track {
  background: var(--color-bg-secondary);
  border-radius: 4px;
}

::-webkit-scrollbar-thumb {
  background: var(--color-border-base);
  border-radius: 4px;
  transition: background 0.3s;
}

::-webkit-scrollbar-thumb:hover {
  background: var(--color-text-secondary);
}

/* 选中文字样式 */
::selection {
  background-color: rgba(26, 92, 158, 0.2);
  color: var(--color-text-primary);
}

/* 链接样式 */
a {
  color: var(--color-primary);
  text-decoration: none;
  transition: color var(--transition-fast);
}

a:hover {
  color: var(--color-primary-light-3);
}

/* 图片样式 */
img {
  max-width: 100%;
  height: auto;
  vertical-align: middle;
}

/* 清除浮动 */
.clearfix::after {
  content: '';
  display: block;
  clear: both;
}

/* 文本截断 */
.text-ellipsis {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.text-ellipsis-2 {
  display: -webkit-box;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 2;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* 金额数字样式 */
.amount {
  font-family: "DIN Alternate", "Helvetica Neue", Helvetica, Arial, sans-serif;
  font-weight: 500;
}

/* 页面容器 */
.page-container {
  padding: 20px;
  background-color: var(--color-bg-page);
  min-height: calc(100vh - 60px);
}

/* 卡片容器 */
.card-container {
  background: var(--color-bg-white);
  border-radius: var(--border-radius-medium);
  box-shadow: var(--shadow-light);
}

/* Flex工具类 */
.flex {
  display: flex;
}

.flex-center {
  display: flex;
  align-items: center;
  justify-content: center;
}

.flex-between {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.flex-1 {
  flex: 1;
}

.gap-sm { gap: var(--spacing-sm); }
.gap-md { gap: var(--spacing-md); }
.gap-lg { gap: var(--spacing-lg); }
```

### 4.3 Element Plus主题覆盖

```css
/* 文件路径: src/styles/element-plus.css */

/* Element Plus 主题变量覆盖 */
:root {
  --el-color-primary: #1A5C9E;
  --el-color-primary-light-3: #3D7AC7;
  --el-color-primary-light-5: #5A94D6;
  --el-color-primary-light-7: #7AADE4;
  --el-color-primary-light-8: #9AC0EB;
  --el-color-primary-light-9: #BAD4F2;
  --el-color-primary-dark-2: #1470CC;
  
  --el-color-success: #67C23A;
  --el-color-warning: #E6A23C;
  --el-color-danger: #F56C6C;
  --el-color-info: #909399;
  
  --el-text-color-primary: #303133;
  --el-text-color-regular: #606266;
  --el-text-color-secondary: #909399;
  --el-text-color-placeholder: #C0C4CC;
  
  --el-border-color-base: #DCDFE6;
  --el-border-color-light: #E4E7ED;
  --el-border-color-lighter: #EBEEF5;
  --el-border-color-extra-light: #F2F6FC;
  
  --el-bg-color: #FFFFFF;
  --el-bg-color-page: #F5F7FA;
  --el-bg-color-secondary: #FAFAFA;
  
  --el-border-radius-base: 4px;
  --el-border-radius-small: 2px;
  --el-border-radius-round: 4px;
  --el-border-radius-circle: 50%;
}

/* 按钮圆角 */
.el-button {
  border-radius: var(--border-radius-base);
  transition: var(--transition-base);
}

.el-button:hover {
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(26, 92, 158, 0.25);
}

.el-button:active {
  transform: translateY(0);
  box-shadow: none;
}

/* 输入框圆角 */
.el-input__wrapper,
.el-textarea__inner,
.el-select__wrapper {
  border-radius: var(--border-radius-base);
  box-shadow: none !important;
}

.el-input__wrapper:hover,
.el-select__wrapper:hover {
  box-shadow: none !important;
}

.el-input__wrapper.is-focus,
.el-select__wrapper.is-focused {
  box-shadow: 0 0 0 1px var(--color-primary) inset !important;
}

/* 表格样式 */
.el-table {
  border-radius: var(--border-radius-medium);
  overflow: hidden;
}

.el-table__header th {
  background-color: var(--color-bg-page) !important;
  color: var(--color-text-secondary);
  font-weight: 600;
}

.el-table__body tr:hover > td {
  background-color: rgba(26, 92, 158, 0.05) !important;
}

/* 弹窗圆角 */
.el-dialog {
  border-radius: var(--border-radius-medium);
  overflow: hidden;
}

/* 卡片圆角 */
.el-card {
  border-radius: var(--border-radius-medium);
  border: none;
  box-shadow: var(--shadow-light);
}

/* 分页器 */
.el-pagination {
  justify-content: flex-end;
}

.el-pagination .el-pager li {
  border-radius: var(--border-radius-base);
}

.el-pagination .el-pager li.is-active {
  background-color: var(--color-primary);
  color: #FFFFFF;
}

/* 标签样式 */
.el-tag {
  border-radius: var(--border-radius-base);
}

/* 下拉菜单 */
.el-select-dropdown {
  border-radius: var(--border-radius-base);
  box-shadow: var(--shadow-medium);
}

.el-select-dropdown__item.is-selected {
  color: var(--color-primary);
  font-weight: 600;
}

/* 日期选择器 */
.el-date-picker {
  border-radius: var(--border-radius-medium);
  box-shadow: var(--shadow-medium);
}
```

---

## 第五部分：响应式设计规范

### 5.1 响应式断点

| 断点 | 屏幕宽度 | 布局调整 |
|------|----------|----------|
| 超小屏 | < 768px | 侧边栏收起，仅图标显示 |
| 小屏 | 768px - 992px | 侧边栏折叠，表格列简化 |
| 中屏 | 992px - 1200px | 正常布局，列宽调整 |
| 大屏 | 1200px - 1920px | 完整布局 |
| 超大屏 | > 1920px | 固定最大宽度，内容居中 |

### 5.2 响应式样式示例

```css
/* 响应式布局 */
@media screen and (max-width: 768px) {
  .app-sidebar {
    width: 64px;
  }
  
  .app-sidebar .el-menu-item span,
  .app-sidebar .el-sub-menu__title span {
    display: none;
  }
  
  .search-form .el-form-item {
    width: 100%;
    margin-bottom: 12px;
  }
  
  .stat-cards .el-col {
    width: 50%;
    margin-bottom: 16px;
  }
}

@media screen and (max-width: 1200px) {
  .table-columns-hide {
    display: none;
  }
}
```

---

## 文档结束

本文档为化工单据管理系统的完整UI设计规范，涵盖视觉风格、组件样式、页面方案等内容。前端开发人员可依据此文档进行页面开发，确保系统整体风格统一、交互一致。

### 附录：设计资源清单

| 资源类型 | 资源名称 | 说明 |
|----------|----------|------|
| 图标库 | Element Plus Icons | 系统图标 |
| 字体 | Microsoft YaHei | 中文字体 |
| 颜色规范 | 本文档第一部分 | 色值定义 |
| 组件样式 | 本文档第二部分 | Element Plus覆盖样式 |
| 页面模板 | 本文档第三部分 | Vue3代码示例 |
