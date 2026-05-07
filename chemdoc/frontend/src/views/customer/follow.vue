<template>
  <div class="follow-container">
    <el-card class="search-card">
      <el-form :inline="true" :model="searchForm" class="search-form">
        <el-form-item label="客户名称">
          <el-select
            v-model="searchForm.customer_id"
            placeholder="请选择"
            clearable
            filterable
            @change="handleCustomerChange"
          >
            <el-option
              v-for="customer in customerList"
              :key="customer.id"
              :label="customer.name"
              :value="customer.id"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="跟进方式">
          <el-select v-model="searchForm.follow_type" placeholder="请选择" clearable>
            <el-option label="电话" value="phone" />
            <el-option label="拜访" value="visit" />
            <el-option label="邮件" value="email" />
            <el-option label="会议" value="meeting" />
            <el-option label="其他" value="other" />
          </el-select>
        </el-form-item>
        <el-form-item label="跟进时间">
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
          <span class="table-title">跟进记录</span>
          <div class="table-actions">
            <el-button type="primary" @click="handleAdd">
              <el-icon><Plus /></el-icon>
              新增跟进
            </el-button>
          </div>
        </div>
      </template>

      <el-table
        v-loading="tableLoading"
        :data="followList"
        stripe
        border
      >
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="customer_name" label="客户名称" min-width="180" show-overflow-tooltip />
        <el-table-column prop="follow_type" label="跟进方式" width="100">
          <template #default="{ row }">
            <el-tag :type="getFollowTypeTag(row.follow_type)" size="small">
              {{ getFollowTypeText(row.follow_type) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="content" label="跟进内容" min-width="300" show-overflow-tooltip />
        <el-table-column prop="next_follow_time" label="下次跟进时间" width="120">
          <template #default="{ row }">
            {{ row.next_follow_time || '-' }}
          </template>
        </el-table-column>
        <el-table-column prop="create_user_name" label="跟进人" width="100" />
        <el-table-column prop="create_time" label="跟进时间" width="160" />
        <el-table-column label="操作" width="180" fixed="right">
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
        <el-form-item label="客户名称" prop="customer_id">
          <el-select v-model="formData.customer_id" placeholder="请选择" style="width: 100%" filterable>
            <el-option
              v-for="customer in customerList"
              :key="customer.id"
              :label="customer.name"
              :value="customer.id"
            />
          </el-select>
        </el-form-item>

        <el-form-item label="跟进方式" prop="follow_type">
          <el-select v-model="formData.follow_type" placeholder="请选择" style="width: 100%">
            <el-option label="电话" value="phone" />
            <el-option label="拜访" value="visit" />
            <el-option label="邮件" value="email" />
            <el-option label="会议" value="meeting" />
            <el-option label="其他" value="other" />
          </el-select>
        </el-form-item>

        <el-form-item label="跟进内容" prop="content">
          <el-input
            v-model="formData.content"
            type="textarea"
            :rows="5"
            placeholder="请输入跟进内容"
          />
        </el-form-item>

        <el-form-item label="下次跟进">
          <el-date-picker
            v-model="formData.next_follow_time"
            type="datetime"
            placeholder="选择下次跟进时间"
            value-format="YYYY-MM-DD HH:mm:ss"
            style="width: 100%"
          />
        </el-form-item>
      </el-form>

      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" :loading="submitLoading" @click="handleSubmit">
          确定
        </el-button>
      </template>
    </el-dialog>

    <el-dialog v-model="detailVisible" title="跟进详情" width="600px">
      <el-descriptions :column="1" border>
        <el-descriptions-item label="客户名称">{{ currentFollow.customer_name }}</el-descriptions-item>
        <el-descriptions-item label="跟进方式">
          <el-tag :type="getFollowTypeTag(currentFollow.follow_type)" size="small">
            {{ getFollowTypeText(currentFollow.follow_type) }}
          </el-tag>
        </el-descriptions-item>
        <el-descriptions-item label="跟进内容">
          <div style="white-space: pre-wrap;">{{ currentFollow.content }}</div>
        </el-descriptions-item>
        <el-descriptions-item label="下次跟进时间">
          {{ currentFollow.next_follow_time || '-' }}
        </el-descriptions-item>
        <el-descriptions-item label="跟进人">{{ currentFollow.create_user_name }}</el-descriptions-item>
        <el-descriptions-item label="跟进时间">{{ currentFollow.create_time }}</el-descriptions-item>
      </el-descriptions>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Search, Refresh, Plus } from '@element-plus/icons-vue'
import { getList, create, update, deleteFollow } from '@/api/modules/follow'
import { getAll as getCustomerList } from '@/api/modules/customer'

const route = useRoute()

const searchForm = reactive({
  customer_id: '',
  follow_type: '',
  date_range: []
})

const tableLoading = ref(false)
const followList = ref([])
const customerList = ref([])

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
const currentFollow = ref({})

const formData = reactive({
  id: null,
  customer_id: null,
  customer_name: '',
  follow_type: '',
  content: '',
  next_follow_time: ''
})

const formRules = {
  customer_id: [{ required: true, message: '请选择客户', trigger: 'change' }],
  follow_type: [{ required: true, message: '请选择跟进方式', trigger: 'change' }],
  content: [{ required: true, message: '请输入跟进内容', trigger: 'blur' }]
}

const followTypeMap = {
  phone: { text: '电话', tag: 'primary' },
  visit: { text: '拜访', tag: 'success' },
  email: { text: '邮件', tag: 'warning' },
  meeting: { text: '会议', tag: 'info' },
  other: { text: '其他', tag: '' }
}

const getFollowTypeText = (type) => followTypeMap[type]?.text || type
const getFollowTypeTag = (type) => followTypeMap[type]?.tag || 'info'

const loadData = async () => {
  tableLoading.value = true
  try {
    const params = {
      page: pagination.page,
      page_size: pagination.pageSize,
      customer_id: searchForm.customer_id,
      follow_type: searchForm.follow_type
    }
    
    if (searchForm.date_range && searchForm.date_range.length === 2) {
      params.start_date = searchForm.date_range[0]
      params.end_date = searchForm.date_range[1]
    }
    
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
    const res = await getCustomerList()
    if (res.code === 200) {
      customerList.value = res.data || []
    }
  } catch (error) {
    console.error('获取客户列表失败:', error)
  }
}

const handleCustomerChange = () => {
  pagination.page = 1
  loadData()
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
  dialogTitle.value = '新增跟进'
  dialogVisible.value = true
}

const handleEdit = (row) => {
  dialogTitle.value = '编辑跟进'
  Object.assign(formData, {
    id: row.id,
    customer_id: row.customer_id,
    customer_name: row.customer_name,
    follow_type: row.follow_type,
    content: row.content,
    next_follow_time: row.next_follow_time
  })
  dialogVisible.value = true
}

const handleView = (row) => {
  currentFollow.value = row
  detailVisible.value = true
}

const handleDelete = async (row) => {
  try {
    await ElMessageBox.confirm(`确定要删除该跟进记录吗？`, '提示', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    })
    
    const res = await deleteFollow(row.id)
    if (res.code === 200) {
      ElMessage.success('删除成功')
      loadData()
    }
  } catch (error) {
    if (error !== 'cancel') {
      console.error('删除跟进记录失败:', error)
      ElMessage.error('删除跟进记录失败')
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
    follow_type: '',
    content: '',
    next_follow_time: ''
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
  
  if (route.query.customer_id) {
    searchForm.customer_id = Number(route.query.customer_id)
  }
  
  loadData()
})
</script>

<style scoped>
.follow-container {
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
