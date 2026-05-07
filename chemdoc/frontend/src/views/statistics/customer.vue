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
            <el-form-item label="客户类型">
              <el-select v-model="searchForm.customer_type" placeholder="请选择" clearable>
                <el-option label="潜在客户" value="potential" />
                <el-option label="正式客户" value="formal" />
                <el-option label="VIP客户" value="vip" />
                <el-option label="失效客户" value="inactive" />
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
            <div class="summary-label">客户总数</div>
            <div class="summary-value">{{ summary.total_count }}</div>
          </div>
        </el-card>
      </el-col>
      <el-col :xs="24" :sm="12" :lg="6">
        <el-card class="summary-card">
          <div class="summary-item">
            <div class="summary-label">新增客户</div>
            <div class="summary-value success">{{ summary.new_count }}</div>
          </div>
        </el-card>
      </el-col>
      <el-col :xs="24" :sm="12" :lg="6">
        <el-card class="summary-card">
          <div class="summary-item">
            <div class="summary-label">流失客户</div>
            <div class="summary-value danger">{{ summary.lost_count }}</div>
          </div>
        </el-card>
      </el-col>
      <el-col :xs="24" :sm="12" :lg="6">
        <el-card class="summary-card">
          <div class="summary-item">
            <div class="summary-label">转化率</div>
            <div class="summary-value">{{ summary.conversion_rate }}%</div>
          </div>
        </el-card>
      </el-col>
    </el-row>

    <el-row :gutter="20" class="chart-row">
      <el-col :xs="24" :lg="12">
        <el-card>
          <template #header>
            <span class="card-title">客户增长趋势</span>
          </template>
          <div ref="trendChartRef" class="chart-container"></div>
        </el-card>
      </el-col>
      <el-col :xs="24" :lg="12">
        <el-card>
          <template #header>
            <span class="card-title">客户类型分布</span>
          </template>
          <div ref="typeChartRef" class="chart-container"></div>
        </el-card>
      </el-col>
    </el-row>

    <el-row :gutter="20" class="table-row">
      <el-col :span="24">
        <el-card>
          <template #header>
            <span class="card-title">客户明细</span>
          </template>
          <el-table :data="customerList" border stripe>
            <el-table-column prop="name" label="客户名称" min-width="200" />
            <el-table-column prop="customer_type" label="客户类型" width="120">
              <template #default="{ row }">
                {{ getCustomerTypeText(row.customer_type) }}
              </template>
            </el-table-column>
            <el-table-column prop="level" label="客户等级" width="100">
              <template #default="{ row }">
                {{ row.level }}类
              </template>
            </el-table-column>
            <el-table-column prop="order_count" label="订单数" width="100" align="center" />
            <el-table-column prop="order_amount" label="订单金额" width="120" align="right">
              <template #default="{ row }">
                ¥{{ Number(row.order_amount || 0).toLocaleString() }}
              </template>
            </el-table-column>
            <el-table-column prop="contact" label="联系人" width="120" />
            <el-table-column prop="phone" label="联系电话" width="130" />
            <el-table-column prop="create_time" label="创建时间" width="160" />
          </el-table>
          <div class="pagination-container">
            <el-pagination
              v-model:current-page="pagination.page"
              v-model:page-size="pagination.pageSize"
              :page-sizes="[10, 20, 50]"
              :total="pagination.total"
              layout="total, sizes, prev, pager, next"
              @size-change="handleSizeChange"
              @current-change="handlePageChange"
            />
          </div>
        </el-card>
      </el-col>
    </el-row>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, nextTick } from 'vue'
import { Search, Refresh } from '@element-plus/icons-vue'
import { getCustomerStatistics } from '@/api/modules/statistics'
import { ElMessage } from 'element-plus'
import * as echarts from 'echarts'

const searchForm = reactive({
  date_range: [],
  customer_type: ''
})

const summary = reactive({
  total_count: 0,
  new_count: 0,
  lost_count: 0,
  conversion_rate: 0
})

const customerList = ref([])
const trendChartRef = ref(null)
const typeChartRef = ref(null)
let trendChart = null
let typeChart = null

const pagination = reactive({
  page: 1,
  pageSize: 10,
  total: 0
})

const customerTypeMap = {
  potential: '潜在客户',
  formal: '正式客户',
  vip: 'VIP客户',
  inactive: '失效客户'
}

const getCustomerTypeText = (type) => customerTypeMap[type] || type

const loadData = async () => {
  try {
    const params = {
      page: pagination.page,
      page_size: pagination.pageSize,
      ...searchForm
    }
    
    if (searchForm.date_range && searchForm.date_range.length === 2) {
      params.start_date = searchForm.date_range[0]
      params.end_date = searchForm.date_range[1]
    }
    
    const res = await getCustomerStatistics(params)
    if (res.code === 200) {
      const data = res.data
      summary.total_count = data.summary.total_count || 0
      summary.new_count = data.summary.new_count || 0
      summary.lost_count = data.summary.lost_count || 0
      summary.conversion_rate = data.summary.conversion_rate || 0
      
      customerList.value = data.list || []
      pagination.total = data.total || 0
      
      if (data.trend) {
        initTrendChart(data.trend)
      }
      if (data.distribution) {
        initTypeChart(data.distribution)
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
    legend: { data: ['新增客户', '流失客户'] },
    grid: { left: '3%', right: '4%', bottom: '3%', containLabel: true },
    xAxis: { type: 'category', data: data.dates || [] },
    yAxis: { type: 'value' },
    series: [
      { name: '新增客户', type: 'bar', data: data.new_data || [], itemStyle: { color: '#67c23a' } },
      { name: '流失客户', type: 'bar', data: data.lost_data || [], itemStyle: { color: '#f56c6c' } }
    ]
  })
}

const initTypeChart = (data) => {
  if (!typeChartRef.value) return
  
  if (typeChart) {
    typeChart.dispose()
  }
  
  typeChart = echarts.init(typeChartRef.value)
  trendChart.setOption({
    tooltip: { trigger: 'item' },
    legend: { bottom: '5%', left: 'center' },
    series: [{
      name: '客户类型',
      type: 'pie',
      radius: ['40%', '70%'],
      avoidLabelOverlap: false,
      itemStyle: { borderRadius: 10, borderColor: '#fff', borderWidth: 2 },
      label: { show: false },
      emphasis: { label: { show: true, fontSize: 14 } },
      data: data.map(item => ({
        name: getCustomerTypeText(item.type),
        value: item.count
      }))
    }]
  })
}

const handleSearch = () => {
  pagination.page = 1
  loadData()
}

const handleReset = () => {
  Object.keys(searchForm).forEach(key => {
    searchForm[key] = key === 'date_range' ? [] : ''
  })
  pagination.page = 1
  loadData()
}

const handleSizeChange = (size) => {
  pagination.pageSize = size
  loadData()
}

const handlePageChange = (page) => {
  pagination.page = page
  loadData()
}

const handleResize = () => {
  trendChart?.resize()
  typeChart?.resize()
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

.summary-value.success {
  color: #67c23a;
}

.summary-value.danger {
  color: #f56c6c;
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
