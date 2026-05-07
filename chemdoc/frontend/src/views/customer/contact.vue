<template>
  <div class="contact-container">
    <el-card class="search-card">
      <el-form :inline="true" :model="searchForm" class="search-form">
        <el-form-item label="联系人">
          <el-input v-model="searchForm.name" placeholder="请输入联系人姓名" clearable @keyup.enter="handleSearch" />
        </el-form-item>
        <el-form-item label="所属客户">
          <el-select v-model="searchForm.customer_id" placeholder="请选择" clearable filterable>
            <el-option
              v-for="customer in customerList"
              :key="customer.id"
              :label="customer.name"
              :value="customer.id"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="职位">
          <el-input v-model="searchForm.position" placeholder="请输入职位" clearable />
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
          <span class="table-title">联系人列表</span>
          <div class="table-actions">
            <el-button type="primary" @click="handleAdd">
              <el-icon><Plus /></el-icon>
              新增联系人
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
        :data="contactList"
        stripe
        border
        @selection-change="handleSelectionChange"
      >
        <el-table-column type="selection" width="50" />
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="name" label="姓名" width="120" />
        <el-table-column prop="customer_name" label="所属客户" min-width="180" show-overflow-tooltip />
        <el-table-column prop="position" label="职位" width="120" />
        <el-table-column prop="phone" label="手机" width="130" />
        <el-table-column prop="email" label="邮箱" width="180" />
        <el-table-column prop="wechat" label="微信" width="130" />
        <el-table-column prop="is_primary" label="主联系人" width="100" align="center">
          <template #default="{ row }">
            <el-tag v-if="row.is_primary" type="success" size="small">是</el-tag>
            <span v-else>-</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="200" fixed="right">
          <template #default="{ row }">
            <el-button type="primary" link size="small" @click="handleView(row)">
              查看
            </el-button>
            <el-button type="primary" link size="small" @click="handleEdit(row)">
              编辑
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
      width="600px"
      :close-on-click-modal="false"
      @close="handleDialogClose"
    >
      <el-form
        ref="formRef"
        :model="formData"
        :rules="formRules"
        label-width="100px"
      >
        <el-form-item label="所属客户" prop="customer_id">
          <el-select v-model="formData.customer_id" placeholder="请选择" style="width: 100%" filterable>
            <el-option
              v-for="customer in customerList"
              :key="customer.id"
              :label="customer.name"
              :value="customer.id"
            />
          </el-select>
        </el-form-item>

        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="姓名" prop="name">
              <el-input v-model="formData.name" placeholder="请输入联系人姓名" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="职位" prop="position">
              <el-input v-model="formData.position" placeholder="请输入职位" />
            </el-form-item>
          </el-col>
        </el-row>

        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="手机" prop="phone">
              <el-input v-model="formData.phone" placeholder="请输入手机号" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="座机" prop="tel">
              <el-input v-model="formData.tel" placeholder="请输入座机号码" />
            </el-form-item>
          </el-col>
        </el-row>

        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="邮箱" prop="email">
              <el-input v-model="formData.email" placeholder="请输入邮箱" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="微信" prop="wechat">
              <el-input v-model="formData.wechat" placeholder="请输入微信号" />
            </el-form-item>
          </el-col>
        </el-row>

        <el-form-item label="性别" prop="gender">
          <el-radio-group v-model="formData.gender">
            <el-radio label="male">男</el-radio>
            <el-radio label="female">女</el-radio>
          </el-radio-group>
        </el-form-item>

        <el-form-item label="是否主联系人">
          <el-switch v-model="formData.is_primary" :active-value="1" :inactive-value="0" />
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

    <el-dialog v-model="detailVisible" title="联系人详情" width="600px">
      <el-descriptions :column="2" border>
        <el-descriptions-item label="姓名">{{ currentContact.name }}</el-descriptions-item>
        <el-descriptions-item label="性别">{{ currentContact.gender === 'male' ? '男' : '女' }}</el-descriptions-item>
        <el-descriptions-item label="所属客户">{{ currentContact.customer_name }}</el-descriptions-item>
        <el-descriptions-item label="职位">{{ currentContact.position }}</el-descriptions-item>
        <el-descriptions-item label="手机">{{ currentContact.phone }}</el-descriptions-item>
        <el-descriptions-item label="座机">{{ currentContact.tel }}</el-descriptions-item>
        <el-descriptions-item label="邮箱">{{ currentContact.email }}</el-descriptions-item>
        <el-descriptions-item label="微信">{{ currentContact.wechat }}</el-descriptions-item>
        <el-descriptions-item label="主联系人">{{ currentContact.is_primary ? '是' : '否' }}</el-descriptions-item>
        <el-descriptions-item label="备注" :span="2">{{ currentContact.remark }}</el-descriptions-item>
      </el-descriptions>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Search, Refresh, Plus, Download } from '@element-plus/icons-vue'
import { getList, create, update, deleteContact } from '@/api/modules/contact'
import { getAll as getCustomerList } from '@/api/modules/customer'

const searchForm = reactive({
  name: '',
  customer_id: '',
  position: ''
})

const tableLoading = ref(false)
const contactList = ref([])
const customerList = ref([])
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
const currentContact = ref({})

const formData = reactive({
  id: null,
  customer_id: null,
  customer_name: '',
  name: '',
  position: '',
  phone: '',
  tel: '',
  email: '',
  wechat: '',
  gender: 'male',
  is_primary: 0,
  remark: ''
})

const formRules = {
  customer_id: [{ required: true, message: '请选择客户', trigger: 'change' }],
  name: [{ required: true, message: '请输入联系人姓名', trigger: 'blur' }],
  phone: [
    { required: true, message: '请输入手机号', trigger: 'blur' },
    { pattern: /^1[3-9]\d{9}$/, message: '请输入正确的手机号', trigger: 'blur' }
  ]
}

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
      contactList.value = res.data.list || []
      pagination.total = res.data.total || 0
    }
  } catch (error) {
    console.error('获取联系人列表失败:', error)
    ElMessage.error('获取联系人列表失败')
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
  dialogTitle.value = '新增联系人'
  dialogVisible.value = true
}

const handleEdit = (row) => {
  dialogTitle.value = '编辑联系人'
  Object.assign(formData, {
    id: row.id,
    customer_id: row.customer_id,
    customer_name: row.customer_name,
    name: row.name,
    position: row.position,
    phone: row.phone,
    tel: row.tel,
    email: row.email,
    wechat: row.wechat,
    gender: row.gender || 'male',
    is_primary: row.is_primary || 0,
    remark: row.remark
  })
  dialogVisible.value = true
}

const handleView = (row) => {
  currentContact.value = row
  detailVisible.value = true
}

const handleDelete = async (row) => {
  try {
    await ElMessageBox.confirm(`确定要删除联系人"${row.name}"吗？`, '提示', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    })
    
    const res = await deleteContact(row.id)
    if (res.code === 200) {
      ElMessage.success('删除成功')
      loadData()
    }
  } catch (error) {
    if (error !== 'cancel') {
      console.error('删除联系人失败:', error)
      ElMessage.error('删除联系人失败')
    }
  }
}

const handleSubmit = async () => {
  if (!formRef.value) return
  
  await formRef.value.validate(async (valid) => {
    if (!valid) return
    
    submitLoading.value = true
    try {
      const customer = customerList.value.find(c => c.id === formData.customer_id)
      const submitData = {
        ...formData,
        customer_name: customer?.name || ''
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
    name: '',
    position: '',
    phone: '',
    tel: '',
    email: '',
    wechat: '',
    gender: 'male',
    is_primary: 0,
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
})
</script>

<style scoped>
.contact-container {
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
