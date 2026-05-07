<template>
  <div class="order-container">
    <el-card class="search-card">
      <el-form :inline="true" :model="searchForm" class="search-form">
        <el-form-item label="订单号">
          <el-input v-model="searchForm.order_no" placeholder="请输入订单号" clearable @keyup.enter="handleSearch" />
        </el-form-item>
        <el-form-item label="客户名称">
          <el-input v-model="searchForm.customer_name" placeholder="请输入客户名称" clearable @keyup.enter="handleSearch" />
        </el-form-item>
        <el-form-item label="订单状态">
          <el-select v-model="searchForm.status" placeholder="请选择" clearable>
            <el-option label="草稿" value="draft" />
            <el-option label="待审核" value="pending" />
            <el-option label="已审核" value="approved" />
            <el-option label="已发货" value="shipped" />
            <el-option label="已完成" value="completed" />
            <el-option label="已取消" value="cancelled" />
          </el-select>
        </el-form-item>
        <el-form-item label="创建时间">
          <el-date-picker
            v-model="searchForm.date_range"
            type="daterange"
            range-separator="至"
            start-placeholder="开始日期"
            end-placeholder="结束日期"
            value-format="YYYY-MM-DD"
          />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="handleSearch">
            <el-icon><Search /></el-icon>
            搜索
          </el-button>
          <el-button @click="handleReset">
            <el-icon><Refresh /></el-icon>
            重置
          </el-button>
        </el-form-item>
      </el-form>
    </el-card>

    <el-card class="table-card">
      <template #header>
        <div class="table-header">
          <span class="table-title">订单列表</span>
          <div class="table-actions">
            <el-button type="primary" @click="handleAdd">
              <el-icon><Plus /></el-icon>
              新增订单
            </el-button>
            <el-button type="success" @click="handleExport">
              <el-icon><Download /></el-icon>
              导出数据
            </el-button>
          </div>
        </div>
      </template>

      <el-table
        v-loading="tableLoading"
        :data="orderList"
        stripe
        border
        @selection-change="handleSelectionChange"
      >
        <el-table-column type="selection" width="50" />
        <el-table-column prop="order_no" label="订单号" width="160" />
        <el-table-column prop="customer_name" label="客户名称" min-width="180" show-overflow-tooltip />
        <el-table-column prop="contact_name" label="联系人" width="100" />
        <el-table-column prop="contact_phone" label="联系电话" width="130" />
        <el-table-column label="订单金额" width="120" align="right">
          <template #default="{ row }">
            <span class="amount">¥{{ Number(row.total_amount || 0).toLocaleString() }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="product_count" label="产品数量" width="100" align="center" />
        <el-table-column prop="status" label="订单状态" width="100">
          <template #default="{ row }">
            <el-tag :type="getStatusType(row.status)">
              {{ getStatusText(row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="create_user_name" label="创建人" width="100" />
        <el-table-column prop="create_time" label="创建时间" width="160" />
        <el-table-column label="操作" width="300" fixed="right">
          <template #default="{ row }">
            <el-button type="primary" link size="small" @click="handleView(row)">
              查看
            </el-button>
            <el-button
              v-if="row.status === 'draft' || row.status === 'cancelled'"
              type="primary"
              link
              size="small"
              @click="handleEdit(row)"
            >
              编辑
            </el-button>
            <el-button
              v-if="row.status === 'draft'"
              type="success"
              link
              size="small"
              @click="handleSubmit(row)"
            >
              提交审核
            </el-button>
            <el-button
              v-if="row.status === 'pending' && hasPermission('order.approve')"
              type="success"
              link
              size="small"
              @click="handleApprove(row)"
            >
              审核
            </el-button>
            <el-button
              v-if="row.status === 'approved'"
              type="warning"
              link
              size="small"
              @click="handleShip(row)"
            >
              发货
            </el-button>
            <el-button
              v-if="row.status === 'shipped'"
              type="success"
              link
              size="small"
              @click="handleComplete(row)"
            >
              完成
            </el-button>
            <el-button
              v-if="row.status !== 'completed' && row.status !== 'cancelled'"
              type="danger"
              link
              size="small"
              @click="handleCancel(row)"
            >
              取消
            </el-button>
          </template>
        </el-table-column>
      </el-table>

      <div class="pagination-container">
        <el-pagination
          v-model:current-page="pagination.page"
          v-model:page-size="pagination.pageSize"
          :page-sizes="[10, 20, 50, 100]"
          :total="pagination.total"
          layout="total, sizes, prev, pager, next, jumper"
          @size-change="handleSizeChange"
          @current-change="handlePageChange"
        />
      </div>
    </el-card>

    <el-dialog
      v-model="dialogVisible"
      :title="dialogTitle"
      width="900px"
      :close-on-click-modal="false"
      @close="handleDialogClose"
    >
      <el-form
        ref="formRef"
        :model="formData"
        :rules="formRules"
        label-width="100px"
      >
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="客户名称" prop="customer_id">
              <el-select
                v-model="formData.customer_id"
                filterable
                placeholder="请选择客户"
                style="width: 100%"
                @change="handleCustomerChange"
              >
                <el-option
                  v-for="customer in customerList"
                  :key="customer.id"
                  :label="customer.name"
                  :value="customer.id"
                />
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="联系人" prop="contact_name">
              <el-input v-model="formData.contact_name" placeholder="请输入联系人" />
            </el-form-item>
          </el-col>
        </el-row>

        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="联系电话" prop="contact_phone">
              <el-input v-model="formData.contact_phone" placeholder="请输入联系电话" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="收货地址" prop="delivery_address">
              <el-input v-model="formData.delivery_address" placeholder="请输入收货地址" />
            </el-form-item>
          </el-col>
        </el-row>

        <el-form-item label="订单产品">
          <el-table :data="formData.items" border size="small">
            <el-table-column label="产品" min-width="200">
              <template #default="{ row, $index }">
                <el-select
                  v-model="row.product_id"
                  filterable
                  placeholder="请选择产品"
                  @change="(val) => handleProductChange(val, $index)"
                >
                  <el-option
                    v-for="product in productList"
                    :key="product.id"
                    :label="product.name"
                    :value="product.id"
                  />
                </el-select>
              </template>
            </el-table-column>
            <el-table-column label="规格" width="120">
              <template #default="{ row }">
                {{ row.spec || '-' }}
              </template>
            </el-table-column>
            <el-table-column label="单价" width="120" align="right">
              <template #default="{ row }">
                ¥{{ Number(row.price || 0).toFixed(2) }}
              </template>
            </el-table-column>
            <el-table-column label="数量" width="120">
              <template #default="{ row, $index }">
                <el-input-number
                  v-model="row.quantity"
                  :min="1"
                  :max="9999"
                  size="small"
                  @change="() => calculateAmount($index)"
                />
              </template>
            </el-table-column>
            <el-table-column label="金额" width="120" align="right">
              <template #default="{ row }">
                ¥{{ Number(row.subtotal || 0).toFixed(2) }}
              </template>
            </el-table-column>
            <el-table-column label="操作" width="80" align="center">
              <template #default="{ $index }">
                <el-button type="danger" link size="small" @click="removeItem($index)">
                  删除
                </el-button>
              </template>
            </el-table-column>
          </el-table>
          <div class="add-item-btn">
            <el-button type="primary" link size="small" @click="addItem">
              <el-icon><Plus /></el-icon>
              添加产品
            </el-button>
          </div>
        </el-form-item>

        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="产品总额">
              <el-input :value="'¥' + Number(formData.total_amount || 0).toFixed(2)" disabled />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="优惠金额">
              <el-input-number
                v-model="formData.discount_amount"
                :min="0"
                :precision="2"
                :controls="false"
                style="width: 100%"
                placeholder="请输入优惠金额"
                @change="calculateTotalAmount"
              />
            </el-form-item>
          </el-col>
        </el-row>

        <el-form-item label="订单金额">
          <el-input :value="'¥' + Number(orderAmount).toFixed(2)" disabled class="order-amount-input" />
        </el-form-item>

        <el-form-item label="备注" prop="remark">
          <el-input v-model="formData.remark" type="textarea" :rows="3" placeholder="请输入备注信息" />
        </el-form-item>
      </el-form>

      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button @click="handleSaveDraft">保存草稿</el-button>
        <el-button type="primary" :loading="submitLoading" @click="handleSubmitForm">
          提交审核
        </el-button>
      </template>
    </el-dialog>

    <el-dialog v-model="detailVisible" title="订单详情" width="1000px">
      <el-descriptions :column="2" border>
        <el-descriptions-item label="订单号">{{ currentOrder.order_no }}</el-descriptions-item>
        <el-descriptions-item label="订单状态">
          <el-tag :type="getStatusType(currentOrder.status)">
            {{ getStatusText(currentOrder.status) }}
          </el-tag>
        </el-descriptions-item>
        <el-descriptions-item label="客户名称">{{ currentOrder.customer_name }}</el-descriptions-item>
        <el-descriptions-item label="联系人">{{ currentOrder.contact_name }}</el-descriptions-item>
        <el-descriptions-item label="联系电话">{{ currentOrder.contact_phone }}</el-descriptions-item>
        <el-descriptions-item label="收货地址" :span="2">{{ currentOrder.delivery_address }}</el-descriptions-item>
        <el-descriptions-item label="产品总额">¥{{ Number(currentOrder.product_amount || 0).toFixed(2) }}</el-descriptions-item>
        <el-descriptions-item label="优惠金额">¥{{ Number(currentOrder.discount_amount || 0).toFixed(2) }}</el-descriptions-item>
        <el-descriptions-item label="订单总额" :span="2">
          <span class="amount">¥{{ Number(currentOrder.total_amount || 0).toFixed(2) }}</span>
        </el-descriptions-item>
        <el-descriptions-item label="备注" :span="2">{{ currentOrder.remark }}</el-descriptions-item>
        <el-descriptions-item label="创建人">{{ currentOrder.create_user_name }}</el-descriptions-item>
        <el-descriptions-item label="创建时间">{{ currentOrder.create_time }}</el-descriptions-item>
      </el-descriptions>

      <el-divider>订单产品</el-divider>
      <el-table :data="currentOrder.items" border size="small">
        <el-table-column prop="product_name" label="产品名称" />
        <el-table-column prop="spec" label="规格" width="120" />
        <el-table-column prop="unit" label="单位" width="80" />
        <el-table-column prop="price" label="单价" width="100" align="right">
          <template #default="{ row }">
            ¥{{ Number(row.price || 0).toFixed(2) }}
          </template>
        </el-table-column>
        <el-table-column prop="quantity" label="数量" width="100" align="center" />
        <el-table-column prop="subtotal" label="金额" width="120" align="right">
          <template #default="{ row }">
            ¥{{ Number(row.subtotal || 0).toFixed(2) }}
          </template>
        </el-table-column>
      </el-table>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Search, Refresh, Plus, Download } from '@element-plus/icons-vue'
import { getList, create, update, deleteOrder, getAll as getCustomerList } from '@/api/modules/order'
import { getAll as getProductList } from '@/api/modules/product'
import { useUserStore } from '@/store/modules/user'

const userStore = useUserStore()

const searchForm = reactive({
  order_no: '',
  customer_name: '',
  status: '',
  date_range: []
})

const tableLoading = ref(false)
const orderList = ref([])
const selectedRows = ref([])
const customerList = ref([])
const productList = ref([])

const pagination = reactive({
  page: 1,
  pageSize: 10,
  total: 0
})

const dialogVisible = ref(false)
const detailVisible = ref(false)
const dialogTitle = ref('')
const submitLoading = ref(false)
const formRef = ref(null)
const currentOrder = ref({})

const formData = reactive({
  id: null,
  customer_id: null,
  customer_name: '',
  contact_name: '',
  contact_phone: '',
  delivery_address: '',
  product_amount: 0,
  discount_amount: 0,
  total_amount: 0,
  items: [],
  remark: ''
})

const formRules = {
  customer_id: [{ required: true, message: '请选择客户', trigger: 'change' }],
  contact_name: [{ required: true, message: '请输入联系人', trigger: 'blur' }],
  contact_phone: [{ required: true, message: '请输入联系电话', trigger: 'blur' }]
}

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

const orderAmount = computed(() => {
  const productAmount = formData.items.reduce((sum, item) => sum + (item.subtotal || 0), 0)
  return productAmount - (formData.discount_amount || 0)
})

const hasPermission = (permission) => {
  return userStore.groups.includes(1) || userStore.permissions?.includes(permission)
}

const loadData = async () => {
  tableLoading.value = true
  try {
    const params = {
      page: pagination.page,
      page_size: pagination.pageSize,
      order_no: searchForm.order_no,
      customer_name: searchForm.customer_name,
      status: searchForm.status
    }
    
    if (searchForm.date_range && searchForm.date_range.length === 2) {
      params.start_date = searchForm.date_range[0]
      params.end_date = searchForm.date_range[1]
    }
    
    const res = await getList(params)
    if (res.code === 200) {
      orderList.value = res.data.list || []
      pagination.total = res.data.total || 0
    }
  } catch (error) {
    console.error('获取订单列表失败:', error)
    ElMessage.error('获取订单列表失败')
  } finally {
    tableLoading.value = false
  }
}

const loadCustomers = async () => {
  try {
    const res = await getCustomerList()
    if (res.code === 200) {
      customerList.value = res.data || []
    }
  } catch (error) {
    console.error('获取客户列表失败:', error)
  }
}

const loadProducts = async () => {
  try {
    const res = await getProductList()
    if (res.code === 200) {
      productList.value = res.data || []
    }
  } catch (error) {
    console.error('获取产品列表失败:', error)
  }
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

const handleAdd = () => {
  dialogTitle.value = '新增订单'
  dialogVisible.value = true
}

const handleEdit = (row) => {
  dialogTitle.value = '编辑订单'
  Object.assign(formData, {
    id: row.id,
    customer_id: row.customer_id,
    customer_name: row.customer_name,
    contact_name: row.contact_name,
    contact_phone: row.contact_phone,
    delivery_address: row.delivery_address,
    product_amount: row.product_amount,
    discount_amount: row.discount_amount,
    total_amount: row.total_amount,
    items: row.items || [],
    remark: row.remark
  })
  dialogVisible.value = true
}

const handleView = (row) => {
  currentOrder.value = row
  detailVisible.value = true
}

const handleSubmit = async (row) => {
  try {
    await ElMessageBox.confirm('确定要提交该订单进行审核吗？', '提示', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    })
    
    const res = await update({ id: row.id, status: 'pending' })
    if (res.code === 200) {
      ElMessage.success('提交成功')
      loadData()
    }
  } catch (error) {
    if (error !== 'cancel') {
      console.error('提交失败:', error)
      ElMessage.error('提交失败')
    }
  }
}

const handleApprove = async (row) => {
  try {
    await ElMessageBox.confirm('确定要审核通过该订单吗？', '提示', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    })
    
    const res = await update({ id: row.id, status: 'approved' })
    if (res.code === 200) {
      ElMessage.success('审核通过')
      loadData()
    }
  } catch (error) {
    if (error !== 'cancel') {
      console.error('审核失败:', error)
      ElMessage.error('审核失败')
    }
  }
}

const handleShip = async (row) => {
  try {
    await ElMessageBox.confirm('确定要标记该订单为已发货吗？', '提示', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    })
    
    const res = await update({ id: row.id, status: 'shipped' })
    if (res.code === 200) {
      ElMessage.success('已标记发货')
      loadData()
    }
  } catch (error) {
    if (error !== 'cancel') {
      console.error('操作失败:', error)
      ElMessage.error('操作失败')
    }
  }
}

const handleComplete = async (row) => {
  try {
    await ElMessageBox.confirm('确定要完成该订单吗？', '提示', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    })
    
    const res = await update({ id: row.id, status: 'completed' })
    if (res.code === 200) {
      ElMessage.success('订单已完成')
      loadData()
    }
  } catch (error) {
    if (error !== 'cancel') {
      console.error('操作失败:', error)
      ElMessage.error('操作失败')
    }
  }
}

const handleCancel = async (row) => {
  try {
    await ElMessageBox.confirm('确定要取消该订单吗？', '提示', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    })
    
    const res = await update({ id: row.id, status: 'cancelled' })
    if (res.code === 200) {
      ElMessage.success('订单已取消')
      loadData()
    }
  } catch (error) {
    if (error !== 'cancel') {
      console.error('操作失败:', error)
      ElMessage.error('操作失败')
    }
  }
}

const handleCustomerChange = (customerId) => {
  const customer = customerList.value.find(c => c.id === customerId)
  if (customer) {
    formData.customer_name = customer.name
    formData.contact_name = customer.contact || ''
    formData.contact_phone = customer.phone || ''
    formData.delivery_address = customer.address || ''
  }
}

const handleProductChange = (productId, index) => {
  const product = productList.value.find(p => p.id === productId)
  if (product) {
    formData.items[index].product_name = product.name
    formData.items[index].spec = product.spec || ''
    formData.items[index].unit = product.unit || ''
    formData.items[index].price = product.price || 0
    calculateAmount(index)
  }
}

const calculateAmount = (index) => {
  const item = formData.items[index]
  item.subtotal = (item.price || 0) * (item.quantity || 0)
  calculateTotalAmount()
}

const calculateTotalAmount = () => {
  formData.product_amount = formData.items.reduce((sum, item) => sum + (item.subtotal || 0), 0)
  formData.total_amount = formData.product_amount - (formData.discount_amount || 0)
}

const addItem = () => {
  formData.items.push({
    product_id: null,
    product_name: '',
    spec: '',
    unit: '',
    price: 0,
    quantity: 1,
    subtotal: 0
  })
}

const removeItem = (index) => {
  formData.items.splice(index, 1)
  calculateTotalAmount()
}

const handleSaveDraft = async () => {
  await handleSubmitForm('draft')
}

const handleSubmitForm = async (status = 'pending') => {
  if (!formRef.value) return
  
  await formRef.value.validate(async (valid) => {
    if (!valid) return
    
    if (formData.items.length === 0) {
      ElMessage.warning('请添加订单产品')
      return
    }
    
    submitLoading.value = true
    try {
      const submitData = {
        ...formData,
        status: status
      }
      
      const res = submitData.id ? await update(submitData) : await create(submitData)
      
      if (res.code === 200) {
        ElMessage.success(submitData.id ? '编辑成功' : '新增成功')
        dialogVisible.value = false
        loadData()
      }
    } catch (error) {
      console.error('提交失败:', error)
      ElMessage.error('操作失败')
    } finally {
      submitLoading.value = false
    }
  })
}

const handleDialogClose = () => {
  formRef.value?.resetFields()
  Object.assign(formData, {
    id: null,
    customer_id: null,
    customer_name: '',
    contact_name: '',
    contact_phone: '',
    delivery_address: '',
    product_amount: 0,
    discount_amount: 0,
    total_amount: 0,
    items: [],
    remark: ''
  })
}

const handleSelectionChange = (rows) => {
  selectedRows.value = rows
}

const handleExport = () => {
  ElMessage.info('导出功能开发中')
}

const handleSizeChange = (size) => {
  pagination.pageSize = size
  loadData()
}

const handlePageChange = (page) => {
  pagination.page = page
  loadData()
}

onMounted(() => {
  loadData()
  loadCustomers()
  loadProducts()
})
</script>

<style scoped>
.order-container {
  padding: 0;
}

.search-card {
  margin-bottom: 20px;
}

.table-card {
  margin-bottom: 20px;
}

.table-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.table-title {
  font-size: 16px;
  font-weight: 600;
  color: #303133;
}

.table-actions {
  display: flex;
  gap: 8px;
}

.pagination-container {
  display: flex;
  justify-content: flex-end;
  margin-top: 20px;
}

.amount {
  color: #f56c6c;
  font-weight: 600;
}

.add-item-btn {
  margin-top: 10px;
}

.order-amount-input :deep(.el-input__inner) {
  color: #f56c6c;
  font-weight: 600;
  font-size: 18px;
}

:deep(.el-table) {
  font-size: 14px;
}

:deep(.el-form-item) {
  margin-bottom: 18px;
}
</style>
