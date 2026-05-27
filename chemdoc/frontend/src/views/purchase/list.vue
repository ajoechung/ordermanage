<template>
  <div class="purchase-container">
    <el-card class="search-card">
      <el-form :inline="true" :model="searchForm">
        <el-form-item label="采购单号">
          <el-input v-model="searchForm.keyword" placeholder="请输入采购单号" clearable @keyup.enter="handleSearch" style="width: 220px" />
        </el-form-item>
        <el-form-item label="供应商">
          <el-select v-model="searchForm.supplier_id" placeholder="请选择" clearable filterable style="width: 220px">
            <el-option v-for="s in supplierList" :key="s.supplier_id" :label="s.name" :value="s.supplier_id" />
          </el-select>
        </el-form-item>
        <el-form-item label="状态">
          <el-select v-model="searchForm.status" placeholder="请选择" clearable style="width: 150px">
            <el-option label="草稿" :value="1" />
            <el-option label="已提交" :value="2" />
            <el-option label="已确认" :value="3" />
            <el-option label="已入库" :value="4" />
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
          <span class="table-title">采购单列表</span>
          <el-button type="primary" @click="handleAdd"><Plus />新增采购单</el-button>
        </div>
      </template>

      <el-table v-loading="tableLoading" :data="purchaseList" stripe border>
        <el-table-column prop="purchase_id" label="ID" width="80" />
        <el-table-column prop="order_no" label="采购单号" width="160" />
        <el-table-column label="关联订单" width="160">
          <template #default="{ row }">{{ row.order_no || '-' }}</template>
        </el-table-column>
        <el-table-column prop="supplier_name" label="供应商" min-width="180" show-overflow-tooltip />
        <el-table-column prop="contact" label="联系人" width="100" />
        <el-table-column prop="phone" label="联系电话" width="130" />
        <el-table-column label="金额" width="120" align="right">
          <template #default="{ row }"><span class="amount">¥{{ Number(row.total_amount || 0).toLocaleString() }}</span></template>
        </el-table-column>
        <el-table-column prop="status" label="状态" width="100">
          <template #default="{ row }"><el-tag :type="getStatusType(row.status)">{{ getStatusText(row.status) }}</el-tag></template>
        </el-table-column>
        <el-table-column prop="expected_date" label="预计到货" width="120" />
        <el-table-column prop="create_time" label="创建时间" width="160" />
        <el-table-column label="操作" width="220" fixed="right">
          <template #default="{ row }">
            <el-button type="primary" link size="small" @click="handleView(row)">查看</el-button>
            <el-button v-if="row.status === 1" type="primary" link size="small" @click="handleEdit(row)">编辑</el-button>
            <el-button v-if="row.status === 1" type="success" link size="small" @click="handleChangeStatus(row, 2)">提交</el-button>
            <el-button v-if="row.status === 2" type="success" link size="small" @click="handleChangeStatus(row, 3)">确认</el-button>
            <el-button v-if="row.status === 3" type="success" link size="small" @click="handleChangeStatus(row, 4)">入库</el-button>
            <el-button v-if="row.status === 4" type="success" link size="small" @click="handleChangeStatus(row, 5)">完成</el-button>
            <el-button v-if="[1, 2].includes(row.status)" type="danger" link size="small" @click="handleChangeStatus(row, 6)">取消</el-button>
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
            <el-form-item label="供应商" prop="supplier_id">
              <el-select v-model="formData.supplier_id" filterable placeholder="请选择" style="width: 100%" @change="handleSupplierChange">
                <el-option v-for="s in supplierList" :key="s.supplier_id" :label="s.name" :value="s.supplier_id" />
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="关联订单">
              <el-select v-model="formData.order_id" filterable placeholder="请选择订单（可选）" clearable style="width: 100%">
                <el-option v-for="o in orderList" :key="o.order_id" :label="o.order_no" :value="o.order_id" />
              </el-select>
            </el-form-item>
          </el-col>
        </el-row>
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="联系人" prop="contact">
              <el-input v-model="formData.contact" placeholder="请输入联系人" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="联系电话" prop="phone">
              <el-input v-model="formData.phone" placeholder="请输入电话" />
            </el-form-item>
          </el-col>
        </el-row>
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="预计到货">
              <el-date-picker v-model="formData.expected_date" type="date" value-format="YYYY-MM-DD" style="width: 100%" />
            </el-form-item>
          </el-col>
        </el-row>

        <el-form-item label="采购产品">
          <el-table :data="formData.items" border size="small">
            <el-table-column label="产品" min-width="220">
              <template #default="{ row, $index }">
                <el-select v-model="row.product_id" filterable placeholder="请选择" @change="(val) => handleProductChange(val, $index)">
                  <el-option v-for="p in productList" :key="p.product_id" :label="p.name" :value="p.product_id" />
                </el-select>
              </template>
            </el-table-column>
            <el-table-column label="规格" width="120">
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
            <el-table-column label="数量" width="150">
              <template #default="{ row, $index }">
                <el-input-number v-model="row.quantity" :min="1" size="small" @change="() => calculateAmount($index)" />
                <span style="margin-left: 5px; color: #909399; font-size: 12px">{{ row.product_unit || '-' }}</span>
              </template>
            </el-table-column>
            <el-table-column label="金额" width="120" align="right">
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
            <el-form-item label="采购总额">
              <el-input :model-value="`¥${Number(purchaseAmount).toFixed(2)}`" disabled />
            </el-form-item>
          </el-col>
        </el-row>

        <el-form-item label="备注">
          <el-input v-model="formData.remark" type="textarea" :rows="3" placeholder="请输入备注" />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" :loading="submitLoading" @click="handleSubmit">确定</el-button>
      </template>
    </el-dialog>

    <el-dialog v-model="detailVisible" title="采购单详情" width="900px">
      <el-descriptions :column="2" border>
        <el-descriptions-item label="采购单号">{{ currentPurchase.order_no }}</el-descriptions-item>
        <el-descriptions-item label="状态">
          <el-tag :type="getStatusType(currentPurchase.status)">{{ getStatusText(currentPurchase.status) }}</el-tag>
        </el-descriptions-item>
        <el-descriptions-item label="供应商">{{ currentPurchase.supplier_name }}</el-descriptions-item>
        <el-descriptions-item label="联系人">{{ currentPurchase.contact }}</el-descriptions-item>
        <el-descriptions-item label="联系电话">{{ currentPurchase.phone }}</el-descriptions-item>
        <el-descriptions-item label="预计到货">{{ currentPurchase.expected_date }}</el-descriptions-item>
        <el-descriptions-item label="采购总额" :span="2">
          <span class="amount">¥{{ Number(currentPurchase.total_amount || 0).toFixed(2) }}</span>
        </el-descriptions-item>
        <el-descriptions-item label="备注" :span="2">{{ currentPurchase.remark }}</el-descriptions-item>
      </el-descriptions>

      <div v-if="currentPurchase.items && currentPurchase.items.length > 0" style="margin-top: 20px;">
        <h4>采购明细</h4>
        <el-table :data="currentPurchase.items" border size="small">
          <el-table-column prop="product_name" label="产品名称" />
          <el-table-column prop="spec" label="规格" width="120" />
          <el-table-column prop="price" label="单价" width="120" align="right">
            <template #default="{ row }">¥{{ Number(row.price || 0).toFixed(2) }}</template>
          </el-table-column>
          <el-table-column label="数量" width="120">
            <template #default="{ row }">
              {{ row.quantity }}<span style="margin-left: 5px; color: #909399; font-size: 12px;">{{ row.product_unit || '-' }}</span>
            </template>
          </el-table-column>
          <el-table-column prop="amount" label="金额" width="120" align="right">
            <template #default="{ row }">¥{{ Number(row.amount || 0).toFixed(2) }}</template>
          </el-table-column>
        </el-table>
      </div>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Search, Refresh, Plus } from '@element-plus/icons-vue'
import { getList as getSupplierList } from '@/api/modules/supplier'
import { getAll as getProductList } from '@/api/modules/product'
import { getAll as getOrderList } from '@/api/modules/order'
import { getPurchaseList, createPurchase, updatePurchase, deletePurchase, updatePurchaseStatus } from '@/api/modules/purchase'

const route = useRoute()

const searchForm = reactive({ keyword: '', supplier_id: '', status: '' })
const tableLoading = ref(false)
const purchaseList = ref([])
const supplierList = ref([])
const productList = ref([])
const orderList = ref([])

const pagination = reactive({ page: 1, pageSize: 20, total: 0 })

const dialogVisible = ref(false)
const detailVisible = ref(false)
const dialogTitle = ref('')
const submitLoading = ref(false)
const formRef = ref(null)
const currentPurchase = ref({})

const formData = reactive({
  purchase_id: null,
  supplier_id: null,
  supplier_name: '',
  order_id: null,
  contact: '',
  phone: '',
  expected_date: '',
  items: [],
  remark: ''
})

const formRules = {
  supplier_id: [{ required: true, message: '请选择供应商', trigger: 'change' }],
  contact: [{ required: true, message: '请输入联系人', trigger: 'blur' }],
  phone: [
    { pattern: /^1[3-9]\d{9}$|^(\d{3,4}-)?\d{7,8}$/, message: '请输入正确的手机号码或座机号码', trigger: 'blur' }
  ]
}

const statusMap = {
  1: { text: '草稿', type: 'info' },
  2: { text: '已提交', type: 'warning' },
  3: { text: '已确认', type: 'primary' },
  4: { text: '已入库', type: 'success' },
  5: { text: '已完成', type: 'success' },
  6: { text: '已取消', type: 'danger' }
}

const getStatusText = (status) => statusMap[status]?.text || '未知'
const getStatusType = (status) => statusMap[status]?.type || 'info'

const purchaseAmount = computed(() => formData.items.reduce((sum, item) => sum + (item.subtotal || 0), 0))

const loadData = async () => {
  tableLoading.value = true
  try {
    const params = { page: pagination.page, pageSize: pagination.pageSize }
    if (searchForm.keyword) params.keyword = searchForm.keyword
    if (searchForm.supplier_id) params.supplier_id = searchForm.supplier_id
    if (searchForm.status !== '') params.status = searchForm.status

    const res = await getPurchaseList(params)
    if (res.code === 200) {
      purchaseList.value = res.data.list || []
      pagination.total = res.data.total || 0
    }
  } catch (error) {
    console.error('获取采购单列表失败:', error)
    ElMessage.error('获取采购单列表失败')
  } finally {
    tableLoading.value = false
  }
}

const loadSuppliers = async () => {
  try {
    const res = await getSupplierList()
    if (res.code === 200) supplierList.value = res.data || []
  } catch (error) {
    console.error('获取供应商列表失败:', error)
  }
}

const loadProducts = async () => {
  try {
    const res = await getProductList({ page: 1, pageSize: 1000 })
    if (res.code === 200) productList.value = res.data.list || []
  } catch (error) {
    console.error('获取产品列表失败:', error)
  }
}

const loadOrders = async () => {
  try {
    const res = await getOrderList({ page: 1, pageSize: 1000 })
    if (res.code === 200) orderList.value = res.data.list || []
  } catch (error) {
    console.error('获取订单列表失败:', error)
  }
}

const handleSearch = () => { pagination.page = 1; loadData() }
const handleReset = () => {
  Object.assign(searchForm, { keyword: '', supplier_id: '', status: '' })
  pagination.page = 1
  loadData()
}

const handleAdd = () => {
  dialogTitle.value = '新增采购单'
  dialogVisible.value = true
}

const handleEdit = async (row) => {
  dialogTitle.value = '编辑采购单'
  Object.keys(formData).forEach(key => {
    formData[key] = row[key] ?? formData[key]
  })
  // 获取详情，包含items
  try {
    const { getPurchaseDetail } = await import('@/api/modules/purchase')
    const res = await getPurchaseDetail(row.purchase_id)
    if (res.code === 200 && res.data.items) {
      formData.items = res.data.items.map(item => ({
          product_id: item.product_id,
          product_name: item.product_name,
          product_spec: item.spec || '',
          product_unit: item.product_unit || '',
          unit_price: item.price || 0,
          quantity: item.quantity || 1,
          subtotal: item.amount || 0
        }))
    }
  } catch (error) {
    console.error('获取详情失败:', error)
  }
  dialogVisible.value = true
}

const handleView = async (row) => {
  currentPurchase.value = row
  // 获取详情，包含items
  try {
    const { getPurchaseDetail } = await import('@/api/modules/purchase')
    const res = await getPurchaseDetail(row.purchase_id)
    if (res.code === 200) {
      currentPurchase.value = res.data
    }
  } catch (error) {
    console.error('获取详情失败:', error)
  }
  detailVisible.value = true
}

const handleChangeStatus = async (row, status) => {
  const statusText = { 2: '提交', 3: '确认', 4: '入库', 5: '完成', 6: '取消' }
  try {
    await ElMessageBox.confirm(`确定要${statusText[status]}该采购单吗？`, '提示', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    })
    const res = await updatePurchaseStatus({ id: row.purchase_id, status: status })
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
    await ElMessageBox.confirm(`确定要删除采购单"${row.order_no}"吗？`, '提示', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    })
    const res = await deletePurchase(row.purchase_id)
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

const handleSupplierChange = (id) => {
  const s = supplierList.value.find(x => x.supplier_id === id)
  if (s) {
    formData.supplier_name = s.name
    formData.contact = s.contact || ''
    formData.phone = s.phone || ''
  }
}

const handleProductChange = (id, index) => {
  const p = productList.value.find(x => x.product_id === id)
  if (p) {
    formData.items[index].product_name = p.name
    formData.items[index].product_spec = p.spec || ''
    formData.items[index].product_unit = p.unit || ''
    formData.items[index].unit_price = p.price || 0
    calculateAmount(index)
  }
}

const handleUnitPriceChange = (index) => {
  calculateAmount(index)
}

const calculateAmount = (index) => {
  formData.items[index].subtotal = (formData.items[index].unit_price || 0) * (formData.items[index].quantity || 0)
}

const addItem = () => {
  formData.items.push({
    product_id: null,
    product_name: '',
    product_spec: '',
    product_unit: '',
    unit_price: 0,
    quantity: 1,
    subtotal: 0
  })
}

const removeItem = (index) => {
  formData.items.splice(index, 1)
}

const handleSubmit = async () => {
  if (!formRef.value) return
  await formRef.value.validate(async (valid) => {
    if (!valid) return
    if (formData.items.length === 0) {
      ElMessage.warning('请添加采购产品')
      return
    }
    submitLoading.value = true
    try {
      const submitData = { ...formData }
      if (submitData.purchase_id) {
        await updatePurchase(submitData.purchase_id, submitData)
      } else {
        delete submitData.purchase_id
        delete submitData.supplier_name
        await createPurchase(submitData)
      }
      ElMessage.success(submitData.purchase_id ? '编辑成功' : '新增成功')
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
    purchase_id: null,
    supplier_id: null,
    supplier_name: '',
    order_id: null,
    contact: '',
    phone: '',
    expected_date: '',
    items: [],
    remark: ''
  })
}

const handleSizeChange = (size) => { pagination.pageSize = size; loadData() }
const handlePageChange = (page) => { pagination.page = page; loadData() }

onMounted(() => {
  loadSuppliers()
  loadProducts()
  loadOrders()
  if (route.query.supplier_id) searchForm.supplier_id = Number(route.query.supplier_id)
  loadData()
})
</script>

<style scoped>
.purchase-container { padding: 0; }
.search-card, .table-card { margin-bottom: 20px; }
.table-header { display: flex; justify-content: space-between; align-items: center; }
.table-title { font-size: 16px; font-weight: 600; color: #303133; }
.pagination-container { display: flex; justify-content: flex-end; margin-top: 20px; }
.amount { color: #f56c6c; font-weight: 600; }
:deep(.el-table) { font-size: 14px; }
</style>