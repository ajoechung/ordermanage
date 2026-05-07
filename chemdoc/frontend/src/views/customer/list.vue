<template>
  <div class="customer-container">
    <el-card class="search-card">
      <el-form :inline="true" :model="searchForm" class="search-form">
        <el-form-item label="客户名称">
          <el-input v-model="searchForm.name" placeholder="请输入客户名称" clearable @keyup.enter="handleSearch" />
        </el-form-item>
        <el-form-item label="客户类型">
          <el-select v-model="searchForm.customer_type" placeholder="请选择" clearable>
            <el-option label="潜在客户" value="potential" />
            <el-option label="正式客户" value="formal" />
            <el-option label="VIP客户" value="vip" />
            <el-option label="失效客户" value="inactive" />
          </el-select>
        </el-form-item>
        <el-form-item label="客户来源">
          <el-select v-model="searchForm.source" placeholder="请选择" clearable>
            <el-option label="网络推广" value="network" />
            <el-option label="电话营销" value="phone" />
            <el-option label="客户介绍" value="referral" />
            <el-option label="展会活动" value="exhibition" />
            <el-option label="其他" value="other" />
          </el-select>
        </el-form-item>
        <el-form-item label="客户等级">
          <el-select v-model="searchForm.level" placeholder="请选择" clearable>
            <el-option label="A类客户" value="A" />
            <el-option label="B类客户" value="B" />
            <el-option label="C类客户" value="C" />
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
            <el-button type="success" @click="handleExport">
              <el-icon><Download /></el-icon>
              导出数据
            </el-button>
          </div>
        </div>
      </template>

      <el-table
        v-loading="tableLoading"
        :data="customerList"
        stripe
        border
        @selection-change="handleSelectionChange"
      >
        <el-table-column type="selection" width="50" />
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="name" label="客户名称" min-width="200" show-overflow-tooltip />
        <el-table-column prop="customer_type" label="客户类型" width="100">
          <template #default="{ row }">
            <el-tag :type="getCustomerTypeTag(row.customer_type)">
              {{ getCustomerTypeText(row.customer_type) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="industry" label="行业" width="100" />
        <el-table-column prop="level" label="等级" width="80">
          <template #default="{ row }">
            <el-tag :type="getLevelTag(row.level)" size="small">
              {{ row.level }}类
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="contact" label="联系人" width="120" />
        <el-table-column prop="phone" label="联系电话" width="130" />
        <el-table-column prop="source" label="客户来源" width="100">
          <template #default="{ row }">
            {{ getSourceText(row.source) }}
          </template>
        </el-table-column>
        <el-table-column prop="owner_user_name" label="负责人" width="100" />
        <el-table-column label="操作" width="280" fixed="right">
          <template #default="{ row }">
            <el-button type="primary" link size="small" @click="handleView(row)">
              查看
            </el-button>
            <el-button type="primary" link size="small" @click="handleEdit(row)">
              编辑
            </el-button>
            <el-button type="primary" link size="small" @click="handleFollow(row)">
              跟进
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
            <el-form-item label="客户名称" prop="name">
              <el-input v-model="formData.name" placeholder="请输入客户名称" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="客户类型" prop="customer_type">
              <el-select v-model="formData.customer_type" placeholder="请选择" style="width: 100%">
                <el-option label="潜在客户" value="potential" />
                <el-option label="正式客户" value="formal" />
                <el-option label="VIP客户" value="vip" />
                <el-option label="失效客户" value="inactive" />
              </el-select>
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
            <el-form-item label="客户等级" prop="level">
              <el-select v-model="formData.level" placeholder="请选择" style="width: 100%">
                <el-option label="A类客户" value="A" />
                <el-option label="B类客户" value="B" />
                <el-option label="C类客户" value="C" />
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
            <el-form-item label="客户来源" prop="source">
              <el-select v-model="formData.source" placeholder="请选择" style="width: 100%">
                <el-option label="网络推广" value="network" />
                <el-option label="电话营销" value="phone" />
                <el-option label="客户介绍" value="referral" />
                <el-option label="展会活动" value="exhibition" />
                <el-option label="其他" value="other" />
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
          <el-input v-model="formData.address" type="textarea" :rows="2" placeholder="请输入详细地址" />
        </el-form-item>

        <el-form-item label="备注" prop="remark">
          <el-input v-model="formData.remark" type="textarea" :rows="3" placeholder="请输入备注信息" />
        </el-form-item>
      </el-form>

      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" :loading="submitLoading" @click="handleSubmit">
          确定
        </el-button>
      </template>
    </el-dialog>

    <el-dialog v-model="detailVisible" title="客户详情" width="800px">
      <el-descriptions :column="2" border>
        <el-descriptions-item label="客户名称">{{ currentCustomer.name }}</el-descriptions-item>
        <el-descriptions-item label="客户类型">
          {{ getCustomerTypeText(currentCustomer.customer_type) }}
        </el-descriptions-item>
        <el-descriptions-item label="行业">{{ currentCustomer.industry }}</el-descriptions-item>
        <el-descriptions-item label="客户等级">{{ currentCustomer.level }}类</el-descriptions-item>
        <el-descriptions-item label="联系人">{{ currentCustomer.contact }}</el-descriptions-item>
        <el-descriptions-item label="联系电话">{{ currentCustomer.phone }}</el-descriptions-item>
        <el-descriptions-item label="电子邮箱">{{ currentCustomer.email }}</el-descriptions-item>
        <el-descriptions-item label="客户来源">{{ getSourceText(currentCustomer.source) }}</el-descriptions-item>
        <el-descriptions-item label="地区" :span="2">{{ currentCustomer.region }}</el-descriptions-item>
        <el-descriptions-item label="详细地址" :span="2">{{ currentCustomer.address }}</el-descriptions-item>
        <el-descriptions-item label="备注" :span="2">{{ currentCustomer.remark }}</el-descriptions-item>
        <el-descriptions-item label="负责人">{{ currentCustomer.owner_user_name }}</el-descriptions-item>
        <el-descriptions-item label="创建时间">{{ currentCustomer.create_time }}</el-descriptions-item>
      </el-descriptions>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Search, Refresh, Plus, Download } from '@element-plus/icons-vue'
import { getList, create, update, deleteCustomer, getAll } from '@/api/modules/customer'

const router = useRouter()

const searchForm = reactive({
  name: '',
  customer_type: '',
  source: '',
  level: ''
})

const tableLoading = ref(false)
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
const currentCustomer = ref({})

const formData = reactive({
  id: null,
  name: '',
  customer_type: '',
  industry: '',
  level: '',
  contact: '',
  phone: '',
  email: '',
  source: '',
  region: [],
  address: '',
  remark: ''
})

const formRules = {
  name: [{ required: true, message: '请输入客户名称', trigger: 'blur' }],
  customer_type: [{ required: true, message: '请选择客户类型', trigger: 'change' }],
  level: [{ required: true, message: '请选择客户等级', trigger: 'change' }]
}

const regionOptions = [
  { value: '华北', label: '华北', children: [{ value: '北京', label: '北京' }, { value: '天津', label: '天津' }] },
  { value: '华东', label: '华东', children: [{ value: '上海', label: '上海' }, { value: '江苏', label: '江苏' }] },
  { value: '华南', label: '华南', children: [{ value: '广东', label: '广东' }, { value: '深圳', label: '深圳' }] },
  { value: '西南', label: '西南', children: [{ value: '四川', label: '四川' }, { value: '重庆', label: '重庆' }] }
]

const customerTypeMap = {
  potential: { text: '潜在客户', tag: 'info' },
  formal: { text: '正式客户', tag: 'success' },
  vip: { text: 'VIP客户', tag: 'warning' },
  inactive: { text: '失效客户', tag: 'danger' }
}

const sourceMap = {
  network: '网络推广',
  phone: '电话营销',
  referral: '客户介绍',
  exhibition: '展会活动',
  other: '其他'
}

const getCustomerTypeText = (type) => customerTypeMap[type]?.text || type
const getCustomerTypeTag = (type) => customerTypeMap[type]?.tag || 'info'
const getLevelTag = (level) => ({ A: 'danger', B: 'warning', C: 'info' }[level] || 'info')
const getSourceText = (source) => sourceMap[source] || source

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
      customerList.value = res.data.list || []
      pagination.total = res.data.total || 0
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
  Object.keys(searchForm).forEach(key => {
    searchForm[key] = ''
  })
  pagination.page = 1
  loadData()
}

const handleAdd = () => {
  dialogTitle.value = '新增客户'
  dialogVisible.value = true
}

const handleEdit = (row) => {
  dialogTitle.value = '编辑客户'
  Object.assign(formData, row)
  dialogVisible.value = true
}

const handleView = (row) => {
  currentCustomer.value = row
  detailVisible.value = true
}

const handleFollow = (row) => {
  router.push({ path: '/customer/follow', query: { customer_id: row.id } })
}

const handleDelete = async (row) => {
  try {
    await ElMessageBox.confirm(`确定要删除客户"${row.name}"吗？`, '提示', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    })
    
    const res = await deleteCustomer(row.id)
    if (res.code === 200) {
      ElMessage.success('删除成功')
      loadData()
    }
  } catch (error) {
    if (error !== 'cancel') {
      console.error('删除客户失败:', error)
      ElMessage.error('删除客户失败')
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
    customer_type: '',
    industry: '',
    level: '',
    contact: '',
    phone: '',
    email: '',
    source: '',
    region: [],
    address: '',
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
.customer-container {
  padding: 0;
}

.search-card {
  margin-bottom: 20px;
}

.search-form {
  margin-bottom: 0;
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
