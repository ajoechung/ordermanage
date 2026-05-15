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
import { ref, computed, watch, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { getCustomerFullDetail } from '@/api/modules/customer'

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

// 监听customerId变化，当customerId改变且抽屉打开时加载数据
watch(() => props.customerId, async (newVal) => {
  console.log('=== customerId变化 ===')
  console.log('newVal:', newVal)
  console.log('visible.value:', visible.value)
  if (newVal && visible.value) {
    console.log('准备加载数据...')
    await loadCustomerDetail(newVal)
  }
})

// 监听visible变化
watch(visible, async (newVal) => {
  console.log('=== visible变化 ===')
  console.log('newVal:', newVal)
  console.log('props.customerId:', props.customerId)
  if (newVal && props.customerId) {
    console.log('准备加载数据...')
    await loadCustomerDetail(props.customerId)
  } else if (!newVal) {
    console.log('清空数据...')
    currentCustomer.value = {}
    contactList.value = []
    followList.value = []
    orderList.value = []
    productList.value = []
    logList.value = []
    activeTab.value = 'basic'
  }
})

// 组件挂载时检查是否需要加载数据
onMounted(() => {
  console.log('=== 子组件挂载 ===')
  console.log('visible.value:', visible.value)
  console.log('props.customerId:', props.customerId)
  if (visible.value && props.customerId) {
    console.log('挂载时加载数据...')
    loadCustomerDetail(props.customerId)
  }
})

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
  try {
    console.log('开始加载客户详情，id:', id)
    const res = await getCustomerFullDetail(id)
    console.log('客户详情API响应:', res)
    if (res.code === 200) {
      const data = res.data
      console.log('客户详情数据:', data)
      
      // 设置客户基本信息
      currentCustomer.value = {
        customer_id: data.customer_id,
        name: data.name || '',
        code: data.code || '',
        industry: data.industry || '',
        level: data.level || 1,
        source: data.source || '',
        scale: data.scale || '',
        address: data.address || '',
        description: data.description || '',
        status: data.status || 1,
        owner_name: data.owner_name || '',
        create_time: data.create_time || '',
        update_time: data.update_time || ''
      }

      // 设置联系人数据
      contactList.value = data.contacts || []

      // 设置跟进记录
      followList.value = data.follows || []

      // 设置历史订单
      orderList.value = data.orders || []

      // 设置交易产品
      productList.value = data.products || []

      // 设置操作日志
      logList.value = data.logs || []
      console.log('客户详情加载完成:', currentCustomer.value)
    } else {
      ElMessage.error(res.msg || '获取客户详情失败')
    }
  } catch (error) {
    console.error('获取客户详情失败:', error)
    ElMessage.error('获取客户详情失败')
  }
}

const handleAddContact = () => {
  ElMessage.info('新增联系人功能待实现')
}

const handleAddFollow = () => {
  ElMessage.info('新增跟进记录功能待实现')
}

onMounted(() => {
  if (visible.value && props.customerId) {
    loadCustomerDetail(props.customerId)
  }
})

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
