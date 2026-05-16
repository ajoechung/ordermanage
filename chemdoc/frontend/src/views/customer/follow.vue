<template>
  <div class="follow-container">
    <el-card class="search-card">
      <el-form :inline="true" :model="searchForm" class="search-form">
        <el-form-item label="所属客户">
          <el-select v-model="searchForm.customer_id" placeholder="请选择" clearable filterable @change="handleSearch" style="width: 220px">
            <el-option v-for="c in customerList" :key="c.customer_id" :label="c.name" :value="c.customer_id" />
          </el-select>
        </el-form-item>
        <el-form-item label="跟进方式">
          <el-select v-model="searchForm.method" placeholder="请选择" clearable @change="handleSearch" style="width: 150px">
            <el-option label="电话" value="电话" />
            <el-option label="拜访" value="拜访" />
            <el-option label="邮件" value="邮件" />
            <el-option label="其他" value="其他" />
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
          <span class="table-title">跟进记录</span>
          <el-button type="primary" @click="handleAdd"><Plus />新增跟进</el-button>
        </div>
      </template>

      <el-table v-loading="tableLoading" :data="followList" stripe border>
        <el-table-column prop="follow_id" label="ID" width="80" />
        <el-table-column prop="customer_name" label="客户名称" min-width="150" show-overflow-tooltip />
        <el-table-column prop="method" label="跟进方式" width="100">
          <template #default="{ row }">
            <el-tag size="small">{{ row.method }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="content" label="跟进内容" min-width="250" show-overflow-tooltip />
        <el-table-column prop="next_follow_time" label="下次跟进" width="160">
          <template #default="{ row }">
            {{ row.next_follow_time || '-' }}
          </template>
        </el-table-column>
        <el-table-column prop="create_time" label="跟进时间" width="160" />
        <el-table-column label="操作" width="120" fixed="right">
          <template #default="{ row }">
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
            <el-option v-for="c in customerList" :key="c.customer_id" :label="c.name" :value="c.customer_id" />
          </el-select>
        </el-form-item>

        <el-form-item label="跟进方式" prop="method">
          <el-select v-model="formData.method" placeholder="请选择" style="width: 100%">
            <el-option label="电话" value="电话" />
            <el-option label="拜访" value="拜访" />
            <el-option label="邮件" value="邮件" />
            <el-option label="其他" value="其他" />
          </el-select>
        </el-form-item>

        <el-form-item label="跟进内容" prop="content">
          <el-input v-model="formData.content" type="textarea" :rows="5" placeholder="请输入跟进内容" />
        </el-form-item>

        <el-form-item label="下次跟进">
          <el-date-picker v-model="formData.next_time" type="datetime" placeholder="选择时间" value-format="YYYY-MM-DD HH:mm:ss" style="width: 100%" />
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
import { getList, create, deleteFollow } from '@/api/modules/follow'
import { getAll } from '@/api/modules/customer'

const searchForm = reactive({
  customer_id: '',
  method: ''
})

const tableLoading = ref(false)
const followList = ref([])
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
  customer_id: null,
  method: '',
  content: '',
  next_time: ''
})

const formRules = {
  customer_id: [{ required: true, message: '请选择客户', trigger: 'change' }],
  method: [{ required: true, message: '请选择跟进方式', trigger: 'change' }],
  content: [{ required: true, message: '请输入跟进内容', trigger: 'blur' }]
}

const loadData = async () => {
  tableLoading.value = true
  try {
    const params = {
      page: pagination.page,
      page_size: pagination.pageSize
    }
    if (searchForm.customer_id) params.customer_id = searchForm.customer_id
    if (searchForm.method) params.method = searchForm.method

    const res = await getList(params)
    if (res.code === 200) {
      followList.value = res.data.list || []
      pagination.total = res.data.total || 0
    }
  } catch (error) {
    console.error('获取跟进记录失败:', error)
    ElMessage.error('获取跟进记录失败')
  } finally {
    tableLoading.value = false
  }
}

const loadCustomers = async () => {
  try {
    const res = await getAll()
    if (res.code === 200) {
      customerList.value = res.data.list || []
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
  Object.assign(searchForm, { customer_id: '', method: '' })
  pagination.page = 1
  loadData()
}

const handleAdd = () => {
  dialogTitle.value = '新增跟进'
  dialogVisible.value = true
}

const handleDelete = async (row) => {
  try {
    await ElMessageBox.confirm('确定要删除该跟进记录吗？', '提示', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    })

    const res = await deleteFollow(row.follow_id)
    if (res.code === 200) {
      ElMessage.success('删除成功')
      loadData()
    } else {
      ElMessage.error(res.msg || '删除失败')
    }
  } catch (error) {
    if (error !== 'cancel') {
      console.error('删除跟进记录失败:', error)
    }
  }
}

const handleSubmit = async () => {
  if (!formRef.value) return

  await formRef.value.validate(async (valid) => {
    if (!valid) return

    submitLoading.value = true
    try {
      await create(formData)
      ElMessage.success('新增成功')
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
  Object.assign(formData, { customer_id: null, method: '', content: '', next_time: '' })
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
.follow-container { padding: 0; }
.search-card, .table-card { margin-bottom: 20px; }
.table-header { display: flex; justify-content: space-between; align-items: center; }
.table-title { font-size: 16px; font-weight: 600; color: #303133; }
.pagination-container { display: flex; justify-content: flex-end; margin-top: 20px; }
:deep(.el-table) { font-size: 14px; }
</style>
