<template>
  <div class="role-container">
    <el-card class="search-card">
      <el-form :inline="true" :model="searchForm" class="search-form">
        <el-form-item label="角色名称">
          <el-input v-model="searchForm.name" placeholder="请输入角色名称" clearable @keyup.enter="handleSearch" />
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
          <span class="table-title">角色列表</span>
          <div class="table-actions">
            <el-button type="primary" @click="handleAdd">
              <el-icon><Plus /></el-icon>
              新增角色
            </el-button>
          </div>
        </div>
      </template>

      <el-table
        v-loading="tableLoading"
        :data="roleList"
        stripe
        border
      >
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="name" label="角色名称" width="150" />
        <el-table-column prop="code" label="角色代码" width="150" />
        <el-table-column prop="description" label="描述" min-width="200" show-overflow-tooltip />
        <el-table-column prop="user_count" label="用户数" width="100" align="center" />
        <el-table-column prop="status" label="状态" width="80">
          <template #default="{ row }">
            <el-switch
              v-model="row.status"
              :active-value="1"
              :inactive-value="0"
              @change="handleStatusChange(row)"
            />
          </template>
        </el-table-column>
        <el-table-column prop="create_time" label="创建时间" width="160" />
        <el-table-column label="操作" width="220" fixed="right">
          <template #default="{ row }">
            <el-button type="primary" link size="small" @click="handleView(row)">
              查看
            </el-button>
            <el-button type="primary" link size="small" @click="handleEdit(row)">
              编辑
            </el-button>
            <el-button type="primary" link size="small" @click="handlePermission(row)">
              权限
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
        <el-form-item label="角色名称" prop="name">
          <el-input v-model="formData.name" placeholder="请输入角色名称" />
        </el-form-item>

        <el-form-item label="角色代码" prop="code">
          <el-input v-model="formData.code" placeholder="请输入角色代码" />
        </el-form-item>

        <el-form-item label="描述" prop="description">
          <el-input v-model="formData.description" type="textarea" :rows="3" placeholder="请输入描述" />
        </el-form-item>
      </el-form>

      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" :loading="submitLoading" @click="handleSubmit">
          确定
        </el-button>
      </template>
    </el-dialog>

    <el-dialog v-model="detailVisible" title="角色详情" width="600px">
      <el-descriptions :column="2" border>
        <el-descriptions-item label="角色名称">{{ currentRole.name }}</el-descriptions-item>
        <el-descriptions-item label="角色代码">{{ currentRole.code }}</el-descriptions-item>
        <el-descriptions-item label="描述" :span="2">{{ currentRole.description }}</el-descriptions-item>
        <el-descriptions-item label="用户数">{{ currentRole.user_count }}</el-descriptions-item>
        <el-descriptions-item label="创建时间">{{ currentRole.create_time }}</el-descriptions-item>
      </el-descriptions>
    </el-dialog>

    <el-dialog v-model="permissionDialogVisible" title="分配权限" width="700px">
      <div class="permission-tree-container">
        <el-tree
          ref="permissionTreeRef"
          :data="permissionTree"
          :default-expand-all="true"
          node-key="id"
          show-checkbox
          :props="{
            label: 'title',
            children: 'children'
          }"
        />
      </div>

      <template #footer>
        <el-button @click="permissionDialogVisible = false">取消</el-button>
        <el-button type="primary" :loading="permissionSubmitLoading" @click="handlePermissionSubmit">
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
import { getRoleList, create, update, deleteRole, getPermissionList, assignPermission } from '@/api/modules/system'

const searchForm = reactive({
  name: ''
})

const tableLoading = ref(false)
const roleList = ref([])
const permissionTree = ref([])

const pagination = reactive({
  page: 1,
  pageSize: 10,
  total: 0
})

const dialogVisible = ref(false)
const detailVisible = ref(false)
const permissionDialogVisible = ref(false)
const dialogTitle = ref('')
const submitLoading = ref(false)
const permissionSubmitLoading = ref(false)
const formRef = ref(null)
const permissionTreeRef = ref(null)
const currentRole = ref({})
const currentRoleId = ref(null)

const formData = reactive({
  id: null,
  name: '',
  code: '',
  description: ''
})

const formRules = {
  name: [{ required: true, message: '请输入角色名称', trigger: 'blur' }],
  code: [
    { required: true, message: '请输入角色代码', trigger: 'blur' },
    { pattern: /^[a-z_]+$/, message: '角色代码只能包含小写字母和下划线', trigger: 'blur' }
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
    
    const res = await getRoleList(params)
    if (res.code === 200) {
      roleList.value = res.data.list || []
      pagination.total = res.data.total || 0
    }
  } catch (error) {
    console.error('获取角色列表失败:', error)
    ElMessage.error('获取角色列表失败')
  } finally {
    tableLoading.value = false
  }
}

const loadPermissions = async () => {
  try {
    const res = await getPermissionList()
    if (res.code === 200) {
      permissionTree.value = res.data || []
    }
  } catch (error) {
    console.error('获取权限列表失败:', error)
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
  dialogTitle.value = '新增角色'
  dialogVisible.value = true
}

const handleEdit = (row) => {
  dialogTitle.value = '编辑角色'
  Object.assign(formData, {
    id: row.id,
    name: row.name,
    code: row.code,
    description: row.description
  })
  dialogVisible.value = true
}

const handleView = (row) => {
  currentRole.value = row
  detailVisible.value = true
}

const handleDelete = async (row) => {
  if (row.id === 1) {
    ElMessage.warning('超级管理员角色不能删除')
    return
  }
  
  try {
    await ElMessageBox.confirm(`确定要删除角色"${row.name}"吗？`, '提示', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    })
    
    const res = await deleteRole(row.id)
    if (res.code === 200) {
      ElMessage.success('删除成功')
      loadData()
    }
  } catch (error) {
    if (error !== 'cancel') {
      console.error('删除角色失败:', error)
      ElMessage.error('删除角色失败')
    }
  }
}

const handleStatusChange = async (row) => {
  try {
    const res = await update({ id: row.id, status: row.status })
    if (res.code === 200) {
      ElMessage.success(row.status === 1 ? '角色已启用' : '角色已禁用')
    }
  } catch (error) {
    console.error('修改状态失败:', error)
    ElMessage.error('修改状态失败')
    row.status = row.status === 1 ? 0 : 1
  }
}

const handlePermission = async (row) => {
  currentRoleId.value = row.id
  permissionDialogVisible.value = true
  
  await loadPermissions()
  
  nextTick(() => {
    if (row.rules) {
      const checkedKeys = row.rules.split(',').filter(Boolean).map(Number)
      permissionTreeRef.value?.setCheckedKeys(checkedKeys)
    } else {
      permissionTreeRef.value?.setCheckedKeys([])
    }
  })
}

const handlePermissionSubmit = async () => {
  const checkedNodes = permissionTreeRef.value?.getCheckedNodes() || []
  const halfCheckedNodes = permissionTreeRef.value?.getHalfCheckedNodes() || []
  
  const allChecked = [...checkedNodes, ...halfCheckedNodes]
  const ruleIds = allChecked.map(node => node.id).join(',')
  
  permissionSubmitLoading.value = true
  try {
    const res = await assignPermission({
      id: currentRoleId.value,
      rules: ruleIds
    })
    if (res.code === 200) {
      ElMessage.success('权限分配成功')
      permissionDialogVisible.value = false
      loadData()
    }
  } catch (error) {
    console.error('分配权限失败:', error)
    ElMessage.error('分配权限失败')
  } finally {
    permissionSubmitLoading.value = false
  }
}

const handleSubmit = async () => {
  if (!formRef.value) return
  
  await formRef.value.validate(async (valid) => {
    if (!valid) return
    
    submitLoading.value = true
    try {
      const res = formData.id ? await update(formData) : await create(formData)
      
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
    name: '',
    code: '',
    description: ''
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
.role-container {
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

.permission-tree-container {
  max-height: 400px;
  overflow-y: auto;
}

:deep(.el-table) {
  font-size: 14px;
}

:deep(.el-form-item) {
  margin-bottom: 18px;
}
</style>
