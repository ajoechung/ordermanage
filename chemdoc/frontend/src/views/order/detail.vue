<template>
  <el-drawer
    v-model="visible"
    title="订单详情"
    size="60%"
    direction="rtl"
    :close-on-click-modal="false"
    @close="handleClose"
  >
    <div v-loading="loading">
      <el-descriptions :column="2" border v-if="orderData">
        <el-descriptions-item label="订单编号">{{ orderData.order_no || '-' }}</el-descriptions-item>
        <el-descriptions-item label="客户名称">{{ orderData.customer_name || '-' }}</el-descriptions-item>
        <el-descriptions-item label="订单状态">
          <el-tag :type="getStatusTag(orderData.order_status)">{{ getStatusText(orderData.order_status) }}</el-tag>
        </el-descriptions-item>
        <el-descriptions-item label="总金额">¥{{ Number(orderData.total_amount || 0).toFixed(2) }}</el-descriptions-item>
        <el-descriptions-item label="创建时间">{{ orderData.create_time || '-' }}</el-descriptions-item>
        <el-descriptions-item label="更新时间">{{ orderData.update_time || '-' }}</el-descriptions-item>
      </el-descriptions>

      <el-divider />

      <h4>订单明细</h4>
      <el-table :data="orderItems" stripe border style="margin-top: 16px">
        <el-table-column prop="product_name" label="产品名称" min-width="180" />
        <el-table-column prop="spec" label="规格" width="150" />
        <el-table-column prop="unit" label="单位" width="80" />
        <el-table-column prop="price" label="单价" width="120" align="right">
          <template #default="{ row }">¥{{ Number(row.price || 0).toFixed(2) }}</template>
        </el-table-column>
        <el-table-column prop="quantity" label="数量" width="100" align="center" />
        <el-table-column prop="subtotal" label="小计" width="120" align="right">
          <template #default="{ row }">¥{{ Number(row.subtotal || 0).toFixed(2) }}</template>
        </el-table-column>
      </el-table>

      <el-empty v-if="!orderData" description="暂无数据" />
    </div>
  </el-drawer>
</template>

<script setup>
import { ref, watch } from 'vue'
import { getOrderDetail } from '@/api/modules/order'
import { ElMessage } from 'element-plus'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  orderId: {
    type: [Number, String],
    default: null
  }
})

const emit = defineEmits(['update:modelValue'])

const visible = ref(false)
const loading = ref(false)
const orderData = ref(null)
const orderItems = ref([])

watch(() => props.modelValue, (val) => {
  visible.value = val
  if (val && props.orderId) {
    loadOrderDetail(props.orderId)
  }
})

watch(visible, (val) => {
  emit('update:modelValue', val)
})

const getStatusText = (status) => {
  const map = { 0: '草稿', 1: '待审核', 2: '已审核', 3: '已发货', 4: '已完成', 5: '已取消' }
  return map[status] || '未知'
}

const getStatusTag = (status) => {
  const map = { 0: 'info', 1: 'warning', 2: 'primary', 3: 'success', 4: 'success', 5: 'danger' }
  return map[status] || 'info'
}

const loadOrderDetail = async (id) => {
  loading.value = true
  try {
    const res = await getOrderDetail(id)
    if (res.code === 200) {
      orderData.value = res.data
      orderItems.value = res.data.items || []
    } else {
      ElMessage.error(res.msg || '获取订单详情失败')
    }
  } catch (error) {
    console.error('获取订单详情失败:', error)
    ElMessage.error('获取订单详情失败')
  } finally {
    loading.value = false
  }
}

const handleClose = () => {
  orderData.value = null
  orderItems.value = []
}
</script>
