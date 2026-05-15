<template>
  <div class="customer-container">
    <el-card class="search-card">
      <el-form :inline="true" :model="searchForm" class="search-form">
        <el-form-item label="客户名称">
          <el-input v-model="searchForm.keyword" placeholder="请输入客户名称" clearable @keyup.enter="handleSearch" />
        </el-form-item>
        <el-form-item label="行业">
          <el-input v-model="searchForm.industry" placeholder="请输入行业" clearable />
        </el-form-item>
        <el-form-item label="客户等级">
          <el-select v-model="searchForm.level" placeholder="请选择" clearable style="width: 150px">
            <el-option label="普通客户" :value="1" />
            <el-option label="重要客户" :value="2" />
            <el-option label="核心客户" :value="3" />
          </el-select>
        </el-form-item>
        <el-form-item label="状态">
          <el-select v-model="searchForm.status" placeholder="请选择" clearable style="width: 120px">
            <el-option label="正常" :value="1" />
            <el-option label="禁用" :value="0" />
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
          <span class="table-title">客户列表</span>
          <div class="table-actions">
            <el-button type="primary" @click="handleAdd">
              <el-icon><Plus /></el-icon>
              新增客户
            </el-button>
          </div>
        </div>
      </template>

      <el-table v-loading="tableLoading" :data="customerList" stripe border>
        <el-table-column prop="customer_id" label="ID" width="80" />
        <el-table-column prop="name" label="客户名称" min-width="180" show-overflow-tooltip />
        <el-table-column prop="code" label="客户编码" width="120" />
        <el-table-column prop="industry" label="行业" width="100" />
        <el-table-column prop="level" label="等级" width="100">
          <template #default="{ row }">
            <el-tag :type="getLevelTag(row.level)" size="small">
              {{ getLevelText(row.level) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="source" label="客户来源" width="100" />
        <el-table-column prop="address" label="地址" min-width="150" show-overflow-tooltip />
        <el-table-column prop="owner_name" label="负责人" width="100" />
        <el-table-column prop="create_time" label="创建时间" width="160" />
        <el-table-column label="操作" width="200" fixed="right">
          <template #default="{ row }">
            <el-button type="primary" link size="small" @click="handleView(row)">查看</el-button>
            <el-button type="primary" link size="small" @click="handleEdit(row)">编辑</el-button>
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

    <el-dialog v-model="dialogVisible" :title="dialogTitle" width="700px" :close-on-click-modal="false" @close="handleDialogClose">
      <el-form ref="formRef" :model="formData" :rules="formRules" label-width="100px">
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="客户名称" prop="name">
              <el-input v-model="formData.name" placeholder="请输入客户名称" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="客户编码" prop="code">
              <el-input v-model="formData.code" placeholder="请输入客户编码" />
            </el-form-item>
          </el-col>
        </el-row>

        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="行业" prop="industry">
              <el-input v-model="formData.industry" placeholder="请输入行业" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="客户来源" prop="source">
              <el-input v-model="formData.source" placeholder="请输入客户来源" />
            </el-form-item>
          </el-col>
        </el-row>

        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="客户等级" prop="level">
              <el-select v-model="formData.level" placeholder="请选择" style="width: 100%">
                <el-option label="普通客户" :value="1" />
                <el-option label="重要客户" :value="2" />
                <el-option label="核心客户" :value="3" />
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="客户规模" prop="scale">
              <el-input v-model="formData.scale" placeholder="请输入客户规模" />
            </el-form-item>
          </el-col>
        </el-row>

        <el-form-item label="详细地址" prop="address">
          <el-input v-model="formData.address" type="textarea" :rows="2" placeholder="请输入详细地址" />
        </el-form-item>

        <el-form-item label="客户描述" prop="description">
          <el-input v-model="formData.description" type="textarea" :rows="3" placeholder="请输入客户描述" />
        </el-form-item>

        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="状态" prop="status">
              <el-radio-group v-model="formData.status">
                <el-radio :label="1">正常</el-radio>
                <el-radio :label="0">禁用</el-radio>
              </el-radio-group>
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>

      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" :loading="submitLoading" @click="handleSubmit">确定</el-button>
      </template>
    </el-dialog>

    <customer-detail
      v-model="detailVisible"
      :customer-id="currentCustomerId"
      ref="detailRef"
    />
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, nextTick } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Search, Refresh, Plus } from '@element-plus/icons-vue'
import { getList, create, update, deleteCustomer } from '@/api/modules/customer'
import CustomerDetail from './detail.vue'

const searchForm = reactive({
  keyword: '',
  industry: '',
  level: '',
  status: ''
})

const tableLoading = ref(false)
const customerList = ref([])

const pagination = reactive({
  page: 1,
  pageSize: 20,
  total: 0
})

const dialogVisible = ref(false)
const detailVisible = ref(false)
const dialogTitle = ref('')
const submitLoading = ref(false)
const formRef = ref(null)
const detailRef = ref(null)
const currentCustomerId = ref(null)

const formData = reactive({
  customer_id: null,
  name: '',
  code: '',
  industry: '',
  source: '',
  level: 1,
  scale: '',
  address: '',
  description: '',
  status: 1
})

const formRules = {
  name: [{ required: true, message: '请输入客户名称', trigger: 'blur' }]
}

const getLevelText = (level) => ({ 1: '普通', 2: '重要', 3: '核心' }[level] || '未知')
const getLevelTag = (level) => ({ 1: '', 2: 'warning', 3: 'danger' }[level] || 'info')

const loadData = async () => {
  tableLoading.value = true
  try {
    const params = {
      page: pagination.page,
      page_size: pagination.pageSize,
      ...searchForm
    }
    Object.keys(params).forEach(key => {
      if (params[key] === '' || params[key] === null) delete params[key]
    })

    const res = await getList(params)
    if (res.code === 200) {
      customerList.value = res.data.list || []
      pagination.total = res.data.total || 0
    } else {
      ElMessage.error(res.msg || '获取数据失败')
    }
  } catch (error) {
    console.error('获取客户列表失败:', error)
    ElMessage.error('获取客户列表失败')
  } finally {
    tableLoading.value = false
  }
}

const handleSearch = () => {
  pagination.page = 1
  loadData()
}

const handleReset = () => {
  Object.assign(searchForm, { keyword: '', industry: '', level: '', status: '' })
  pagination.page = 1
  loadData()
}

const handleAdd = () => {
  dialogTitle.value = '新增客户'
  dialogVisible.value = true
}

const handleEdit = (row) => {
  dialogTitle.value = '编辑客户'
  Object.keys(formData).forEach(key => {
    formData[key] = row[key] ?? formData[key]
  })
  dialogVisible.value = true
}

const handleView = (row) => {
  alert('handleView called! customer_id: ' + row.customer_id)
  console.log('handleView called, row:', row)
  currentCustomerId.value = row.customer_id
  console.log('currentCustomerId set:', currentCustomerId.value)
  detailVisible.value = true
  setTimeout(() => {
    console.log('setTimeout: detailRef:', detailRef.value)
    if (detailRef.value) {
      console.log('calling loadCustomerDetail with:', row.customer_id)
      detailRef.value.loadCustomerDetail(row.customer_id)
    } else {
      console.error('detailRef is undefined!')
    }
  }, 100)
}

const handleDelete = async (row) => {
  try {
    await ElMessageBox.confirm(`确定要删除客户"${row.name}"吗？`, '提示', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    })

    const res = await deleteCustomer(row.customer_id)
    if (res.code === 200) {
      ElMessage.success('删除成功')
      loadData()
    } else {
      ElMessage.error(res.msg || '删除失败')
    }
  } catch (error) {
    if (error !== 'cancel') {
      console.error('删除客户失败:', error)
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
      if (submitData.customer_id) {
        await update(submitData.customer_id, submitData)
      } else {
        delete submitData.customer_id
        await create(submitData)
      }
      ElMessage.success(submitData.customer_id ? '编辑成功' : '新增成功')
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
    customer_id: null,
    name: '',
    code: '',
    industry: '',
    source: '',
    level: 1,
    scale: '',
    address: '',
    description: '',
    status: 1
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
  loadData()
})
</script>

<style scoped>
.customer-container {
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
</style>
