<template>
  <div class="order-container">
    <el-card class="search-card">
      <el-form :inline="true" :model="searchForm" class="search-form">
        <el-form-item label="订单号">
          <el-input v-model="searchForm.keyword" placeholder="请输入订单号" clearable @keyup.enter="handleSearch" style="width: 220px" />
        </el-form-item>
        <el-form-item label="客户名称">
          <el-input v-model="searchForm.customer_name" placeholder="请输入客户名称" clearable @keyup.enter="handleSearch" style="width: 220px" />
        </el-form-item>
        <el-form-item label="订单状态">
          <el-select v-model="searchForm.order_status" placeholder="请选择" clearable filterable style="width: 150px">
            <el-option label="待确认" :value="1" />
            <el-option label="已确认" :value="2" />
            <el-option label="生产中" :value="3" />
            <el-option label="已发货" :value="4" />
            <el-option label="已完成" :value="5" />
            <el-option label="已取消" :value="6" />
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
          <template #default="{ row }"><span class="amount">¥{{ Number(row.actual_amount || 0).toLocaleString() }}</span></template>
        </el-table-column>
        <el-table-column prop="order_status" label="状态" width="100">
          <template #default="{ row }">
            <el-tag :type="getStatusType(row.order_status)">{{ getStatusText(row.order_status) }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="create_time" label="创建时间" width="160" />
        <el-table-column label="操作" width="260" fixed="right">
          <template #default="{ row }">
            <el-button type="primary" link size="small" @click="handleView(row)">查看</el-button>
            <el-button v-if="row.order_status === 1" type="primary" link size="small" @click="handleEdit(row)">编辑</el-button>
            <el-button v-if="row.order_status === 1" type="success" link size="small" @click="handleChangeStatus(row, 2)">确认</el-button>
            <el-button v-if="row.order_status === 2" type="success" link size="small" @click="handleChangeStatus(row, 3)">生产</el-button>
            <el-button v-if="row.order_status === 3" type="success" link size="small" @click="handleChangeStatus(row, 4)">发货</el-button>
            <el-button v-if="row.order_status === 4" type="success" link size="small" @click="handleChangeStatus(row, 5)">完成</el-button>
            <el-button v-if="[1, 2].includes(row.order_status)" type="danger" link size="small" @click="handleChangeStatus(row, 6)">取消</el-button>
            <el-button type="primary" link size="small" icon="Upload" @click="openInvoiceDialog(row)">发票</el-button>
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
              <el-select 
                v-model="formData.selected_contact_id" 
                placeholder="请选择联系人" 
                clearable
                filterable
                @change="handleContactChange"
                style="width: 100%"
              >
                <el-option 
                  v-for="contact in customerContacts" 
                  :key="contact.contact_id" 
                  :label="contact.name" 
                  :value="contact.contact_id" 
                />
              </el-select>
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
              <template #default="{ row }">{{ row.product_spec || '-' }}</template>
            </el-table-column>
            <el-table-column label="单价" width="120" align="right">
              <template #default="{ row, $index }">
                <el-input-number 
                  v-model="row.unit_price" 
                  :min="0" 
                  :precision="2" 
                  :controls="false" 
                  size="small"
                  style="width: 100%"
                  @change="() => handleUnitPriceChange($index)"
                />
              </template>
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
              <template #default="{ $index }">
                <el-button type="danger" link size="small" @click="removeItem($index)">删</el-button>
              </template>
            </el-table-column>
          </el-table>
          <div style="margin-top: 10px">
            <el-button type="primary" link size="small" @click="addItem"><Plus />添加产品</el-button>
          </div>
        </el-form-item>

        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="产品总额">
              <el-input :model-value="`¥${Number(orderAmount).toFixed(2)}`" disabled />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="优惠金额">
              <el-input-number v-model="formData.discount_amount" :min="0" :precision="2" :controls="false" style="width: 100%" @change="calculateTotalAmount" />
            </el-form-item>
          </el-col>
        </el-row>

        <el-form-item label="订单总额">
          <el-input :model-value="`¥${Number(formData.actual_amount || 0).toFixed(2)}`" disabled class="order-amount" />
        </el-form-item>

        <el-form-item label="备注">
          <el-input v-model="formData.remark" type="textarea" :rows="3" placeholder="请输入备注" />
        </el-form-item>
      </el-form>

      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" :loading="submitLoading" @click="handleSubmit">确定</el-button>
      </template>
    </el-dialog>

    <el-dialog v-model="detailVisible" title="订单详情" width="900px">
      <el-descriptions :column="2" border>
        <el-descriptions-item label="订单号">{{ currentOrder.order_no }}</el-descriptions-item>
        <el-descriptions-item label="状态">
          <el-tag :type="getStatusType(currentOrder.order_status)">{{ getStatusText(currentOrder.order_status) }}</el-tag>
        </el-descriptions-item>
        <el-descriptions-item label="客户名称">{{ currentOrder.customer_name }}</el-descriptions-item>
        <el-descriptions-item label="联系人">{{ currentOrder.contact_name }}</el-descriptions-item>
        <el-descriptions-item label="联系电话">{{ currentOrder.contact_phone }}</el-descriptions-item>
        <el-descriptions-item label="收货地址" :span="2">{{ currentOrder.delivery_address }}</el-descriptions-item>
        <el-descriptions-item label="订单总额" :span="2">
          <span class="amount">¥{{ Number(currentOrder.actual_amount || 0).toFixed(2) }}</span>
        </el-descriptions-item>
        <el-descriptions-item label="备注" :span="2">{{ currentOrder.remark }}</el-descriptions-item>
        <el-descriptions-item label="创建时间">{{ currentOrder.create_time }}</el-descriptions-item>
      </el-descriptions>
      
      <div v-if="currentOrder.items && currentOrder.items.length > 0" style="margin-top: 20px;">
        <h4 style="margin-bottom: 10px; font-weight: 600;">订单明细</h4>
        <el-table :data="currentOrder.items" border size="small">
          <el-table-column prop="product_name" label="产品名称" min-width="200" />
          <el-table-column prop="product_spec" label="规格" width="120" />
          <el-table-column prop="unit_price" label="单价" width="100" align="right">
            <template #default="{ row }">¥{{ Number(row.unit_price || row.price || 0).toFixed(2) }}</template>
          </el-table-column>
          <el-table-column prop="quantity" label="数量" width="100" />
          <el-table-column label="金额" width="120" align="right">
            <template #default="{ row }">¥{{ Number(row.subtotal || row.amount || 0).toFixed(2) }}</template>
          </el-table-column>
        </el-table>
      </div>
    </el-dialog>

    <el-dialog v-model="invoiceDialogVisible" :title="invoiceDialogTitle" width="600px" @close="closeInvoiceDialog">
      <div class="invoice-container">
        <div class="upload-area">
          <el-button 
            type="primary" 
            :loading="invoiceUploading" 
            icon="Upload"
            @click="triggerInvoiceUpload"
          >
            上传发票
          </el-button>
          <input 
            ref="invoiceFileInput"
            type="file" 
            accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" 
            @change="handleInvoiceUpload"
            style="display: none"
          />
          <span class="upload-tip">支持 PDF、DOC、DOCX、JPG、PNG 格式</span>
        </div>

        <div class="invoice-list">
          <el-table v-if="invoiceList.length > 0" :data="invoiceList" border size="small">
            <el-table-column prop="file_name" label="文件名" min-width="200" />
            <el-table-column prop="upload_time" label="上传时间" width="160" />
            <el-table-column prop="file_size" label="文件大小" width="100" />
            <el-table-column label="操作" width="120">
              <template #default="{ row }">
                <el-button type="primary" link size="small" icon="Eye" @click="previewInvoice(row)">预览</el-button>
                <el-button type="danger" link size="small" icon="Delete" @click="deleteInvoice(row)">删除</el-button>
              </template>
            </el-table-column>
          </el-table>
          <div v-else class="empty-tip">
            <div style="font-size: 48px; color: #ccc; margin-bottom: 10px;">📄</div>
            <p>暂无发票，请上传</p>
          </div>
        </div>
      </div>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Search, Refresh, Plus, Upload, Eye } from '@element-plus/icons-vue'
import { getList, create, update, deleteOrder, updateOrderStatus, getOrderDetail } from '@/api/modules/order'
import { getAll as getCustomerList } from '@/api/modules/customer'
import { getAll as getProductList } from '@/api/modules/product'
import { useUserStore } from '@/store/modules/user'

const userStore = useUserStore()

const searchForm = reactive({ keyword: '', customer_name: '', order_status: '' })
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
const invoiceFileInput = ref(null)

const formData = reactive({
  order_id: null,
  customer_id: null,
  customer_name: '',
  contact_name: '',
  contact_phone: '',
  delivery_address: '',
  discount_amount: 0,
  actual_amount: 0,
  items: [],
  remark: '',
  selected_contact_id: null
})

const formRules = {
  customer_id: [{ required: true, message: '请选择客户', trigger: 'change' }],
  contact_name: [{ required: true, message: '请输入联系人', trigger: 'blur' }]
}

const statusMap = {
  1: { text: '待确认', type: 'warning' },
  2: { text: '已确认', type: 'primary' },
  3: { text: '生产中', type: 'info' },
  4: { text: '已发货', type: 'success' },
  5: { text: '已完成', type: 'success' },
  6: { text: '已取消', type: 'danger' }
}

const getStatusText = (status) => statusMap[status]?.text || '未知'
const getStatusType = (status) => statusMap[status]?.type || 'info'

const orderAmount = computed(() => formData.items.reduce((sum, item) => sum + (item.subtotal || 0), 0))

const loadData = async () => {
  tableLoading.value = true
  try {
    const params = { page: pagination.page, page_size: pagination.pageSize }
    if (searchForm.keyword) params.keyword = searchForm.keyword
    if (searchForm.customer_name) params.keyword = searchForm.customer_name
    if (searchForm.order_status !== '') params.order_status = searchForm.order_status

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
    const res = await getCustomerList({ page: 1, page_size: 1000 })
    if (res.code === 200) customerList.value = res.data.list || []
  } catch (error) {
    console.error('获取客户列表失败:', error)
  }
}

const loadProducts = async () => {
  try {
    const res = await getProductList({ page: 1, page_size: 1000 })
    if (res.code === 200) productList.value = res.data.list || []
  } catch (error) {
    console.error('获取产品列表失败:', error)
  }
}

// 客户对应的联系人列表
const customerContacts = ref([])

// 发票相关
const invoiceDialogVisible = ref(false)
const invoiceDialogTitle = ref('')
const currentOrderId = ref(null)
const invoiceList = ref([])
const invoiceUploading = ref(false)

// 打开发票管理弹窗
const openInvoiceDialog = async (row) => {
  currentOrderId.value = row.order_id
  invoiceDialogTitle.value = `订单 ${row.order_no} - 发票管理`
  await loadInvoiceList(row.order_id)
  invoiceDialogVisible.value = true
}

// 触发发票上传
const triggerInvoiceUpload = () => {
  if (invoiceFileInput.value) {
    invoiceFileInput.value.click()
  }
}

// 加载发票列表
const loadInvoiceList = async (orderId) => {
  try {
    const { getInvoiceList } = await import('@/api/modules/order')
    const res = await getInvoiceList(orderId)
    if (res.code === 200) {
      invoiceList.value = res.data || []
    }
  } catch (error) {
    console.error('获取发票列表失败:', error)
  }
}

// 上传发票
const handleInvoiceUpload = async (event) => {
  const file = event.target.files[0]
  if (!file) return
  
  invoiceUploading.value = true
  const formData = new FormData()
  formData.append('order_id', currentOrderId.value)
  formData.append('file', file)
  
  try {
    const { uploadInvoice } = await import('@/api/modules/order')
    const res = await uploadInvoice(formData)
    if (res.code === 200) {
      ElMessage.success('上传成功')
      await loadInvoiceList(currentOrderId.value)
    } else {
      ElMessage.error(res.msg || '上传失败')
    }
  } catch (error) {
    console.error('上传失败:', error)
    ElMessage.error('上传失败')
  } finally {
    invoiceUploading.value = false
    event.target.value = ''
  }
}

// 预览发票
const previewInvoice = (invoice) => {
  if (invoice.file_path) {
    window.open(invoice.file_path, '_blank')
  }
}

// 删除发票
const deleteInvoice = async (invoice) => {
  try {
    await ElMessageBox.confirm('确定要删除该发票吗？', '提示', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    })
    const { deleteInvoice } = await import('@/api/modules/order')
    const res = await deleteInvoice(invoice.invoice_id)
    if (res.code === 200) {
      ElMessage.success('删除成功')
      await loadInvoiceList(currentOrderId.value)
    } else {
      ElMessage.error(res.msg || '删除失败')
    }
  } catch (error) {
    if (error !== 'cancel') console.error('删除失败:', error)
  }
}

// 关闭发票弹窗
const closeInvoiceDialog = () => {
  invoiceDialogVisible.value = false
  currentOrderId.value = null
  invoiceList.value = []
}

// 加载客户对应的联系人
const loadCustomerContacts = async (customerId) => {
  if (!customerId) {
    customerContacts.value = []
    return
  }
  try {
    const { getList } = await import('@/api/modules/contact')
    const res = await getList({ customer_id: customerId, page: 1, page_size: 100 })
    if (res.code === 200) customerContacts.value = res.data.list || []
  } catch (error) {
    console.error('获取联系人列表失败:', error)
  }
}

const handleSearch = () => { pagination.page = 1; loadData() }
const handleReset = () => {
  Object.assign(searchForm, { keyword: '', customer_name: '', order_status: '' })
  pagination.page = 1
  loadData()
}

const handleAdd = () => {
  dialogTitle.value = '新增订单'
  dialogVisible.value = true
}

const handleEdit = async (row) => {
  dialogTitle.value = '编辑订单'
  
  try {
    const res = await getOrderDetail(row.order_id)
    if (res.code === 200) {
      const detail = res.data
      Object.keys(formData).forEach(key => {
        formData[key] = detail[key] ?? formData[key]
      })
      if (detail.items) {
        formData.items = detail.items.map(item => ({
          product_id: item.product_id,
          product_name: item.product_name || item.product?.name || '',
          product_spec: item.product_spec || item.product?.spec || '',
          unit_price: item.unit_price || item.price || 0,
          quantity: item.quantity,
          subtotal: item.subtotal || item.amount || 0
        }))
      } else {
        formData.items = []
      }
      if (detail.customer_id) {
        await loadCustomerContacts(detail.customer_id)
        if (detail.contact_id) {
          formData.selected_contact_id = detail.contact_id
        } else if (detail.contact_name) {
          const contact = customerContacts.value.find(c => c.name === detail.contact_name)
          if (contact) {
            formData.selected_contact_id = contact.contact_id
          }
        }
      }
    }
  } catch (error) {
    console.error('获取订单详情失败:', error)
  }
  
  dialogVisible.value = true
}

const handleView = async (row) => {
  currentOrder.value = { ...row }
  try {
    const res = await getOrderDetail(row.order_id)
    if (res.code === 200) {
      // 合并数据，确保既有基本信息又有明细
      currentOrder.value = { ...currentOrder.value, ...res.data }
    }
  } catch (error) {
    console.error('获取订单详情失败:', error)
  }
  detailVisible.value = true
}

const handleChangeStatus = async (row, status) => {
  const statusText = { 2: '确认', 3: '开始生产', 4: '确认发货', 5: '确认完成', 6: '取消' }
  try {
    await ElMessageBox.confirm(`确定要${statusText[status]}该订单吗？`, '提示', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    })
    const res = await updateOrderStatus({ id: row.order_id, status: status })
    if (res.code === 200) {
      ElMessage.success('操作成功')
      loadData()
    } else {
      ElMessage.error(res.msg || '操作失败')
    }
  } catch (error) {
    if (error !== 'cancel') console.error('操作失败:', error)
  }
}

const handleDelete = async (row) => {
  try {
    await ElMessageBox.confirm(`确定要删除订单"${row.order_no}"吗？`, '提示', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    })
    const res = await deleteOrder(row.order_id)
    if (res.code === 200) {
      ElMessage.success('删除成功')
      loadData()
    } else {
      ElMessage.error(res.msg || '删除失败')
    }
  } catch (error) {
    if (error !== 'cancel') console.error('删除失败:', error)
  }
}

const handleCustomerChange = async (id) => {
  const c = customerList.value.find(x => x.customer_id === id)
  if (c) {
    formData.customer_name = c.name
    formData.delivery_address = c.address || ''
    // 清空联系人相关信息
    formData.contact_name = ''
    formData.contact_phone = ''
    // 加载该客户的联系人列表
    await loadCustomerContacts(id)
  } else {
    customerContacts.value = []
  }
}

const handleContactChange = (id) => {
  const contact = customerContacts.value.find(x => x.contact_id === id)
  if (contact) {
    formData.contact_name = contact.name || ''
    formData.contact_phone = contact.mobile || contact.phone || ''
  }
}

const handleProductChange = (id, index) => {
  const p = productList.value.find(x => x.product_id === id)
  if (p) {
    formData.items[index].product_name = p.name
    formData.items[index].product_spec = p.spec || ''
    formData.items[index].unit_price = p.price || 0
    calculateAmount(index)
  }
}

// 单价变化时重新计算金额
const handleUnitPriceChange = (index) => {
  calculateAmount(index)
}

const calculateAmount = (index) => {
  formData.items[index].subtotal = (formData.items[index].unit_price || 0) * (formData.items[index].quantity || 0)
  calculateTotalAmount()
}

const calculateTotalAmount = () => {
  formData.actual_amount = orderAmount.value - (formData.discount_amount || 0)
}

const addItem = () => {
  formData.items.push({
    product_id: null,
    product_name: '',
    product_spec: '',
    unit_price: 0,
    quantity: 1,
    subtotal: 0
  })
}

const removeItem = (index) => {
  formData.items.splice(index, 1)
  calculateTotalAmount()
}

const handleSubmit = async () => {
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
        customer_id: formData.customer_id,
        contact_name: formData.contact_name,
        contact_phone: formData.contact_phone,
        delivery_address: formData.delivery_address,
        discount_amount: formData.discount_amount || 0,
        actual_amount: formData.actual_amount,
        items: formData.items.map(item => ({
          product_id: item.product_id,
          unit_price: item.unit_price,
          quantity: item.quantity
        })),
        remark: formData.remark
      }

      if (formData.order_id) {
        await update(formData.order_id, submitData)
      } else {
        await create(submitData)
      }
      ElMessage.success(formData.order_id ? '编辑成功' : '新增成功')
      dialogVisible.value = false
      loadData()
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
    order_id: null,
    customer_id: null,
    customer_name: '',
    contact_name: '',
    contact_phone: '',
    delivery_address: '',
    discount_amount: 0,
    actual_amount: 0,
    items: [],
    remark: '',
    selected_contact_id: null
  })
  customerContacts.value = []
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
.order-container { padding: 0; }
.search-card, .table-card { margin-bottom: 20px; }
.table-header { display: flex; justify-content: space-between; align-items: center; }
.table-title { font-size: 16px; font-weight: 600; color: #303133; }
.pagination-container { display: flex; justify-content: flex-end; margin-top: 20px; }
.amount { color: #f56c6c; font-weight: 600; }
.order-amount :deep(.el-input__inner) { color: #f56c6c; font-weight: 600; font-size: 18px; }
:deep(.el-table) { font-size: 14px; }

.invoice-container { padding: 10px; }
.upload-area { display: flex; align-items: center; gap: 15px; margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid #ebeef5; }
.upload-tip { font-size: 12px; color: #909399; }
.invoice-list { margin-top: 10px; }
.empty-tip { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 40px 0; color: #909399; }
.empty-tip p { margin-top: 10px; }
</style>
