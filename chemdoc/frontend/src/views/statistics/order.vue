<template>
  <div class="statistics-container">
    <el-row :gutter="20" class="filter-row">
      <el-col :span="24">
        <el-card>
          <el-form :inline="true" :model="searchForm">
            <el-form-item label="时间范围">
              <el-date-picker
                v-model="searchForm.date_range"
                type="daterange"
                range-separator="至"
                start-placeholder="开始日期"
                end-placeholder="结束日期"
                value-format="YYYY-MM-DD"
              />
            </el-form-item>
            <el-form-item label="订单状态">
              <el-select v-model="searchForm.status" placeholder="请选择" clearable>
                <el-option label="草稿" value="draft" />
                <el-option label="待审核" value="pending" />
                <el-option label="已审核" value="approved" />
                <el-option label="已完成" value="completed" />
              </el-select>
            </el-form-item>
            <el-form-item>
              <el-button type="primary" @click="handleSearch">
                <el-icon><Search /></el-icon>
                查询
              </el-button>
              <el-button @click="handleReset">
                <el-icon><Refresh /></el-icon>
                重置
              </el-button>
            </el-form-item>
          </el-form>
        </el-card>
      </el-col>
    </el-row>

    <el-row :gutter="20" class="summary-row">
      <el-col :xs="24" :sm="12" :lg="6">
        <el-card class="summary-card">
          <div class="summary-item">
            <div class="summary-label">订单总数</div>
            <div class="summary-value">{{ summary.total_count }}</div>
          </div>
        </el-card>
      </el-col>
      <el-col :xs="24" :sm="12" :lg="6">
        <el-card class="summary-card">
          <div class="summary-item">
            <div class="summary-label">订单总额</div>
            <div class="summary-value primary">¥{{ Number(summary.total_amount).toLocaleString() }}</div>
          </div>
        </el-card>
      </el-col>
      <el-col :xs="24" :sm="12" :lg="6">
        <el-card class="summary-card">
          <div class="summary-item">
            <div class="summary-label">已完成订单</div>
            <div class="summary-value success">{{ summary.completed_count }}</div>
          </div>
        </el-card>
      </el-col>
      <el-col :xs="24" :sm="12" :lg="6">
        <el-card class="summary-card">
          <div class="summary-item">
            <div class="summary-label">平均订单金额</div>
            <div class="summary-value">{{ summary.avg_amount }}</div>
          </div>
        </el-card>
      </el-col>
    </el-row>

    <el-row :gutter="20" class="chart-row">
      <el-col :xs="24" :lg="12">
        <el-card>
          <template #header>
            <span class="card-title">销售趋势</span>
          </template>
          <div ref="trendChartRef" class="chart-container"></div>
        </el-card>
      </el-col>
      <el-col :xs="24" :lg="12">
        <el-card>
          <template #header>
            <span class="card-title">订单状态分布</span>
          </template>
          <div ref="statusChartRef" class="chart-container"></div>
        </el-card>
      </el-col>
    </el-row>


  </div>
</template>

<script setup>
import { ref, reactive, onMounted, nextTick } from 'vue'
import { Search, Refresh } from '@element-plus/icons-vue'
import { getOrderStatistics } from '@/api/modules/statistics'
import { ElMessage } from 'element-plus'
import * as echarts from 'echarts'

const searchForm = reactive({
  date_range: [],
  status: ''
})

const summary = reactive({
  total_count: 0,
  total_amount: 0,
  completed_count: 0,
  avg_amount: 0
})

const trendChartRef = ref(null)
const statusChartRef = ref(null)
let trendChart = null
let statusChart = null

const statusMap = {
  draft: { text: '草稿', type: 'info' },
  pending: { text: '待审核', type: 'warning' },
  approved: { text: '已审核', type: 'success' },
  shipped: { text: '已发货', type: 'primary' },
  completed: { text: '已完成', type: 'success' },
  cancelled: { text: '已取消', type: 'danger' }
}

const getStatusText = (status) => statusMap[status]?.text || status
const getStatusType = (status) => statusMap[status]?.type || 'info'

const loadData = async () => {
  try {
    const params = {}
    
    if (searchForm.date_range && searchForm.date_range.length === 2) {
      params.date_range = searchForm.date_range
    }
    
    const res = await getOrderStatistics(params)
    if (res.code === 200) {
      const data = res.data
      
      summary.total_count = data.total_count || 0
      summary.total_amount = data.total_amount || 0
      summary.completed_count = data.completed_count || 0
      summary.avg_amount = data.avg_amount || 0
      
      if (data.monthly_data && data.monthly_data.length > 0) {
        initTrendChart(data.monthly_data)
      }
      
      if (data.status_data && data.status_data.length > 0) {
        initStatusChart(data.status_data)
      }
    }
  } catch (error) {
    console.error('获取统计数据失败:', error)
    ElMessage.error('获取统计数据失败')
  }
}

const initTrendChart = (data) => {
  if (!trendChartRef.value) return
  
  if (trendChart) {
    trendChart.dispose()
  }
  
  trendChart = echarts.init(trendChartRef.value)
  trendChart.setOption({
    tooltip: { trigger: 'axis' },
    grid: { left: '3%', right: '4%', bottom: '3%', containLabel: true },
    xAxis: { type: 'category', data: data.map(item => item.month) },
    yAxis: { type: 'value', axisLabel: { formatter: '¥{value}' } },
    series: [{
      name: '销售额',
      type: 'line',
      smooth: true,
      areaStyle: { opacity: 0.3 },
      itemStyle: { color: '#409eff' },
      data: data.map(item => item.amount)
    }]
  })
}

const initStatusChart = (data) => {
  if (!statusChartRef.value) return
  
  if (statusChart) {
    statusChart.dispose()
  }
  
  statusChart = echarts.init(statusChartRef.value)
  statusChart.setOption({
    tooltip: { trigger: 'item' },
    legend: { bottom: '5%', left: 'center' },
    series: [{
      name: '订单状态',
      type: 'pie',
      radius: ['40%', '70%'],
      avoidLabelOverlap: false,
      itemStyle: { borderRadius: 10, borderColor: '#fff', borderWidth: 2 },
      label: { show: true },
      emphasis: { label: { show: true, fontSize: 14 } },
      data: data.map(item => ({
        name: getStatusText(item.status),
        value: item.count
      }))
    }]
  })
}

const handleSearch = () => {
  loadData()
}

const handleReset = () => {
  Object.keys(searchForm).forEach(key => {
    searchForm[key] = key === 'date_range' ? [] : ''
  })
  loadData()
}

const handleResize = () => {
  trendChart?.resize()
  statusChart?.resize()
}

onMounted(() => {
  loadData()
  window.addEventListener('resize', handleResize)
})
</script>

<style scoped>
.statistics-container {
  padding: 0;
}

.filter-row {
  margin-bottom: 20px;
}

.summary-row {
  margin-bottom: 20px;
}

.summary-card {
  margin-bottom: 20px;
}

.summary-item {
  text-align: center;
  padding: 10px 0;
}

.summary-label {
  font-size: 14px;
  color: #909399;
  margin-bottom: 8px;
}

.summary-value {
  font-size: 28px;
  font-weight: 600;
  color: #303133;
}

.summary-value.primary {
  color: #409eff;
}

.summary-value.success {
  color: #67c23a;
}

.chart-row {
  margin-bottom: 20px;
}

.chart-container {
  width: 100%;
  height: 300px;
}

.card-title {
  font-size: 16px;
  font-weight: 600;
  color: #303133;
}

.pagination-container {
  display: flex;
  justify-content: flex-end;
  margin-top: 20px;
}
</style>
