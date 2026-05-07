<template>
  <div class="contact-container">
    <el-card class="search-card">
      <el-form :inline="true" :model="searchForm" class="search-form">
        <el-form-item label="联系人">
          <el-input v-model="searchForm.keyword" placeholder="请输入联系人姓名" clearable @keyup.enter="handleSearch" />
        </el-form-item>
        <el-form-item label="所属客户">
          <el-select v-model="searchForm.customer_id" placeholder="请选择" clearable filterable>
            <el-option v-for="customer in customerList" :key="customer.customer_id" :label="customer.name" :value="customer.customer_id" />
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
          <span class="table-title">联系人列表</span>
          <el-button type="primary" @click="handleAdd"><Plus />新增联系人</el-button>
        </div>
      </template>

      <el-table v-loading="tableLoading" :data="contactList" stripe border>
        <el-table-column prop="contact_id" label="ID" width="80" />
        <el-table-column prop="name" label="姓名" width="120" />
        <el-table-column prop="customer_name" label="所属客户" min-width="150" show-overflow-tooltip />
        <el-table-column prop="position" label="职位" width="120" />
        <el-table-column prop="mobile" label="手机" width="130" />
        <el-table-column prop="email" label="邮箱" width="180" />
        <el-table-column prop="is_default" label="主联系人" width="100" align="center">
          <template #default="{ row }">
            <el-tag v-if="row.is_default" type="success" size="small">是</el-tag>
            <span v-else>-</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="160" fixed="right">
          <template #default="{ row }">
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

    <el-dialog v-model="dialogVisible" :title="dialogTitle" width="600px" :close-on-click-modal="false" @close="handleDialogClose">
      <el-form ref="formRef" :model="formData" :rules="formRules" label-width="100px">
        <el-form-item label="所属客户" prop="customer_id">
          <el-select v-model="formData.customer_id" placeholder="请选择" style="width: 100%" filterable>
            <el-option v-for="customer in customerList" :key="customer.customer_id" :label="customer.name" :value="customer.customer_id" />
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
            <el-form-item label="手机" prop="mobile">
              <el-input v-model="formData.mobile" placeholder="请输入手机号" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="座机" prop="phone">
              <el-input v-model="formData.phone" placeholder="请输入座机号码" />
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
            <el-radio :label="1">男</el-radio>
            <el-radio :label="0">女</el-radio>
          </el-radio-group>
        </el-form-item>

        <el-form-item label="备注" prop="remark">
          <el-input v-model="formData.remark" type="textarea" :rows="3" placeholder="请输入备注" />
        </el-form-item>
      </el-form>

      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" :loading="submitLoading" @click="handleSubmit">确定</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Search, Refresh, Plus } from '@element-plus/icons-vue'
import { getList, create, update, deleteContact } from '@/api/modules/contact'
import { getAll } from '@/api/modules/customer'

const searchForm = reactive({
  keyword: '',
  customer_id: ''
})

const tableLoading = ref(false)
const contactList = ref([])
const customerList = ref([])

const pagination = reactive({
  page: 1,
  pageSize: 20,
  total: 0
})

const dialogVisible = ref(false)
const dialogTitle = ref('')
const submitLoading = ref(false)
const formRef = ref(null)

const formData = reactive({
  contact_id: null,
  customer_id: null,
  name: '',
  position: '',
  mobile: '',
  phone: '',
  email: '',
  wechat: '',
  gender: 1,
  remark: ''
})

const formRules = {
  customer_id: [{ required: true, message: '请选择客户', trigger: 'change' }],
  name: [{ required: true, message: '请输入联系人姓名', trigger: 'blur' }],
  mobile: [{ required: true, message: '请输入手机号', trigger: 'blur' }]
}

const loadData = async () => {
  tableLoading.value = true
  try {
    const params = {
      page: pagination.page,
      page_size: pagination.pageSize
    }
    if (searchForm.keyword) params.keyword = searchForm.keyword
    if (searchForm.customer_id) params.customer_id = searchForm.customer_id

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
    const res = await getAll()
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
  Object.assign(searchForm, { keyword: '', customer_id: '' })
  pagination.page = 1
  loadData()
}

const handleAdd = () => {
  dialogTitle.value = '新增联系人'
  dialogVisible.value = true
}

const handleEdit = (row) => {
  dialogTitle.value = '编辑联系人'
  Object.keys(formData).forEach(key => {
    formData[key] = row[key] ?? formData[key]
  })
  dialogVisible.value = true
}

const handleDelete = async (row) => {
  try {
    await ElMessageBox.confirm(`确定要删除联系人"${row.name}"吗？`, '提示', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    })

    const res = await deleteContact(row.contact_id)
    if (res.code === 200) {
      ElMessage.success('删除成功')
      loadData()
    } else {
      ElMessage.error(res.msg || '删除失败')
    }
  } catch (error) {
    if (error !== 'cancel') {
      console.error('删除联系人失败:', error)
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
      if (submitData.contact_id) {
        await update(submitData.contact_id, submitData)
      } else {
        delete submitData.contact_id
        await create(submitData)
      }
      ElMessage.success(submitData.contact_id ? '编辑成功' : '新增成功')
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
    contact_id: null,
    customer_id: null,
    name: '',
    position: '',
    mobile: '',
    phone: '',
    email: '',
    wechat: '',
    gender: 1,
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
  loadCustomers()
  loadData()
})
</script>

<style scoped>
.contact-container { padding: 0; }
.search-card, .table-card { margin-bottom: 20px; }
.table-header { display: flex; justify-content: space-between; align-items: center; }
.table-title { font-size: 16px; font-weight: 600; color: #303133; }
.pagination-container { display: flex; justify-content: flex-end; margin-top: 20px; }
:deep(.el-table) { font-size: 14px; }
</style>
