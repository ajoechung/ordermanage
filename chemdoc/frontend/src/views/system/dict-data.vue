<template>
  <div class="dict-container">
    <el-card class="search-card">
      <el-form :inline="true" :model="searchForm" class="search-form">
        <el-form-item label="所属类型">
          <el-select v-model="searchForm.dict_id" placeholder="请选择字典类型" clearable style="width: 200px">
            <el-option v-for="item in dictTypes" :key="item.dict_id" :label="item.dict_name" :value="item.dict_id" />
          </el-select>
        </el-form-item>
        <el-form-item label="字典标签/值">
          <el-input v-model="searchForm.keyword" placeholder="请输入字典标签或值" clearable @keyup.enter="handleSearch" />
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
          <span class="table-title">字典数据列表</span>
          <div class="table-actions">
            <el-button type="primary" @click="handleAdd">
              <el-icon><Plus /></el-icon>
              新增字典数据
            </el-button>
          </div>
        </div>
      </template>

      <el-table
        v-loading="tableLoading"
        :data="tableData"
        stripe
        border
      >
        <el-table-column prop="data_id" label="ID" width="80" />
        <el-table-column prop="dict_name" label="所属类型" width="150" />
        <el-table-column prop="dict_label" label="字典标签" width="150" />
        <el-table-column prop="dict_value" label="字典值" width="150" />
        <el-table-column prop="description" label="描述" min-width="200" show-overflow-tooltip />
        <el-table-column prop="status" label="状态" width="80">
          <template #default="{ row }">
            <el-tag :type="parseInt(row.status) === 1 ? 'success' : 'danger'">
              {{ parseInt(row.status) === 1 ? '启用' : '禁用' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="sort" label="排序" width="80" />
        <el-table-column prop="create_time" label="创建时间" width="160" />
        <el-table-column label="操作" width="200" fixed="right">
          <template #default="{ row }">
            <el-button type="primary" link size="small" @click="handleEdit(row)">编辑</el-button>
            <el-button type="danger" link size="small" @click="handleDelete(row)">删除</el-button>
          </template>
        </el-table-column>
      </el-table>

      <div class="pagination-container">
        <el-pagination
          v-model:current-page="pagination.page"
          v-model:page-size="pagination.page_size"
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
        <el-form-item label="所属类型" prop="dict_id">
          <el-select v-model="formData.dict_id" placeholder="请选择字典类型" style="width: 100%">
            <el-option v-for="item in dictTypes" :key="item.dict_id" :label="item.dict_name" :value="item.dict_id" />
          </el-select>
        </el-form-item>
        <el-form-item label="字典标签" prop="dict_label">
          <el-input v-model="formData.dict_label" placeholder="请输入字典标签" />
        </el-form-item>
        <el-form-item label="字典值" prop="dict_value">
          <el-input v-model="formData.dict_value" placeholder="请输入字典值" />
        </el-form-item>
        <el-form-item label="描述" prop="description">
          <el-input v-model="formData.description" type="textarea" :rows="3" placeholder="请输入描述" />
        </el-form-item>
        <el-form-item label="状态" prop="status">
          <el-select v-model="formData.status" placeholder="请选择状态">
            <el-option label="启用" :value="1" />
            <el-option label="禁用" :value="0" />
          </el-select>
        </el-form-item>
        <el-form-item label="排序" prop="sort">
          <el-input-number v-model="formData.sort" :min="0" style="width: 100%" />
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
import {
  getDictDataList,
  createDictData,
  updateDictData,
  deleteDictData,
  getDictTypeList
} from '@/api/modules/dict'

const searchForm = reactive({
  dict_id: '',
  keyword: ''
})

const tableLoading = ref(false)
const tableData = ref([])
const dictTypes = ref([])

const pagination = reactive({
  page: 1,
  page_size: 20,
  total: 0
})

const dialogVisible = ref(false)
const dialogTitle = ref('')
const submitLoading = ref(false)
const formRef = ref(null)

const formData = reactive({
  data_id: null,
  dict_id: '',
  dict_label: '',
  dict_value: '',
  description: '',
  status: 1,
  sort: 0
})

const formRules = {
  dict_id: [{ required: true, message: '请选择所属类型', trigger: 'change' }],
  dict_label: [{ required: true, message: '请输入字典标签', trigger: 'blur' }],
  dict_value: [{ required: true, message: '请输入字典值', trigger: 'blur' }],
  status: [{ required: true, message: '请选择状态', trigger: 'change' }]
}

const loadData = async () => {
  tableLoading.value = true
  try {
    const params = {
      page: pagination.page,
      page_size: pagination.page_size,
      dict_id: searchForm.dict_id,
      keyword: searchForm.keyword
    }
    const res = await getDictDataList(params)
    if (res.code === 200) {
      tableData.value = res.data.list || []
      pagination.total = res.data.total || 0
    }
  } catch (error) {
    console.error('获取字典数据列表失败:', error)
    ElMessage.error('获取字典数据列表失败')
  } finally {
    tableLoading.value = false
  }
}

const loadDictTypes = async () => {
  try {
    const res = await getDictTypeList({ page: 1, page_size: 100 })
    if (res.code === 200) {
      dictTypes.value = res.data.list || []
    }
  } catch (error) {
    console.error('获取字典类型列表失败:', error)
  }
}

const handleSearch = () => {
  pagination.page = 1
  loadData()
}

const handleReset = () => {
  searchForm.dict_id = ''
  searchForm.keyword = ''
  pagination.page = 1
  loadData()
}

const handleAdd = () => {
  dialogTitle.value = '新增字典数据'
  Object.assign(formData, {
    data_id: null,
    dict_id: '',
    dict_label: '',
    dict_value: '',
    description: '',
    status: 1,
    sort: 0
  })
  dialogVisible.value = true
}

const handleEdit = (row) => {
  dialogTitle.value = '编辑字典数据'
  Object.assign(formData, {
    data_id: row.data_id,
    dict_id: row.dict_id,
    dict_label: row.dict_label,
    dict_value: row.dict_value,
    description: row.description || '',
    status: row.status !== undefined ? row.status : 1,
    sort: row.sort || 0
  })
  dialogVisible.value = true
}

const handleDelete = async (row) => {
  try {
    await ElMessageBox.confirm(`确定要删除字典数据 "${row.dict_label}" 吗？`, '提示', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    })

    const res = await deleteDictData(row.data_id)
    if (res.code === 200) {
      ElMessage.success('删除成功')
      loadData()
    }
  } catch (error) {
    if (error !== 'cancel') {
      console.error('删除字典数据失败:', error)
      ElMessage.error('删除字典数据失败')
    }
  }
}

const handleSubmit = async () => {
  if (!formRef.value) return

  await formRef.value.validate(async (valid) => {
    if (!valid) return

    submitLoading.value = true
    try {
      let res
      if (formData.data_id) {
        res = await updateDictData(formData.data_id, formData)
      } else {
        res = await createDictData(formData)
      }

      if (res.code === 200) {
        ElMessage.success(formData.data_id ? '编辑成功' : '新增成功')
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
    data_id: null,
    dict_id: '',
    dict_label: '',
    dict_value: '',
    description: '',
    status: 1,
    sort: 0
  })
}

const handleSizeChange = (size) => {
  pagination.page_size = size
  loadData()
}

const handlePageChange = (page) => {
  pagination.page = page
  loadData()
}

onMounted(() => {
  loadData()
  loadDictTypes()
})
</script>

<style scoped>
.dict-container {
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