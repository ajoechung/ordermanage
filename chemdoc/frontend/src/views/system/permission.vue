<template>
  <div class="permission-container">
    <el-card class="search-card">
      <el-form :inline="true" :model="searchForm" class="search-form">
        <el-form-item label="权限名称">
          <el-input v-model="searchForm.title" placeholder="请输入权限名称" clearable @keyup.enter="handleSearch" />
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
          <span class="table-title">权限列表</span>
          <div class="table-actions">
            <el-button type="primary" @click="handleAdd">
              <el-icon><Plus /></el-icon>
              新增权限
            </el-button>
          </div>
        </div>
      </template>

      <el-table
        v-loading="tableLoading"
        :data="permissionList"
        stripe
        border
        row-key="id"
        :tree-props="{ children: 'children', hasChildren: 'hasChildren' }"
      >
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="title" label="权限名称" min-width="150" />
        <el-table-column prop="name" label="规则标识" min-width="150" />
        <el-table-column prop="pid" label="父级ID" width="100" />
        <el-table-column prop="sort" label="排序" width="80" />
        <el-table-column prop="status" label="状态" width="100">
          <template #default="{ row }">
            <el-tag :type="getStatusType(row.status)">
              {{ getStatusText(row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="is_menu" label="是否菜单" width="100">
          <template #default="{ row }">
            <el-tag :type="row.is_menu === 1 ? 'success' : 'info'">
              {{ row.is_menu === 1 ? '是' : '否' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="remark" label="备注" min-width="150" show-overflow-tooltip />
        <el-table-column prop="create_time" label="创建时间" width="180" />
        <el-table-column label="操作" width="200" fixed="right">
          <template #default="{ row }">
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
        <el-form-item label="权限名称" prop="title">
          <el-input v-model="formData.title" placeholder="请输入权限名称" />
        </el-form-item>

        <el-form-item label="规则标识" prop="name">
          <el-input v-model="formData.name" placeholder="请输入规则标识" />
        </el-form-item>

        <el-form-item label="父级权限" prop="pid">
          <el-tree-select
            v-model="formData.pid"
            :data="permissionTree"
            :props="{ label: 'title', value: 'id', children: 'children' }"
            placeholder="请选择父级权限"
            clearable
            check-strictly
            value-key="id"
          />
        </el-form-item>

        <el-form-item label="类型" prop="type">
          <el-select v-model="formData.type" placeholder="请选择类型">
            <el-option label="规则" :value="1" />
          </el-select>
        </el-form-item>

        <el-form-item label="排序" prop="sort">
          <el-input-number v-model="formData.sort" :min="0" />
        </el-form-item>

        <el-form-item label="状态" prop="status">
          <el-select v-model="formData.status" placeholder="请选择状态">
            <el-option label="启用" :value="1" />
            <el-option label="禁用" :value="0" />
          </el-select>
        </el-form-item>

        <el-form-item label="是否菜单" prop="is_menu">
          <el-switch v-model="formData.is_menu" :active-value="1" :inactive-value="0" />
        </el-form-item>

        <el-form-item label="条件表达式" prop="condition">
          <el-input v-model="formData.condition" type="textarea" :rows="2" placeholder="请输入条件表达式" />
        </el-form-item>

        <el-form-item label="备注" prop="remark">
          <el-input v-model="formData.remark" type="textarea" :rows="2" placeholder="请输入备注" />
        </el-form-item>
      </el-form>

      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" :loading="submitLoading" @click="handleSubmit">
          确定
        </el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Search, Refresh, Plus } from '@element-plus/icons-vue'
import { getPermissionList, getPermissionTree, createPermission, updatePermission, deletePermission } from '@/api/modules/system'

// 辅助函数：安全的状态处理
const getStatusValue = (status) => {
  if (status == null || status === '') return 1
  const val = Number(status)
  return isNaN(val) ? 1 : val
}

const getStatusType = (status) => {
  return getStatusValue(status) === 1 ? 'success' : 'danger'
}

const getStatusText = (status) => {
  return getStatusValue(status) === 1 ? '启用' : '禁用'
}

const searchForm = reactive({
  title: ''
})

const tableLoading = ref(false)
const permissionList = ref([])
const permissionTree = ref([])

const pagination = reactive({
  page: 1,
  pageSize: 10,
  total: 0
})

const dialogVisible = ref(false)
const dialogTitle = ref('')
const submitLoading = ref(false)
const formRef = ref(null)

const formData = reactive({
  id: null,
  name: '',
  title: '',
  type: 1,
  pid: 0,
  status: 1,
  condition: '',
  remark: '',
  is_menu: 0,
  sort: 0
})

const formRules = {
  name: [
    { required: true, message: '请输入规则标识', trigger: 'blur' }
  ],
  title: [
    { required: true, message: '请输入权限名称', trigger: 'blur' }
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
    
    const res = await getPermissionList(params)
    if (res.code === 200) {
      permissionList.value = res.data.list || []
      pagination.total = res.data.total || 0
    }
  } catch (error) {
    console.error('获取权限列表失败:', error)
    ElMessage.error('获取权限列表失败')
  } finally {
    tableLoading.value = false
  }
}

const loadPermissionTree = async () => {
  try {
    const res = await getPermissionTree()
    if (res.code === 200) {
      permissionTree.value = [{ id: 0, title: '顶级权限', children: res.data || [] }]
    }
  } catch (error) {
    console.error('获取权限树失败:', error)
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

const handleAdd = async () => {
  dialogTitle.value = '新增权限'
  await loadPermissionTree()
  Object.assign(formData, {
    id: null,
    name: '',
    title: '',
    type: 1,
    pid: 0,
    status: 1,
    condition: '',
    remark: '',
    is_menu: 0,
    sort: 0
  })
  dialogVisible.value = true
}

const handleEdit = async (row) => {
  dialogTitle.value = '编辑权限'
  await loadPermissionTree()
  Object.assign(formData, {
    id: row.id,
    name: row.name,
    title: row.title,
    type: row.type,
    pid: row.pid,
    status: getStatusValue(row.status),
    condition: row.condition,
    remark: row.remark,
    is_menu: row.is_menu,
    sort: row.sort
  })
  dialogVisible.value = true
}

const handleDelete = async (row) => {
  try {
    await ElMessageBox.confirm(`确定要删除权限"${row.title}"吗？`, '提示', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    })
    
    const res = await deletePermission(row.id)
    if (res.code === 200) {
      ElMessage.success('删除成功')
      loadData()
    }
  } catch (error) {
    if (error !== 'cancel') {
      console.error('删除权限失败:', error)
      ElMessage.error(error.msg || '删除权限失败')
    }
  }
}

const handleSubmit = async () => {
  if (!formRef.value) return
  
  await formRef.value.validate(async (valid) => {
    if (!valid) return
    
    submitLoading.value = true
    try {
      const res = formData.id ? await updatePermission(formData) : await createPermission(formData)
      
      if (res.code === 200) {
        ElMessage.success(formData.id ? '编辑成功' : '新增成功')
        dialogVisible.value = false
        loadData()
      } else {
        ElMessage.error(res.msg || '操作失败')
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
    title: '',
    type: 1,
    pid: 0,
    status: 1,
    condition: '',
    remark: '',
    is_menu: 0,
    sort: 0
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
.permission-container {
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
