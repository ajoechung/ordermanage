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
        <el-form-item>
          <el-button type="primary" @click="handleSearch"><Search />搜索</el-button>
          <el-button @click="handleReset"><Refresh />重置</el-button>
        </el-form-item>
      </el-form>
    </el-card>

    <el-card class="table-card">
      <template #header>
        <div class="table-header">
          <span class="table-title">订单列表</span>
          <el-button type="primary" @click="handleAdd"><Plus />新增订单</el-button>
        </div>
      </template>

      <el-table v-loading="tableLoading" :data="orderList" stripe border>
        <el-table-column prop="order_id" label="ID" width="80" />
        <el-table-column prop="order_no" label="订单号" width="160" />
        <el-table-column prop="customer_name" label="客户名称" min-width="180" show-overflow-tooltip />
        <el-table-column prop="contact_name" label="联系人" width="100" />
        <el-table-column prop="contact_phone" label="联系电话" width="130" />
        <el-table-column label="订单金额" width="120" align="right">
          <template #default="{ row }"><span class="amount">¥{{ Number(row.total_amount || 0).toLocaleString() }}</span></template>
        </el-table-column>
        <el-table-column prop="status" label="状态" width="100">
          <template #default="{ row }">
            <el-tag :type="getStatusType(row.status)">{{ getStatusText(row.status) }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="create_time" label="创建时间" width="160" />
        <el-table-column label="操作" width="200" fixed="right">
          <template #default="{ row }">
            <el-button type="primary" link size="small" @click="handleView(row)">查看</el-button>
            <el-button v-if="row.status === 'draft'" type="primary" link size="small" @click="handleEdit(row)">编辑</el-button>
            <el-button v-if="['draft', 'cancelled'].includes(row.status)" type="success" link size="small" @click="handleChangeStatus(row, 'pending')">提交</el-button>
            <el-button v-if="row.status === 'pending' && isAdmin" type="success" link size="small" @click="handleChangeStatus(row, 'approved')">审核</el-button>
            <el-button type="danger" link size="small" @click="handleDelete(row)">删除</el-button>
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

    <el-dialog v-model="dialogVisible" :title="dialogTitle" width="900px" :close-on-click-modal="false" @close="handleDialogClose">
      <el-form ref="formRef" :model="formData" :rules="formRules" label-width="100px">
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="客户名称" prop="customer_id">
              <el-select v-model="formData.customer_id" filterable placeholder="请选择客户" style="width: 100%" @change="handleCustomerChange">
                <el-option v-for="c in customerList" :key="c.customer_id" :label="c.name" :value="c.customer_id" />
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
                <el-select v-model="row.product_id" filterable placeholder="请选择" @change="(val) => handleProductChange(val, $index)">
                  <el-option v-for="p in productList" :key="p.product_id" :label="p.name" :value="p.product_id" />
                </el-select>
              </template>
            </el-table-column>
            <el-table-column label="规格" width="100">
              <template #default="{ row }">{{ row.spec || '-' }}</template>
            </el-table-column>
            <el-table-column label="单价" width="100" align="right">
              <template #default="{ row }">¥{{ Number(row.price || 0).toFixed(2) }}</template>
            </el-table-column>
            <el-table-column label="数量" width="140">
              <template #default="{ row, $index }">
                <el-input-number v-model="row.quantity" :min="1" size="small" @change="() => calculateAmount($index)" />
              </template>
            </el-table-column>
            <el-table-column label="金额" width="100" align="right">
              <template #default="{ row }">¥{{ Number(row.subtotal || 0).toFixed(2) }}</template>
            </el-table-column>
            <el-table-column label="操作" width="60" align="center">
              <template #default="{ $index }"><el-button type="danger" link size="small" @click="removeItem($index)">删</el-button>
              </template>
            </el-table-column>
          </el-table>
          <div style="margin-top: 10px"><el-button type="primary" link size="small" @click="addItem"><Plus />添加产品</el-button></div>
        </el-form-item>

        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="产品总额"><el-input :value="`¥${Number(orderAmount).toFixed(2)}`" disabled /></el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="优惠金额">
              <el-input-number v-model="formData.discount_amount" :min="0" :precision="2" :controls="false" style="width: 100%" @change="calculateTotalAmount" />
            </el-form-item>
          </el-col>
        </el-row>

        <el-form-item label="订单总额"><el-input :value="`¥${Number(formData.total_amount).toFixed(2)}`" disabled class="order-amount" /></el-form-item>
        <el-form-item label="备注"><el-input v-model="formData.remark" type="textarea" :rows="3" placeholder="请输入备注" /></el-form-item>
      </el-form>

      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" :loading="submitLoading" @click="handleSubmit">确定</el-button>
      </template>
    </el-dialog>

    <el-dialog v-model="detailVisible" title="订单详情" width="800px">
      <el-descriptions :column="2" border>
        <el-descriptions-item label="订单号">{{ currentOrder.order_no }}</el-descriptions-item>
        <el-descriptions-item label="状态"><el-tag :type="getStatusType(currentOrder.status)">{{ getStatusText(currentOrder.status) }}</el-tag></el-descriptions-item>
        <el-descriptions-item label="客户名称">{{ currentOrder.customer_name }}</el-descriptions-item>
        <el-descriptions-item label="联系人">{{ currentOrder.contact_name }}</el-descriptions-item>
        <el-descriptions-item label="联系电话">{{ currentOrder.contact_phone }}</el-descriptions-item>
        <el-descriptions-item label="收货地址" :span="2">{{ currentOrder.delivery_address }}</el-descriptions-item>
        <el-descriptions-item label="订单总额" :span="2"><span class="amount">¥{{ Number(currentOrder.total_amount || 0).toFixed(2) }}</span></el-descriptions-item>
        <el-descriptions-item label="备注" :span="2">{{ currentOrder.remark }}</el-descriptions-item>
        <el-descriptions-item label="创建时间">{{ currentOrder.create_time }}</el-descriptions-item>
      </el-descriptions>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Search, Refresh, Plus } from '@element-plus/icons-vue'
import { getList, create, update, deleteOrder, getAll as getCustomerList } from '@/api/modules/order'
import { getAll as getProductList } from '@/api/modules/product'
import { useUserStore } from '@/store/modules/user'

const userStore = useUserStore()
const isAdmin = computed(() => userStore.groups?.includes(1))

const searchForm = reactive({ order_no: '', customer_name: '', status: '' })
const tableLoading = ref(false)
const orderList = ref([])
const customerList = ref([])
const productList = ref([])

const pagination = reactive({ page: 1, pageSize: 20, total: 0 })

const dialogVisible = ref(false)
const detailVisible = ref(false)
const dialogTitle = ref('')
const submitLoading = ref(false)
const formRef = ref(null)
const currentOrder = ref({})

const formData = reactive({
  order_id: null,
  customer_id: null,
  customer_name: '',
  contact_name: '',
  contact_phone: '',
  delivery_address: '',
  discount_amount: 0,
  total_amount: 0,
  items: [],
  remark: ''
})

const formRules = {
  customer_id: [{ required: true, message: '请选择客户', trigger: 'change' }],
  contact_name: [{ required: true, message: '请输入联系人', trigger: 'blur' }]
}

const statusMap = { draft: { text: '草稿', type: 'info' }, pending: { text: '待审核', type: 'warning' }, approved: { text: '已审核', type: 'success' }, shipped: { text: '已发货', type: 'primary' }, completed: { text: '已完成', type: 'success' }, cancelled: { text: '已取消', type: 'danger' } }
const getStatusText = (s) => statusMap[s]?.text || s
const getStatusType = (s) => statusMap[s]?.type || 'info'

const orderAmount = computed(() => formData.items.reduce((sum, item) => sum + (item.subtotal || 0), 0))

const loadData = async () => {
  tableLoading.value = true
  try {
    const params = { page: pagination.page, page_size: pagination.pageSize }
    if (searchForm.order_no) params.order_no = searchForm.order_no
    if (searchForm.customer_name) params.customer_name = searchForm.customer_name
    if (searchForm.status) params.status = searchForm.status

    const res = await getList(params)
    if (res.code === 200) { orderList.value = res.data.list || []; pagination.total = res.data.total || 0 }
  } catch (error) { console.error('获取订单列表失败:', error); ElMessage.error('获取订单列表失败') }
  finally { tableLoading.value = false }
}

const loadCustomers = async () => {
  try { const res = await getCustomerList(); if (res.code === 200) customerList.value = res.data || [] }
  catch (error) { console.error('获取客户列表失败:', error) }
}

const loadProducts = async () => {
  try { const res = await getProductList(); if (res.code === 200) productList.value = res.data || [] }
  catch (error) { console.error('获取产品列表失败:', error) }
}

const handleSearch = () => { pagination.page = 1; loadData() }
const handleReset = () => { Object.assign(searchForm, { order_no: '', customer_name: '', status: '' }); pagination.page = 1; loadData() }
const handleAdd = () => { dialogTitle.value = '新增订单'; dialogVisible.value = true }
const handleEdit = (row) => {
  dialogTitle.value = '编辑订单'
  Object.keys(formData).forEach(key => { formData[key] = row[key] ?? formData[key] })
  dialogVisible.value = true
}
const handleView = (row) => { currentOrder.value = row; detailVisible.value = true }

const handleChangeStatus = async (row, status) => {
  try {
    await ElMessageBox.confirm(`确定要${status === 'pending' ? '提交' : '审核通过'}该订单吗？`, '提示', { confirmButtonText: '确定', cancelButtonText: '取消', type: 'warning' })
    const res = await update(row.order_id, { status })
    if (res.code === 200) { ElMessage.success('操作成功'); loadData() }
  } catch (error) { if (error !== 'cancel') console.error('操作失败:', error) }
}

const handleDelete = async (row) => {
  try {
    await ElMessageBox.confirm(`确定要删除订单"${row.order_no}"吗？`, '提示', { confirmButtonText: '确定', cancelButtonText: '取消', type: 'warning' })
    const res = await deleteOrder(row.order_id)
    if (res.code === 200) { ElMessage.success('删除成功'); loadData() }
  } catch (error) { if (error !== 'cancel') console.error('删除失败:', error) }
}

const handleCustomerChange = (id) => {
  const c = customerList.value.find(x => x.customer_id === id)
  if (c) { formData.customer_name = c.name; formData.contact_name = c.contact || ''; formData.contact_phone = c.phone || ''; formData.delivery_address = c.address || '' }
}

const handleProductChange = (id, index) => {
  const p = productList.value.find(x => x.product_id === id)
  if (p) { formData.items[index].product_name = p.name; formData.items[index].spec = p.spec || ''; formData.items[index].unit = p.unit || ''; formData.items[index].price = p.price || 0; calculateAmount(index) }
}

const calculateAmount = (index) => { formData.items[index].subtotal = (formData.items[index].price || 0) * (formData.items[index].quantity || 0); calculateTotalAmount() }
const calculateTotalAmount = () => { formData.total_amount = orderAmount.value - (formData.discount_amount || 0) }

const addItem = () => { formData.items.push({ product_id: null, product_name: '', spec: '', unit: '', price: 0, quantity: 1, subtotal: 0 }) }
const removeItem = (index) => { formData.items.splice(index, 1); calculateTotalAmount() }

const handleSubmit = async () => {
  if (!formRef.value) return
  await formRef.value.validate(async (valid) => {
    if (!valid) return
    if (formData.items.length === 0) { ElMessage.warning('请添加订单产品'); return }
    submitLoading.value = true
    try {
      const submitData = { ...formData }
      if (submitData.order_id) { await update(submitData.order_id, submitData) }
      else { delete submitData.order_id; await create(submitData) }
      ElMessage.success(submitData.order_id ? '编辑成功' : '新增成功')
      dialogVisible.value = false
      loadData()
    } catch (error) { console.error('提交失败:', error); ElMessage.error('操作失败') }
    finally { submitLoading.value = false }
  })
}

const handleDialogClose = () => {
  formRef.value?.resetFields()
  Object.assign(formData, { order_id: null, customer_id: null, customer_name: '', contact_name: '', contact_phone: '', delivery_address: '', discount_amount: 0, total_amount: 0, items: [], remark: '' })
}

const handleSizeChange = (size) => { pagination.pageSize = size; loadData() }
const handlePageChange = (page) => { pagination.page = page; loadData() }

onMounted(() => { loadData(); loadCustomers(); loadProducts() })
</script>

<style scoped>
.order-container { padding: 0; }
.search-card, .table-card { margin-bottom: 20px; }
.table-header { display: flex; justify-content: space-between; align-items: center; }
.table-title { font-size: 16px; font-weight: 600; color: #303133; }
.pagination-container { display: flex; justify-content: flex-end; margin-top: 20px; }
.amount { color: #f56c6c; font-weight: 600; }
.order-amount :deep(.el-input__inner) { color: #f56c6c; font-weight: 600; font-size: 18px; }
:deep(.el-table) { font-size: 14px; }
</style>
