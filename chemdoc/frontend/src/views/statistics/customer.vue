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
            <div class="summary-label">正常客户</div>
            <div class="summary-value success">{{ summary.normal_count }}</div>
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
            <span class="card-title">行业分布</span>
          </template>
          <div ref="typeChartRef" class="chart-container"></div>
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
  normal_count: 0,
  lost_count: 0,
  conversion_rate: 0
})

const trendChartRef = ref(null)
const typeChartRef = ref(null)
let trendChart = null
let typeChart = null

const customerTypeMap = {
  potential: '潜在客户',
  formal: '正式客户',
  vip: 'VIP客户',
  inactive: '失效客户'
}

const getCustomerTypeText = (type) => customerTypeMap[type] || type

const loadData = async () => {
  try {
    const params = {}
    
    if (searchForm.date_range && searchForm.date_range.length === 2) {
      params.date_range = searchForm.date_range
    }
    
    const res = await getCustomerStatistics(params)
    if (res.code === 200) {
      const data = res.data
      
      // 使用后端返回的实际数据
      summary.total_count = data.total_count || 0
      summary.normal_count = data.normal_count || 0
      summary.lost_count = 0  // 后端没有返回这个字段
      summary.conversion_rate = 0  // 后端没有返回这个字段
      
      // 处理行业分布图表
      if (data.industry_data && data.industry_data.length > 0) {
        initIndustryChart(data.industry_data)
      }
      
      // 处理客户等级图表
      if (data.level_data && data.level_data.length > 0) {
        initLevelChart(data.level_data)
      }
      
      // 处理月度趋势图表
      if (data.monthly_data && data.monthly_data.length > 0) {
        initTrendChart(data.monthly_data)
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
    legend: { data: ['新增客户'] },
    grid: { left: '3%', right: '4%', bottom: '3%', containLabel: true },
    xAxis: { 
      type: 'category', 
      data: data.map(item => item.month) 
    },
    yAxis: { type: 'value' },
    series: [
      { 
        name: '新增客户', 
        type: 'bar', 
        data: data.map(item => item.count), 
        itemStyle: { color: '#67c23a' } 
      }
    ]
  })
}

const initIndustryChart = (data) => {
  if (!typeChartRef.value) return
  
  if (typeChart) {
    typeChart.dispose()
  }
  
  typeChart = echarts.init(typeChartRef.value)
  typeChart.setOption({
    tooltip: { trigger: 'item' },
    legend: { bottom: '5%', left: 'center' },
    series: [{
      name: '行业分布',
      type: 'pie',
      radius: ['40%', '70%'],
      avoidLabelOverlap: false,
      itemStyle: { borderRadius: 10, borderColor: '#fff', borderWidth: 2 },
      label: { show: true },
      emphasis: { label: { show: true, fontSize: 14 } },
      data: data.map(item => ({
        name: item.industry || '其他',
        value: item.count
      }))
    }]
  })
}

const initLevelChart = (data) => {
  // 可以扩展为第三个图表，暂时复用行业图表的位置或者添加新的图表区域
  // 这里先不做修改
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
