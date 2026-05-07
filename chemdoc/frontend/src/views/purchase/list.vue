<template>
  <div class="purchase-container">
    <el-card class="search-card">
      <el-form :inline="true" :model="searchForm" class="search-form">
        <el-form-item label="采购单号">
          <el-input v-model="searchForm.order_no" placeholder="请输入采购单号" clearable @keyup.enter="handleSearch" />
        </el-form-item>
        <el-form-item label="供应商">
          <el-select v-model="searchForm.supplier_id" placeholder="请选择" clearable filterable>
            <el-option
              v-for="supplier in supplierList"
              :key="supplier.id"
              :label="supplier.name"
              :value="supplier.id"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="采购状态">
          <el-select v-model="searchForm.status" placeholder="请选择" clearable>
            <el-option label="草稿" value="draft" />
            <el-option label="待审核" value="pending" />
            <el-option label="已审核" value="approved" />
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
          <span class="table-title">采购单列表</span>
          <div class="table-actions">
            <el-button type="primary" @click="handleAdd">
              <el-icon><Plus /></el-icon>
              新增采购单
            </el-button>
          </div>
        </div>
      </template>

      <el-table
        v-loading="tableLoading"
        :data="purchaseList"
        stripe
        border
      >
        <el-table-column prop="order_no" label="采购单号" width="160" />
        <el-table-column prop="supplier_name" label="供应商" min-width="180" show-overflow-tooltip />
        <el-table-column prop="contact" label="联系人" width="120" />
        <el-table-column prop="phone" label="联系电话" width="130" />
        <el-table-column label="采购金额" width="120" align="right">
          <template #default="{ row }">
            <span class="amount">¥{{ Number(row.total_amount || 0).toLocaleString() }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="product_count" label="产品数量" width="100" align="center" />
        <el-table-column prop="status" label="状态" width="100">
          <template #default="{ row }">
            <el-tag :type="getStatusType(row.status)">
              {{ getStatusText(row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="create_user_name" label="创建人" width="100" />
        <el-table-column prop="create_time" label="创建时间" width="160" />
        <el-table-column label="操作" width="280" fixed="right">
          <template #default="{ row }">
            <el-button type="primary" link size="small" @click="handleView(row)">
              查看
            </el-button>
            <el-button
              v-if="row.status === 'draft'"
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
              提交
            </el-button>
            <el-button
              v-if="row.status === 'pending'"
              type="success"
              link
              size="small"
              @click="handleApprove(row)"
            >
              审核
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
      width="800px"
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
            <el-form-item label="供应商" prop="supplier_id">
              <el-select
                v-model="formData.supplier_id"
                filterable
                placeholder="请选择供应商"
                style="width: 100%"
                @change="handleSupplierChange"
              >
                <el-option
                  v-for="supplier in supplierList"
                  :key="supplier.id"
                  :label="supplier.name"
                  :value="supplier.id"
                />
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="联系人" prop="contact">
              <el-input v-model="formData.contact" placeholder="请输入联系人" />
            </el-form-item>
          </el-col>
        </el-row>

        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="联系电话" prop="phone">
              <el-input v-model="formData.phone" placeholder="请输入联系电话" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="预计到货">
              <el-date-picker
                v-model="formData.expected_date"
                type="date"
                placeholder="选择日期"
                value-format="YYYY-MM-DD"
                style="width: 100%"
              />
            </el-form-item>
          </el-col>
        </el-row>

        <el-form-item label="采购产品">
          <el-table :data="formData.items" border size="small">
            <el-table-column label="产品" min-width="200">
              <template #default="{ row, $index }">
                <el-input v-model="row.product_name" placeholder="请输入产品名称" />
              </template>
            </el-table-column>
            <el-table-column label="规格" width="120">
              <template #default="{ row }">
                <el-input v-model="row.spec" placeholder="规格" />
              </template>
            </el-table-column>
            <el-table-column label="单价" width="120">
              <template #default="{ row }">
                <el-input-number
                  v-model="row.price"
                  :min="0"
                  :precision="2"
                  :controls="false"
                  size="small"
                  style="width: 100%"
                  @change="() => calculateAmount($index)"
                />
              </template>
            </el-table-column>
            <el-table-column label="数量" width="120">
              <template #default="{ row, $index }">
                <el-input-number
                  v-model="row.quantity"
                  :min="1"
                  size="small"
                  @change="() => calculateAmount($index)"
                />
              </template>
            </el-table-column>
            <el-table-column label="金额" width="120">
              <template #default="{ row }">
                ¥{{ Number(row.subtotal || 0).toFixed(2) }}
              </template>
            </el-table-column>
            <el-table-column label="操作" width="80">
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

        <el-form-item label="备注">
          <el-input v-model="formData.remark" type="textarea" :rows="3" placeholder="请输入备注" />
        </el-form-item>
      </el-form>

      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" :loading="submitLoading" @click="handleSubmitForm">
          确定
        </el-button>
      </template>
    </el-dialog>

    <el-dialog v-model="detailVisible" title="采购单详情" width="900px">
      <el-descriptions :column="2" border>
        <el-descriptions-item label="采购单号">{{ currentPurchase.order_no }}</el-descriptions-item>
        <el-descriptions-item label="状态">
          <el-tag :type="getStatusType(currentPurchase.status)">
            {{ getStatusText(currentPurchase.status) }}
          </el-tag>
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
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Search, Refresh, Plus } from '@element-plus/icons-vue'
import { getPurchaseList, createPurchase, updatePurchase } from '@/api/modules/supplier'
import { getList as getSupplierList } from '@/api/modules/supplier'

const route = useRoute()

const searchForm = reactive({
  order_no: '',
  supplier_id: '',
  status: '',
  date_range: []
})

const tableLoading = ref(false)
const purchaseList = ref([])
const supplierList = ref([])

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
const currentPurchase = ref({})

const formData = reactive({
  id: null,
  supplier_id: null,
  supplier_name: '',
  contact: '',
  phone: '',
  expected_date: '',
  items: [],
  remark: ''
})

const formRules = {
  supplier_id: [{ required: true, message: '请选择供应商', trigger: 'change' }],
  contact: [{ required: true, message: '请输入联系人', trigger: 'blur' }],
  phone: [{ required: true, message: '请输入联系电话', trigger: 'blur' }]
}

const statusMap = {
  draft: { text: '草稿', type: 'info' },
  pending: { text: '待审核', type: 'warning' },
  approved: { text: '已审核', type: 'success' },
  completed: { text: '已完成', type: 'success' },
  cancelled: { text: '已取消', type: 'danger' }
}

const getStatusText = (status) => statusMap[status]?.text || status
const getStatusType = (status) => statusMap[status]?.type || 'info'

const loadData = async () => {
  tableLoading.value = true
  try {
    const params = {
      page: pagination.page,
      page_size: pagination.pageSize,
      order_no: searchForm.order_no,
      supplier_id: searchForm.supplier_id,
      status: searchForm.status
    }
    
    if (searchForm.date_range && searchForm.date_range.length === 2) {
      params.start_date = searchForm.date_range[0]
      params.end_date = searchForm.date_range[1]
    }
    
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
    if (res.code === 200) {
      supplierList.value = res.data || []
    }
  } catch (error) {
    console.error('获取供应商列表失败:', error)
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
  dialogTitle.value = '新增采购单'
  dialogVisible.value = true
}

const handleEdit = (row) => {
  dialogTitle.value = '编辑采购单'
  Object.assign(formData, {
    id: row.id,
    supplier_id: row.supplier_id,
    supplier_name: row.supplier_name,
    contact: row.contact,
    phone: row.phone,
    expected_date: row.expected_date,
    items: row.items || [],
    remark: row.remark
  })
  dialogVisible.value = true
}

const handleView = (row) => {
  currentPurchase.value = row
  detailVisible.value = true
}

const handleSupplierChange = (supplierId) => {
  const supplier = supplierList.value.find(s => s.id === supplierId)
  if (supplier) {
    formData.supplier_name = supplier.name
    formData.contact = supplier.contact || ''
    formData.phone = supplier.phone || ''
  }
}

const calculateAmount = (index) => {
  const item = formData.items[index]
  item.subtotal = (item.price || 0) * (item.quantity || 0)
}

const addItem = () => {
  formData.items.push({
    product_name: '',
    spec: '',
    price: 0,
    quantity: 1,
    subtotal: 0
  })
}

const removeItem = (index) => {
  formData.items.splice(index, 1)
}

const handleSubmitForm = async () => {
  if (!formRef.value) return
  
  await formRef.value.validate(async (valid) => {
    if (!valid) return
    
    submitLoading.value = true
    try {
      const res = formData.id 
        ? await updatePurchase(formData) 
        : await createPurchase(formData)
      
      if (res.code === 200) {
        ElMessage.success(formData.id ? '编辑成功' : '新增成功')
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
    supplier_id: null,
    supplier_name: '',
    contact: '',
    phone: '',
    expected_date: '',
    items: [],
    remark: ''
  })
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
  loadSuppliers()
  
  if (route.query.supplier_id) {
    searchForm.supplier_id = Number(route.query.supplier_id)
  }
  
  loadData()
})
</script>

<style scoped>
.purchase-container {
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

:deep(.el-table) {
  font-size: 14px;
}

:deep(.el-form-item) {
  margin-bottom: 18px;
}
</style>
