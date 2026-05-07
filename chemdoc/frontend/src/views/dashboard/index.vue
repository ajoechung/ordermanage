<template>
  <div class="dashboard-container">
    <el-row :gutter="20" class="statistics-row">
      <el-col :xs="24" :sm="12" :lg="6" v-for="(stat, index) in statistics" :key="index">
        <el-card class="stat-card" shadow="hover">
          <div class="stat-content">
            <div class="stat-info">
              <p class="stat-title">{{ stat.title }}</p>
              <p class="stat-value">{{ stat.value }}</p>
              <p class="stat-desc">
                <span :class="stat.changeType">{{ stat.change }}%</span>
                {{ stat.label }}
              </p>
            </div>
            <div class="stat-icon" :style="{ backgroundColor: stat.color }">
              <el-icon :size="32" :color="'#ffffff'">
                <component :is="stat.icon" />
              </el-icon>
            </div>
          </div>
        </el-card>
      </el-col>
    </el-row>

    <el-row :gutter="20" class="chart-row">
      <el-col :xs="24" :lg="16">
        <el-card class="chart-card">
          <template #header>
            <div class="card-header">
              <span class="card-title">销售趋势</span>
              <el-radio-group v-model="salesTrendType" size="small">
                <el-radio-button label="week">本周</el-radio-button>
                <el-radio-button label="month">本月</el-radio-button>
                <el-radio-button label="year">本年</el-radio-button>
              </el-radio-group>
            </div>
          </template>
          <div ref="salesChartRef" class="chart-container"></div>
        </el-card>
      </el-col>
      
      <el-col :xs="24" :lg="8">
        <el-card class="chart-card">
          <template #header>
            <div class="card-header">
              <span class="card-title">客户行业分布</span>
            </div>
          </template>
          <div ref="customerChartRef" class="chart-container"></div>
        </el-card>
      </el-col>
    </el-row>

    <el-row :gutter="20" class="table-row">
      <el-col :xs="24" :lg="12">
        <el-card class="list-card">
          <template #header>
            <div class="card-header">
              <span class="card-title">待处理订单</span>
              <el-button type="primary" link @click="$router.push('/order/list')">
                查看更多
              </el-button>
            </div>
          </template>
          <el-table :data="pendingOrders" style="width: 100%">
            <el-table-column prop="order_no" label="订单号" width="140" />
            <el-table-column prop="customer_name" label="客户名称" />
            <el-table-column prop="total_amount" label="金额" width="100" align="right">
              <template #default="{ row }">
                ¥{{ Number(row.total_amount).toLocaleString() }}
              </template>
            </el-table-column>
            <el-table-column prop="status" label="状态" width="80">
              <template #default="{ row }">
                <el-tag :type="getOrderStatusType(row.status)" size="small">
                  {{ getOrderStatusText(row.status) }}
                </el-tag>
              </template>
            </el-table-column>
          </el-table>
        </el-card>
      </el-col>
      
      <el-col :xs="24" :lg="12">
        <el-card class="list-card">
          <template #header>
            <div class="card-header">
              <span class="card-title">最近跟进</span>
              <el-button type="primary" link @click="$router.push('/customer/follow')">
                查看更多
              </el-button>
            </div>
          </template>
          <el-table :data="recentFollows" style="width: 100%">
            <el-table-column prop="customer_name" label="客户名称" />
            <el-table-column prop="follow_type" label="跟进方式" width="100">
              <template #default="{ row }">
                {{ getFollowTypeText(row.follow_type) }}
              </template>
            </el-table-column>
            <el-table-column prop="content" label="跟进内容" show-overflow-tooltip />
            <el-table-column prop="create_time" label="时间" width="160" />
          </el-table>
        </el-card>
      </el-col>
    </el-row>

    <el-row :gutter="20">
      <el-col :xs="24" :lg="24">
        <el-card class="log-card">
          <template #header>
            <div class="card-header">
              <span class="card-title">操作日志</span>
              <el-button type="primary" link @click="$router.push('/system/log')">
                查看更多
              </el-button>
            </div>
          </template>
          <el-table :data="operationLogs" style="width: 100%">
            <el-table-column prop="username" label="操作人" width="100" />
            <el-table-column prop="module" label="模块" width="100" />
            <el-table-column prop="action" label="操作" width="120" />
            <el-table-column prop="description" label="描述" show-overflow-tooltip />
            <el-table-column prop="ip" label="IP地址" width="140" />
            <el-table-column prop="create_time" label="时间" width="180" />
          </el-table>
        </el-card>
      </el-col>
    </el-row>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, nextTick } from 'vue'
import { User, ShoppingCart, Money, Document } from '@element-plus/icons-vue'
import { getDashboardStatistics, getSalesTrend, getCustomerDistribution } from '@/api/modules/statistics'
import { getOrderList } from '@/api/modules/order'
import { getFollowList } from '@/api/modules/follow'
import { getLogList } from '@/api/modules/log'
import { ElMessage } from 'element-plus'
import * as echarts from 'echarts'

const salesTrendType = ref('month')
const salesChartRef = ref(null)
const customerChartRef = ref(null)
let salesChart = null
let customerChart = null

const statistics = reactive([
  { title: '客户总数', value: '0', change: '12.5', label: '较上周', icon: User, color: '#409eff', changeType: 'up' },
  { title: '订单总数', value: '0', change: '8.3', label: '较上周', icon: ShoppingCart, color: '#67c23a', changeType: 'up' },
  { title: '销售总额', value: '¥0', change: '15.2', label: '较上周', icon: Money, color: '#e6a23c', changeType: 'up' },
  { title: '待处理订单', value: '0', change: '3.1', label: '较上周', icon: Document, color: '#f56c6c', changeType: 'down' }
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

const getOrderStatusText = (status) => orderStatusMap[status]?.text || status
const getOrderStatusType = (status) => orderStatusMap[status]?.type || 'info'
const getFollowTypeText = (type) => followTypeMap[type] || type

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
    const res = await getLogList({ page: 1, page_size: 10 })
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
        tooltip: { trigger: 'axis' },
        grid: { left: '3%', right: '4%', bottom: '3%', containLabel: true },
        xAxis: { type: 'category', boundaryGap: false, data: [] },
        yAxis: { type: 'value', axisLabel: { formatter: '¥{value}' } },
        series: [{
          name: '销售额',
          type: 'line',
          smooth: true,
          areaStyle: { opacity: 0.3 },
          itemStyle: { color: '#409eff' },
          data: []
        }]
      })
    }

    if (customerChartRef.value) {
      customerChart = echarts.init(customerChartRef.value)
      customerChart.setOption({
        tooltip: { trigger: 'item' },
        legend: { bottom: '5%', left: 'center' },
        series: [{
          name: '客户行业',
          type: 'pie',
          radius: ['40%', '70%'],
          avoidLabelOverlap: false,
          itemStyle: { borderRadius: 10, borderColor: '#fff', borderWidth: 2 },
          label: { show: false },
          emphasis: { label: { show: true, fontSize: 14 } },
          data: []
        }]
      })
    }
  })
}

const handleResize = () => {
  salesChart?.resize()
  customerChart?.resize()
}

onMounted(async () => {
  await Promise.all([
    loadStatistics(),
    loadPendingOrders(),
    loadRecentFollows(),
    loadOperationLogs()
  ])
  
  initCharts()
  await Promise.all([loadSalesTrend(), loadCustomerDistribution()])
  
  window.addEventListener('resize', handleResize)
})
</script>

<style scoped>
.dashboard-container {
  padding: 0;
}

.statistics-row {
  margin-bottom: 20px;
}

.stat-card {
  margin-bottom: 20px;
}

.stat-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.stat-info {
  flex: 1;
}

.stat-title {
  font-size: 14px;
  color: #909399;
  margin: 0 0 8px 0;
}

.stat-value {
  font-size: 28px;
  font-weight: 600;
  color: #303133;
  margin: 0 0 8px 0;
}

.stat-desc {
  font-size: 12px;
  color: #909399;
  margin: 0;
}

.stat-desc .up {
  color: #67c23a;
}

.stat-desc .down {
  color: #f56c6c;
}

.stat-icon {
  width: 64px;
  height: 64px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0.9;
}

.chart-row {
  margin-bottom: 20px;
}

.chart-card, .list-card, .log-card {
  margin-bottom: 20px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.card-title {
  font-size: 16px;
  font-weight: 600;
  color: #303133;
}

.chart-container {
  width: 100%;
  height: 320px;
}
</style>
