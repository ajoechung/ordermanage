<template>
  <div class="supplier-container">
    <el-card class="search-card">
      <el-form :inline="true" :model="searchForm" class="search-form">
        <el-form-item label="供应商名称">
          <el-input v-model="searchForm.name" placeholder="请输入供应商名称" clearable @keyup.enter="handleSearch" />
        </el-form-item>
        <el-form-item label="供应商类型">
          <el-select v-model="searchForm.supplier_type" placeholder="请选择" clearable>
            <el-option label="原材料供应商" value="raw_material" />
            <el-option label="包装材料供应商" value="packaging" />
            <el-option label="设备供应商" value="equipment" />
            <el-option label="服务供应商" value="service" />
          </el-select>
        </el-form-item>
        <el-form-item label="合作状态">
          <el-select v-model="searchForm.cooperation_status" placeholder="请选择" clearable>
            <el-option label="已合作" value="active" />
            <el-option label="待合作" value="pending" />
            <el-option label="已终止" value="terminated" />
          </el-select>
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
          <span class="table-title">供应商列表</span>
          <div class="table-actions">
            <el-button type="primary" @click="handleAdd">
              <el-icon><Plus /></el-icon>
              新增供应商
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
        :data="supplierList"
        stripe
        border
        @selection-change="handleSelectionChange"
      >
        <el-table-column type="selection" width="50" />
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="name" label="供应商名称" min-width="200" show-overflow-tooltip />
        <el-table-column prop="supplier_type" label="供应商类型" width="120">
          <template #default="{ row }">
            {{ getSupplierTypeText(row.supplier_type) }}
          </template>
        </el-table-column>
        <el-table-column prop="contact" label="联系人" width="120" />
        <el-table-column prop="phone" label="联系电话" width="130" />
        <el-table-column prop="cooperation_status" label="合作状态" width="100">
          <template #default="{ row }">
            <el-tag :type="getCooperationStatusType(row.cooperation_status)">
              {{ getCooperationStatusText(row.cooperation_status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="address" label="地址" min-width="200" show-overflow-tooltip />
        <el-table-column label="操作" width="220" fixed="right">
          <template #default="{ row }">
            <el-button type="primary" link size="small" @click="handleView(row)">
              查看
            </el-button>
            <el-button type="primary" link size="small" @click="handleEdit(row)">
              编辑
            </el-button>
            <el-button type="primary" link size="small" @click="handlePurchase(row)">
              采购
            </el-button>
            <el-button type="danger" link size="small" @click="handleDelete(row)">
              删除
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
      width="700px"
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
            <el-form-item label="供应商名称" prop="name">
              <el-input v-model="formData.name" placeholder="请输入供应商名称" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="供应商类型" prop="supplier_type">
              <el-select v-model="formData.supplier_type" placeholder="请选择" style="width: 100%">
                <el-option label="原材料供应商" value="raw_material" />
                <el-option label="包装材料供应商" value="packaging" />
                <el-option label="设备供应商" value="equipment" />
                <el-option label="服务供应商" value="service" />
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
              <el-input v-model="formData.phone" placeholder="请输入联系电话" />
            </el-form-item>
          </el-col>
        </el-row>

        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="电子邮箱" prop="email">
              <el-input v-model="formData.email" placeholder="请输入邮箱" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="合作状态" prop="cooperation_status">
              <el-select v-model="formData.cooperation_status" placeholder="请选择" style="width: 100%">
                <el-option label="已合作" value="active" />
                <el-option label="待合作" value="pending" />
                <el-option label="已终止" value="terminated" />
              </el-select>
            </el-form-item>
          </el-col>
        </el-row>

        <el-form-item label="地区" prop="region">
          <el-cascader
            v-model="formData.region"
            :options="regionOptions"
            placeholder="请选择地区"
            style="width: 100%"
          />
        </el-form-item>

        <el-form-item label="详细地址" prop="address">
          <el-input v-model="formData.address" placeholder="请输入详细地址" />
        </el-form-item>

        <el-form-item label="主营产品" prop="main_products">
          <el-input v-model="formData.main_products" type="textarea" :rows="2" placeholder="请输入主营产品" />
        </el-form-item>

        <el-form-item label="备注" prop="remark">
          <el-input v-model="formData.remark" type="textarea" :rows="3" placeholder="请输入备注" />
        </el-form-item>
      </el-form>

      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" :loading="submitLoading" @click="handleSubmit">
          确定
        </el-button>
      </template>
    </el-dialog>

    <el-dialog v-model="detailVisible" title="供应商详情" width="700px">
      <el-descriptions :column="2" border>
        <el-descriptions-item label="供应商名称">{{ currentSupplier.name }}</el-descriptions-item>
        <el-descriptions-item label="供应商类型">
          {{ getSupplierTypeText(currentSupplier.supplier_type) }}
        </el-descriptions-item>
        <el-descriptions-item label="联系人">{{ currentSupplier.contact }}</el-descriptions-item>
        <el-descriptions-item label="联系电话">{{ currentSupplier.phone }}</el-descriptions-item>
        <el-descriptions-item label="电子邮箱">{{ currentSupplier.email }}</el-descriptions-item>
        <el-descriptions-item label="合作状态">
          <el-tag :type="getCooperationStatusType(currentSupplier.cooperation_status)">
            {{ getCooperationStatusText(currentSupplier.cooperation_status) }}
          </el-tag>
        </el-descriptions-item>
        <el-descriptions-item label="地区">{{ currentSupplier.region }}</el-descriptions-item>
        <el-descriptions-item label="详细地址" :span="2">{{ currentSupplier.address }}</el-descriptions-item>
        <el-descriptions-item label="主营产品" :span="2">{{ currentSupplier.main_products }}</el-descriptions-item>
        <el-descriptions-item label="备注" :span="2">{{ currentSupplier.remark }}</el-descriptions-item>
      </el-descriptions>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Search, Refresh, Plus, Download } from '@element-plus/icons-vue'
import { getList, create, update, deleteSupplier } from '@/api/modules/supplier'

const router = useRouter()

const searchForm = reactive({
  name: '',
  supplier_type: '',
  cooperation_status: ''
})

const tableLoading = ref(false)
const supplierList = ref([])
const selectedRows = ref([])

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
const currentSupplier = ref({})

const formData = reactive({
  id: null,
  name: '',
  supplier_type: '',
  contact: '',
  phone: '',
  email: '',
  cooperation_status: 'pending',
  region: [],
  address: '',
  main_products: '',
  remark: ''
})

const formRules = {
  name: [{ required: true, message: '请输入供应商名称', trigger: 'blur' }],
  supplier_type: [{ required: true, message: '请选择供应商类型', trigger: 'change' }],
  contact: [{ required: true, message: '请输入联系人', trigger: 'blur' }],
  phone: [{ required: true, message: '请输入联系电话', trigger: 'blur' }]
}

const regionOptions = [
  { value: '华北', label: '华北', children: [{ value: '北京', label: '北京' }, { value: '天津', label: '天津' }] },
  { value: '华东', label: '华东', children: [{ value: '上海', label: '上海' }, { value: '江苏', label: '江苏' }] },
  { value: '华南', label: '华南', children: [{ value: '广东', label: '广东' }, { value: '深圳', label: '深圳' }] },
  { value: '西南', label: '西南', children: [{ value: '四川', label: '四川' }, { value: '重庆', label: '重庆' }] }
]

const supplierTypeMap = {
  raw_material: '原材料供应商',
  packaging: '包装材料供应商',
  equipment: '设备供应商',
  service: '服务供应商'
}

const cooperationStatusMap = {
  active: { text: '已合作', type: 'success' },
  pending: { text: '待合作', type: 'warning' },
  terminated: { text: '已终止', type: 'danger' }
}

const getSupplierTypeText = (type) => supplierTypeMap[type] || type
const getCooperationStatusText = (status) => cooperationStatusMap[status]?.text || status
const getCooperationStatusType = (status) => cooperationStatusMap[status]?.type || 'info'

const loadData = async () => {
  tableLoading.value = true
  try {
    const params = {
      page: pagination.page,
      page_size: pagination.pageSize,
      ...searchForm
    }
    
    const res = await getList(params)
    if (res.code === 200) {
      supplierList.value = res.data.list || []
      pagination.total = res.data.total || 0
    }
  } catch (error) {
    console.error('获取供应商列表失败:', error)
    ElMessage.error('获取供应商列表失败')
  } finally {
    tableLoading.value = false
  }
}

const handleSearch = () => {
  pagination.page = 1
  loadData()
}

const handleReset = () => {
  Object.keys(searchForm).forEach(key => {
    searchForm[key] = ''
  })
  pagination.page = 1
  loadData()
}

const handleAdd = () => {
  dialogTitle.value = '新增供应商'
  dialogVisible.value = true
}

const handleEdit = (row) => {
  dialogTitle.value = '编辑供应商'
  Object.assign(formData, {
    id: row.id,
    name: row.name,
    supplier_type: row.supplier_type,
    contact: row.contact,
    phone: row.phone,
    email: row.email,
    cooperation_status: row.cooperation_status,
    region: row.region ? row.region.split(',') : [],
    address: row.address,
    main_products: row.main_products,
    remark: row.remark
  })
  dialogVisible.value = true
}

const handleView = (row) => {
  currentSupplier.value = row
  detailVisible.value = true
}

const handlePurchase = (row) => {
  router.push({ path: '/purchase/list', query: { supplier_id: row.id } })
}

const handleDelete = async (row) => {
  try {
    await ElMessageBox.confirm(`确定要删除供应商"${row.name}"吗？`, '提示', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    })
    
    const res = await deleteSupplier(row.id)
    if (res.code === 200) {
      ElMessage.success('删除成功')
      loadData()
    }
  } catch (error) {
    if (error !== 'cancel') {
      console.error('删除供应商失败:', error)
      ElMessage.error('删除供应商失败')
    }
  }
}

const handleSubmit = async () => {
  if (!formRef.value) return
  
  await formRef.value.validate(async (valid) => {
    if (!valid) return
    
    submitLoading.value = true
    try {
      const submitData = { ...formData }
      if (submitData.region) {
        submitData.region = submitData.region.join(',')
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
    name: '',
    supplier_type: '',
    contact: '',
    phone: '',
    email: '',
    cooperation_status: 'pending',
    region: [],
    address: '',
    main_products: '',
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
})
</script>

<style scoped>
.supplier-container {
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

:deep(.el-table) {
  font-size: 14px;
}

:deep(.el-form-item) {
  margin-bottom: 18px;
}
</style>
