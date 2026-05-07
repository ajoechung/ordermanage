<template>
  <div class="dashboard-container">
    <div class="dashboard-header">
      <div class="header-content">
        <div class="welcome-section">
          <h1 class="welcome-title">
            <span class="greeting">{{ greeting }}</span>
            <span class="username">{{ userStore.nickname || userStore.username }}</span>
          </h1>
          <p class="welcome-subtitle">{{ currentDate }} · 祝您工作愉快</p>
        </div>
        <div class="header-actions">
          <el-button type="primary" plain @click="refreshData">
            <el-icon><Refresh /></el-icon>
            刷新数据
          </el-button>
        </div>
      </div>
    </div>

    <div class="stats-grid">
      <div
        v-for="(stat, index) in statistics"
        :key="index"
        class="stat-card"
        :class="stat.class"
        v-loading="loading"
      >
        <div class="stat-icon-wrapper">
          <div class="stat-icon-bg"></div>
          <el-icon class="stat-icon"><component :is="stat.icon" /></el-icon>
        </div>
        <div class="stat-content">
          <p class="stat-label">{{ stat.label }}</p>
          <div class="stat-value-row">
            <span class="stat-value">{{ stat.value }}</span>
            <span v-if="stat.change" class="stat-change" :class="stat.changeType">
              <el-icon><component :is="stat.changeType === 'up' ? 'Top' : 'Bottom'" /></el-icon>
              {{ stat.change }}%
            </span>
          </div>
          <p class="stat-description">{{ stat.description }}</p>
        </div>
      </div>
    </div>

    <div class="charts-section">
      <el-row :gutter="24">
        <el-col :span="16">
          <el-card class="chart-card main-chart" shadow="hover">
            <template #header>
              <div class="card-header">
                <div class="header-title">
                  <span class="title-dot"></span>
                  <span>销售趋势</span>
                </div>
                <el-radio-group v-model="salesTrendType" size="small" @change="loadSalesTrend">
                  <el-radio-button label="week">本周</el-radio-button>
                  <el-radio-button label="month">本月</el-radio-button>
                  <el-radio-button label="year">本年</el-radio-button>
                </el-radio-group>
              </div>
            </template>
            <div ref="salesChartRef" class="chart-container"></div>
          </el-card>
        </el-col>
        <el-col :span="8">
          <el-card class="chart-card side-chart" shadow="hover">
            <template #header>
              <div class="card-header">
                <div class="header-title">
                  <span class="title-dot"></span>
                  <span>客户行业分布</span>
                </div>
              </div>
            </template>
            <div ref="customerChartRef" class="chart-container"></div>
          </el-card>
        </el-col>
      </el-row>
    </div>

    <div class="tables-section">
      <el-row :gutter="24">
        <el-col :span="12">
          <el-card class="list-card" shadow="hover">
            <template #header>
              <div class="card-header">
                <div class="header-title">
                  <span class="title-dot warning"></span>
                  <span>待处理订单</span>
                </div>
                <el-badge :value="pendingOrders.length" :hidden="pendingOrders.length === 0">
                  <el-button type="primary" link @click="$router.push('/order/list')">
                    查看更多
                    <el-icon><ArrowRight /></el-icon>
                  </el-button>
                </el-badge>
              </div>
            </template>
            <div class="table-list">
              <div
                v-for="order in pendingOrders"
                :key="order.id"
                class="list-item"
                @click="handleOrderClick(order)"
              >
                <div class="item-main">
                  <span class="item-title">{{ order.order_no }}</span>
                  <el-tag :type="getOrderStatusType(order.status)" size="small">
                    {{ getOrderStatusText(order.status) }}
                  </el-tag>
                </div>
                <div class="item-sub">
                  <span class="item-info">
                    <el-icon><User /></el-icon>
                    {{ order.customer_name }}
                  </span>
                  <span class="item-amount">¥{{ Number(order.total_amount || 0).toLocaleString() }}</span>
                </div>
              </div>
              <el-empty v-if="pendingOrders.length === 0" description="暂无待处理订单" />
            </div>
          </el-card>
        </el-col>
        <el-col :span="12">
          <el-card class="list-card" shadow="hover">
            <template #header>
              <div class="card-header">
                <div class="header-title">
                  <span class="title-dot success"></span>
                  <span>最近跟进</span>
                </div>
                <el-button type="primary" link @click="$router.push('/customer/follow')">
                  查看更多
                  <el-icon><ArrowRight /></el-icon>
                </el-button>
              </div>
            </template>
            <div class="table-list">
              <div
                v-for="follow in recentFollows"
                :key="follow.id"
                class="list-item"
                @click="handleFollowClick(follow)"
              >
                <div class="item-main">
                  <span class="item-title">{{ follow.customer_name }}</span>
                  <el-tag type="info" size="small">
                    {{ getFollowTypeText(follow.follow_type) }}
                  </el-tag>
                </div>
                <div class="item-sub">
                  <span class="item-info item-content">{{ follow.content }}</span>
                </div>
              </div>
              <el-empty v-if="recentFollows.length === 0" description="暂无跟进记录" />
            </div>
          </el-card>
        </el-col>
      </el-row>
    </div>

    <div class="logs-section">
      <el-card class="log-card" shadow="hover">
        <template #header>
          <div class="card-header">
            <div class="header-title">
              <span class="title-dot info"></span>
              <span>操作日志</span>
            </div>
            <el-button type="primary" link @click="$router.push('/log/list')">
              查看全部
              <el-icon><ArrowRight /></el-icon>
            </el-button>
          </div>
        </template>
        <el-table :data="operationLogs" stripe style="width: 100%">
          <el-table-column prop="username" label="操作人" width="120">
            <template #default="{ row }">
              <div class="user-cell">
                <el-avatar :size="28" :src="row.avatar">
                  {{ row.username?.charAt(0) }}
                </el-avatar>
                <span>{{ row.username }}</span>
              </div>
            </template>
          </el-table-column>
          <el-table-column prop="module" label="模块" width="120">
            <template #default="{ row }">
              <el-tag size="small" effect="plain">{{ getModuleText(row.module) }}</el-tag>
            </template>
          </el-table-column>
          <el-table-column prop="action" label="操作" width="100">
            <template #default="{ row }">
              <span class="action-text">{{ getActionText(row.action) }}</span>
            </template>
          </el-table-column>
          <el-table-column prop="description" label="描述" show-overflow-tooltip />
          <el-table-column prop="ip" label="IP地址" width="140" />
          <el-table-column prop="create_time" label="时间" width="180" />
        </el-table>
      </el-card>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, nextTick, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { ElMessage } from 'element-plus'
import {
  User, ShoppingCart, Money, Document, Refresh, Top, Bottom,
  ArrowRight, OfficeBuilding, Box, DataAnalysis
} from '@element-plus/icons-vue'
import { getDashboardStatistics, getSalesTrend, getCustomerDistribution } from '@/api/modules/statistics'
import { getOrderList } from '@/api/modules/order'
import { getFollowList } from '@/api/modules/follow'
import { getLogList } from '@/api/modules/log'
import { useUserStore } from '@/store/modules/user'
import * as echarts from 'echarts'

const router = useRouter()
const userStore = useUserStore()

const loading = ref(false)
const salesTrendType = ref('month')
const salesChartRef = ref(null)
const customerChartRef = ref(null)
let salesChart = null
let customerChart = null

const greeting = computed(() => {
  const hour = new Date().getHours()
  if (hour < 6) return '凌晨好'
  if (hour < 9) return '早上好'
  if (hour < 12) return '上午好'
  if (hour < 14) return '中午好'
  if (hour < 18) return '下午好'
  if (hour < 22) return '晚上好'
  return '你好'
})

const currentDate = computed(() => {
  const now = new Date()
  return `${now.getFullYear()}年${now.getMonth() + 1}月${now.getDate()}日`
})

const statistics = reactive([
  {
    label: '客户总数',
    value: '0',
    icon: OfficeBuilding,
    class: 'customer',
    change: '12.5',
    changeType: 'up',
    description: '较上周新增'
  },
  {
    label: '订单总数',
    value: '0',
    icon: ShoppingCart,
    class: 'order',
    change: '8.3',
    changeType: 'up',
    description: '较上周新增'
  },
  {
    label: '销售总额',
    value: '¥0',
    icon: Money,
    class: 'amount',
    change: '15.2',
    changeType: 'up',
    description: '较上周增长'
  },
  {
    label: '待处理订单',
    value: '0',
    icon: Document,
    class: 'pending',
    change: '3.1',
    changeType: 'down',
    description: '较上周减少'
  }
])

const pendingOrders = ref([])
const recentFollows = ref([])
const operationLogs = ref([])

const orderStatusMap = {
  draft: { text: '草稿', type: 'info' },
  pending: { text: '待审核', type: 'warning' },
  approved: { text: '已审核', type: 'success' },
  shipped: { text: '已发货', type: 'primary' },
  completed: { text: '已完成', type: 'success' },
  cancelled: { text: '已取消', type: 'danger' }
}

const followTypeMap = {
  phone: '电话',
  visit: '拜访',
  email: '邮件',
  meeting: '会议',
  other: '其他'
}

const moduleMap = {
  customer: '客户管理',
  contact: '联系人',
  order: '订单管理',
  product: '产品管理',
  supplier: '供应商',
  system: '系统管理'
}

const actionMap = {
  create: '新增',
  update: '编辑',
  delete: '删除',
  login: '登录',
  logout: '登出'
}

const getOrderStatusText = (status) => orderStatusMap[status]?.text || status
const getOrderStatusType = (status) => orderStatusMap[status]?.type || 'info'
const getFollowTypeText = (type) => followTypeMap[type] || type
const getModuleText = (module) => moduleMap[module] || module
const getActionText = (action) => actionMap[action] || action

const loadStatistics = async () => {
  try {
    const res = await getDashboardStatistics()
    if (res.code === 200) {
      const data = res.data
      statistics[0].value = data.customer_count || '0'
      statistics[1].value = data.order_count || '0'
      statistics[2].value = data.total_amount ? `¥${Number(data.total_amount).toLocaleString()}` : '¥0'
      statistics[3].value = data.pending_order_count || '0'
    }
  } catch (error) {
    console.error('获取统计数据失败:', error)
  }
}

const loadSalesTrend = async () => {
  try {
    const res = await getSalesTrend({ type: salesTrendType.value })
    if (res.code === 200 && salesChart) {
      salesChart.setOption({
        xAxis: { data: res.data.dates },
        series: [{ data: res.data.amounts }]
      })
    }
  } catch (error) {
    console.error('获取销售趋势失败:', error)
  }
}

const loadCustomerDistribution = async () => {
  try {
    const res = await getCustomerDistribution()
    if (res.code === 200 && customerChart) {
      customerChart.setOption({
        series: [{
          data: res.data.map(item => ({ name: item.industry, value: item.count }))
        }]
      })
    }
  } catch (error) {
    console.error('获取客户分布失败:', error)
  }
}

const loadPendingOrders = async () => {
  try {
    const res = await getOrderList({ status: 'pending,approved', page: 1, page_size: 5 })
    if (res.code === 200) {
      pendingOrders.value = res.data.list || []
    }
  } catch (error) {
    console.error('获取待处理订单失败:', error)
  }
}

const loadRecentFollows = async () => {
  try {
    const res = await getFollowList({ page: 1, page_size: 5 })
    if (res.code === 200) {
      recentFollows.value = res.data.list || []
    }
  } catch (error) {
    console.error('获取最近跟进失败:', error)
  }
}

const loadOperationLogs = async () => {
  try {
    const res = await getLogList({ page: 1, page_size: 5 })
    if (res.code === 200) {
      operationLogs.value = res.data.list || []
    }
  } catch (error) {
    console.error('获取操作日志失败:', error)
  }
}

const initCharts = () => {
  nextTick(() => {
    if (salesChartRef.value) {
      salesChart = echarts.init(salesChartRef.value)
      salesChart.setOption({
        tooltip: { trigger: 'axis', backgroundColor: 'rgba(255,255,255,0.95)', borderColor: '#eee', textStyle: { color: '#333' } },
        grid: { left: '3%', right: '4%', bottom: '3%', containLabel: true },
        xAxis: { type: 'category', boundaryGap: false, data: [], axisLine: { lineStyle: { color: '#eee' } }, axisLabel: { color: '#666' } },
        yAxis: { type: 'value', axisLine: { show: false }, axisTick: { show: false }, splitLine: { lineStyle: { color: '#f5f5f5' } }, axisLabel: { formatter: '¥{value}' } },
        series: [{
          name: '销售额',
          type: 'line',
          smooth: true,
          symbol: 'circle',
          symbolSize: 8,
          areaStyle: { opacity: 0.15, color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
            { offset: 0, color: '#667eea' },
            { offset: 1, color: 'rgba(102,126,234,0.1)' }
          ]) },
          itemStyle: { color: '#667eea' },
          lineStyle: { width: 3 },
          data: []
        }]
      })
    }

    if (customerChartRef.value) {
      customerChart = echarts.init(customerChartRef.value)
      customerChart.setOption({
        tooltip: { trigger: 'item', backgroundColor: 'rgba(255,255,255,0.95)', borderColor: '#eee', textStyle: { color: '#333' } },
        legend: { bottom: '5%', left: 'center', textStyle: { color: '#666' } },
        series: [{
          name: '客户行业',
          type: 'pie',
          radius: ['45%', '70%'],
          center: ['50%', '45%'],
          avoidLabelOverlap: false,
          itemStyle: { borderRadius: 8, borderColor: '#fff', borderWidth: 3 },
          label: { show: false },
          emphasis: {
            label: { show: true, fontSize: 14, fontWeight: 'bold' }
          },
          data: []
        }]
      })
    }
  })
}

const refreshData = async () => {
  loading.value = true
  try {
    await Promise.all([
      loadStatistics(),
      loadSalesTrend(),
      loadCustomerDistribution(),
      loadPendingOrders(),
      loadRecentFollows(),
      loadOperationLogs()
    ])
    ElMessage.success('数据已刷新')
  } finally {
    loading.value = false
  }
}

const handleOrderClick = (order) => {
  router.push({ path: '/order/list', query: { id: order.id } })
}

const handleFollowClick = (follow) => {
  router.push({ path: '/customer/follow', query: { customer_id: follow.customer_id } })
}

const handleResize = () => {
  salesChart?.resize()
  customerChart?.resize()
}

onMounted(async () => {
  loading.value = true
  await Promise.all([
    loadStatistics(),
    loadPendingOrders(),
    loadRecentFollows(),
    loadOperationLogs()
  ])
  
  initCharts()
  await Promise.all([loadSalesTrend(), loadCustomerDistribution()])
  
  loading.value = false
  window.addEventListener('resize', handleResize)
})

onUnmounted(() => {
  window.removeEventListener('resize', handleResize)
  salesChart?.dispose()
  customerChart?.dispose()
})
</script>

<style scoped>
.dashboard-container {
  padding: 0;
  max-width: 1600px;
  margin: 0 auto;
}

.dashboard-header {
  margin-bottom: 32px;
  padding: 32px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 16px;
  color: #fff;
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.welcome-title {
  font-size: 28px;
  font-weight: 600;
  margin: 0 0 8px 0;
}

.greeting {
  margin-right: 12px;
}

.username {
  font-weight: 700;
}

.welcome-subtitle {
  font-size: 14px;
  opacity: 0.85;
  margin: 0;
}

.header-actions :deep(.el-button) {
  background: rgba(255,255,255,0.2);
  border: 1px solid rgba(255,255,255,0.3);
  color: #fff;
}

.header-actions :deep(.el-button:hover) {
  background: rgba(255,255,255,0.3);
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 24px;
  margin-bottom: 24px;
}

.stat-card {
  background: #fff;
  border-radius: 16px;
  padding: 24px;
  display: flex;
  gap: 20px;
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}

.stat-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 40px rgba(0,0,0,0.08);
}

.stat-icon-wrapper {
  position: relative;
  width: 64px;
  height: 64px;
  flex-shrink: 0;
}

.stat-icon-bg {
  position: absolute;
  width: 100%;
  height: 100%;
  border-radius: 16px;
  opacity: 0.12;
}

.stat-card.customer .stat-icon-bg { background: #667eea; }
.stat-card.order .stat-icon-bg { background: #67c23a; }
.stat-card.amount .stat-icon-bg { background: #e6a23c; }
.stat-card.pending .stat-icon-bg { background: #f56c6c; }

.stat-icon {
  position: relative;
  font-size: 28px;
  padding: 16px;
  color: #667eea;
}

.stat-card.customer .stat-icon { color: #667eea; }
.stat-card.order .stat-icon { color: #67c23a; }
.stat-card.amount .stat-icon { color: #e6a23c; }
.stat-card.pending .stat-icon { color: #f56c6c; }

.stat-content {
  flex: 1;
}

.stat-label {
  font-size: 14px;
  color: #909399;
  margin: 0 0 8px 0;
}

.stat-value-row {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 4px;
}

.stat-value {
  font-size: 28px;
  font-weight: 700;
  color: #303133;
}

.stat-change {
  display: flex;
  align-items: center;
  gap: 2px;
  font-size: 13px;
  padding: 2px 6px;
  border-radius: 4px;
}

.stat-change.up {
  color: #67c23a;
  background: rgba(103,194,58,0.1);
}

.stat-change.down {
  color: #f56c6c;
  background: rgba(245,108,108,0.1);
}

.stat-description {
  font-size: 12px;
  color: #c0c4cc;
  margin: 0;
}

.charts-section {
  margin-bottom: 24px;
}

.chart-card {
  border-radius: 16px;
  border: none;
}

.chart-card :deep(.el-card__header) {
  padding: 20px 24px;
  border-bottom: 1px solid #f5f5f5;
}

.chart-card :deep(.el-card__body) {
  padding: 24px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.header-title {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 16px;
  font-weight: 600;
  color: #303133;
}

.title-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #667eea;
}

.title-dot.warning { background: #e6a23c; }
.title-dot.success { background: #67c23a; }
.title-dot.info { background: #909399; }

.chart-container {
  width: 100%;
  height: 320px;
}

.tables-section {
  margin-bottom: 24px;
}

.list-card {
  border-radius: 16px;
  border: none;
  height: 100%;
}

.list-card :deep(.el-card__header) {
  padding: 20px 24px;
  border-bottom: 1px solid #f5f5f5;
}

.list-card :deep(.el-card__body) {
  padding: 0;
}

.table-list {
  max-height: 320px;
  overflow-y: auto;
}

.list-item {
  padding: 16px 24px;
  border-bottom: 1px solid #f5f5f5;
  cursor: pointer;
  transition: background-color 0.2s;
}

.list-item:last-child {
  border-bottom: none;
}

.list-item:hover {
  background: #f8f9fb;
}

.item-main {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}

.item-title {
  font-size: 14px;
  font-weight: 500;
  color: #303133;
}

.item-sub {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.item-info {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  color: #909399;
}

.item-content {
  max-width: 200px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.item-amount {
  font-size: 14px;
  font-weight: 600;
  color: #f56c6c;
}

.log-card {
  border-radius: 16px;
  border: none;
}

.log-card :deep(.el-card__header) {
  padding: 20px 24px;
  border-bottom: 1px solid #f5f5f5;
}

.user-cell {
  display: flex;
  align-items: center;
  gap: 10px;
}

.action-text {
  color: #606266;
}

@media (max-width: 1200px) {
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 768px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }
  
  .charts-section :deep(.el-col) {
    margin-bottom: 16px;
  }
  
  .tables-section :deep(.el-col) {
    margin-bottom: 16px;
  }
}
</style>
