<template>
  <el-drawer
    v-model="visible"
    :title="customerName"
    size="50%"
    direction="rtl"
    :destroy-on-close="true"
    class="customer-detail-drawer"
  >
    <el-tabs v-model="activeTab" class="detail-tabs">
      <el-tab-pane label="基本信息" name="basic">
        <el-card shadow="never">
          <el-descriptions :column="2" border>
            <el-descriptions-item label="客户名称">{{ currentCustomer.name || '-' }}</el-descriptions-item>
            <el-descriptions-item label="客户编码">{{ currentCustomer.code || '-' }}</el-descriptions-item>
            <el-descriptions-item label="行业">{{ currentCustomer.industry || '-' }}</el-descriptions-item>
            <el-descriptions-item label="客户等级">
              <el-tag :type="getLevelTag(currentCustomer.level)" size="small">
                {{ getLevelText(currentCustomer.level) }}
              </el-tag>
            </el-descriptions-item>
            <el-descriptions-item label="客户来源">{{ currentCustomer.source || '-' }}</el-descriptions-item>
            <el-descriptions-item label="客户规模">{{ currentCustomer.scale || '-' }}</el-descriptions-item>
            <el-descriptions-item label="状态">
              <el-tag :type="currentCustomer.status === 1 ? 'success' : 'info'" size="small">
                {{ currentCustomer.status === 1 ? '正常' : '禁用' }}
              </el-tag>
            </el-descriptions-item>
            <el-descriptions-item label="负责人">{{ currentCustomer.owner_name || '-' }}</el-descriptions-item>
            <el-descriptions-item label="详细地址" :span="2">{{ currentCustomer.address || '-' }}</el-descriptions-item>
            <el-descriptions-item label="客户描述" :span="2">{{ currentCustomer.description || '-' }}</el-descriptions-item>
            <el-descriptions-item label="创建时间">{{ currentCustomer.create_time || '-' }}</el-descriptions-item>
            <el-descriptions-item label="更新时间">{{ currentCustomer.update_time || '-' }}</el-descriptions-item>
          </el-descriptions>
        </el-card>
      </el-tab-pane>

      <el-tab-pane label="联系人" name="contact">
        <el-card shadow="never">
          <template #header>
            <div class="tab-header">
              <span>联系人列表</span>
              <el-button type="primary" link @click="handleAddContact">新增联系人</el-button>
            </div>
          </template>
          <el-table :data="contactList" stripe border>
            <el-table-column prop="name" label="姓名" width="120" />
            <el-table-column prop="position" label="职位" width="120" />
            <el-table-column prop="phone" label="手机号" width="130" />
            <el-table-column prop="email" label="邮箱" min-width="180" />
            <el-table-column prop="wechat" label="微信" width="120" />
            <el-table-column prop="is_primary" label="主联系人" width="100">
              <template #default="{ row }">
                <el-tag v-if="row.is_primary === 1" type="success" size="small">是</el-tag>
                <el-tag v-else type="info" size="small">否</el-tag>
              </template>
            </el-table-column>
            <el-table-column label="操作" width="120" fixed="right">
              <template #default="{ row }">
                <el-button type="primary" link size="small">编辑</el-button>
                <el-button type="danger" link size="small">删除</el-button>
              </template>
            </el-table-column>
          </el-table>
        </el-card>
      </el-tab-pane>

      <el-tab-pane label="跟进记录" name="follow">
        <el-card shadow="never">
          <template #header>
            <div class="tab-header">
              <span>跟进记录</span>
              <el-button type="primary" link @click="handleAddFollow">新增跟进</el-button>
            </div>
          </template>
          <el-timeline>
            <el-timeline-item
              v-for="item in followList"
              :key="item.follow_id"
              :timestamp="item.create_time"
              placement="top"
            >
              <el-card>
                <div class="follow-item">
                  <div class="follow-header">
                    <span class="follow-type">{{ getFollowTypeText(item.type) }}</span>
                    <span class="follow-user">{{ item.create_user_name }}</span>
                  </div>
                  <div class="follow-content">{{ item.content }}</div>
                  <div v-if="item.attachment" class="follow-attachment">
                    <el-link type="primary" :href="item.attachment" target="_blank">查看附件</el-link>
                  </div>
                </div>
              </el-card>
            </el-timeline-item>
          </el-timeline>
          <el-empty v-if="followList.length === 0" description="暂无跟进记录" />
        </el-card>
      </el-tab-pane>

      <el-tab-pane label="历史订单" name="order">
        <el-card shadow="never">
          <el-table :data="orderList" stripe border>
            <el-table-column prop="order_no" label="订单号" width="160" />
            <el-table-column prop="customer_name" label="客户名称" min-width="150" />
            <el-table-column prop="total_amount" label="订单金额" width="120" align="right">
              <template #default="{ row }">
                ¥{{ Number(row.total_amount || 0).toLocaleString() }}
              </template>
            </el-table-column>
            <el-table-column prop="status" label="状态" width="100">
              <template #default="{ row }">
                <el-tag :type="getOrderStatusTag(row.status)" size="small">
                  {{ getOrderStatusText(row.status) }}
                </el-tag>
              </template>
            </el-table-column>
            <el-table-column prop="create_time" label="创建时间" width="160" />
            <el-table-column label="操作" width="120" fixed="right">
              <template #default="{ row }">
                <el-button type="primary" link size="small">查看详情</el-button>
              </template>
            </el-table-column>
          </el-table>
          <el-empty v-if="orderList.length === 0" description="暂无订单" />
        </el-card>
      </el-tab-pane>

      <el-tab-pane label="交易产品" name="product">
        <el-card shadow="never">
          <el-table :data="productList" stripe border>
            <el-table-column prop="product_name" label="产品名称" min-width="180" />
            <el-table-column prop="spec" label="规格" width="150" />
            <el-table-column prop="unit" label="单位" width="80" />
            <el-table-column prop="price" label="单价" width="120" align="right">
              <template #default="{ row }">
                ¥{{ Number(row.price || 0).toLocaleString() }}
              </template>
            </el-table-column>
            <el-table-column prop="quantity" label="交易数量" width="100" align="center" />
            <el-table-column prop="total_amount" label="交易金额" width="120" align="right">
              <template #default="{ row }">
                ¥{{ Number(row.total_amount || 0).toLocaleString() }}
              </template>
            </el-table-column>
            <el-table-column prop="last_trade_time" label="最后交易时间" width="160" />
          </el-table>
          <el-empty v-if="productList.length === 0" description="暂无交易产品" />
        </el-card>
      </el-tab-pane>

      <el-tab-pane label="操作日志" name="log">
        <el-card shadow="never">
          <el-table :data="logList" stripe border>
            <el-table-column prop="action" label="操作类型" width="120" />
            <el-table-column prop="description" label="操作内容" min-width="200" />
            <el-table-column prop="create_user_name" label="操作人" width="120" />
            <el-table-column prop="create_time" label="操作时间" width="160" />
          </el-table>
          <el-empty v-if="logList.length === 0" description="暂无操作日志" />
        </el-card>
      </el-tab-pane>
    </el-tabs>
  </el-drawer>
</template>

<script setup>
import { ref, computed } from 'vue'
import { ElMessage } from 'element-plus'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  customerId: {
    type: [Number, String],
    default: null
  }
})

const emit = defineEmits(['update:modelValue'])

const visible = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val)
})

const activeTab = ref('basic')
const currentCustomer = ref({})
const contactList = ref([])
const followList = ref([])
const orderList = ref([])
const productList = ref([])
const logList = ref([])

const customerName = computed(() => currentCustomer.value.name || '客户详情')

const getLevelText = (level) => ({ 1: '普通', 2: '重要', 3: '核心' }[level] || '未知')
const getLevelTag = (level) => ({ 1: '', 2: 'warning', 3: 'danger' }[level] || 'info')

const getFollowTypeText = (type) => {
  const typeMap = { 1: '电话', 2: '微信', 3: '邮件', 4: '面谈', 5: '其他' }
  return typeMap[type] || '其他'
}

const getOrderStatusText = (status) => {
  const statusMap = { 0: '草稿', 1: '待审核', 2: '已审核', 3: '已发货', 4: '已完成', 5: '已取消' }
  return statusMap[status] || '未知'
}

const getOrderStatusTag = (status) => {
  const tagMap = { 0: 'info', 1: 'warning', 2: 'primary', 3: 'success', 4: 'success', 5: 'danger' }
  return tagMap[status] || 'info'
}

const loadCustomerDetail = async (id) => {
  // 这里可以调用API获取完整的客户详情，包括关联数据
  // 暂时使用模拟数据
  currentCustomer.value = {
    customer_id: id,
    name: '化工科技有限公司',
    code: 'CUST001',
    industry: '化工',
    level: 3,
    source: '搜索引擎',
    scale: '100-500人',
    address: '北京市朝阳区科技园1号楼',
    description: '这是一家专注于化工产品研发的企业，具有丰富的行业经验和技术实力。',
    status: 1,
    owner_name: '张三',
    create_time: '2024-01-15 09:30:00',
    update_time: '2024-05-10 14:20:00'
  }

  // 联系人数据
  contactList.value = [
    { contact_id: 1, name: '李四', position: '采购经理', phone: '13800138000', email: 'lisi@example.com', wechat: 'lisi_wx', is_primary: 1 },
    { contact_id: 2, name: '王五', position: '技术总监', phone: '13800138001', email: 'wangwu@example.com', wechat: 'wangwu_wx', is_primary: 0 }
  ]

  // 跟进记录
  followList.value = [
    {
      follow_id: 1,
      type: 1,
      content: '初步沟通客户需求，客户对我们的产品表示有兴趣，约定下周进行详细演示。',
      attachment: null,
      create_user_name: '张三',
      create_time: '2024-05-08 10:30:00'
    },
    {
      follow_id: 2,
      type: 4,
      content: '进行产品演示，客户对产品功能很满意，正在对比竞品，需要进一步跟进。',
      attachment: '/uploads/demo.pdf',
      create_user_name: '张三',
      create_time: '2024-05-12 14:00:00'
    }
  ]

  // 历史订单
  orderList.value = [
    { order_id: 1, order_no: 'ORD202405001', customer_name: '化工科技有限公司', total_amount: 50000, status: 4, create_time: '2024-05-05 11:20:00' },
    { order_id: 2, order_no: 'ORD202404008', customer_name: '化工科技有限公司', total_amount: 35000, status: 4, create_time: '2024-04-20 09:15:00' }
  ]

  // 交易产品
  productList.value = [
    { product_id: 1, product_name: '工业级氢氧化钠', spec: '99% 25kg/袋', unit: '袋', price: 1200, quantity: 50, total_amount: 60000, last_trade_time: '2024-05-05 11:20:00' },
    { product_id: 2, product_name: '盐酸标准溶液', spec: '1mol/L 500ml/瓶', unit: '瓶', price: 80, quantity: 100, total_amount: 8000, last_trade_time: '2024-04-20 09:15:00' },
    { product_id: 3, product_name: '无水乙醇', spec: '99.7% 5L/桶', unit: '桶', price: 350, quantity: 30, total_amount: 10500, last_trade_time: '2024-03-15 14:30:00' }
  ]

  // 操作日志
  logList.value = [
    { log_id: 1, action: '新增客户', description: '创建客户：化工科技有限公司', create_user_name: '张三', create_time: '2024-01-15 09:30:00' },
    { log_id: 2, action: '更新客户', description: '修改客户信息：客户规模从50-100人更新为100-500人', create_user_name: '李四', create_time: '2024-03-20 15:45:00' },
    { log_id: 3, action: '添加联系人', description: '新增联系人：李四（采购经理）', create_user_name: '张三', create_time: '2024-04-10 10:20:00' }
  ]
}

const handleAddContact = () => {
  ElMessage.info('新增联系人功能待实现')
}

const handleAddFollow = () => {
  ElMessage.info('新增跟进记录功能待实现')
}

defineExpose({
  loadCustomerDetail
})
</script>

<style scoped>
.detail-tabs {
  height: 100%;
}

.detail-tabs :deep(.el-tabs__content) {
  padding-top: 20px;
}

.tab-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.follow-item {
  padding: 8px 0;
}

.follow-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 8px;
}

.follow-type {
  font-weight: 600;
  color: #409eff;
}

.follow-user {
  color: #909399;
  font-size: 14px;
}

.follow-content {
  color: #303133;
  line-height: 1.6;
  margin-bottom: 8px;
}

.follow-attachment {
  margin-top: 8px;
}
</style>
