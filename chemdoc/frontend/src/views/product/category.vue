<template>
  <div class="category-container">
    <el-card class="table-card">
      <template #header>
        <div class="table-header">
          <span class="table-title">产品分类</span>
          <el-button type="primary" @click="handleAdd(null)"><Plus />新增分类</el-button>
        </div>
      </template>

      <el-table v-loading="tableLoading" :data="categoryList" stripe border row-key="category_id" default-expand-all>
        <el-table-column prop="name" label="分类名称" min-width="200" />
        <el-table-column prop="sort" label="排序" width="80" align="center" />
        <el-table-column prop="is_show" label="显示" width="80" align="center">
          <template #default="{ row }">
            <el-tag :type="row.is_show === 1 ? 'success' : 'info'" size="small">{{ row.is_show === 1 ? '是' : '否' }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="200" fixed="right">
          <template #default="{ row }">
            <el-button type="primary" link size="small" @click="handleAdd(row)">添加子分类</el-button>
            <el-button type="primary" link size="small" @click="handleEdit(row)">编辑</el-button>
            <el-button type="danger" link size="small" @click="handleDelete(row)">删除</el-button>
          </template>
        </el-table-column>
      </el-table>
    </el-card>

    <el-dialog v-model="dialogVisible" :title="dialogTitle" width="500px" :close-on-click-modal="false" @close="handleDialogClose">
      <el-form ref="formRef" :model="formData" :rules="formRules" label-width="100px">
        <el-form-item v-if="formData.parent_id" label="上级分类"><span>{{ parentCategoryName }}</span></el-form-item>
        <el-form-item v-else label="上级分类">
          <el-select v-model="formData.parent_id" placeholder="请选择上级分类（不选则为一级分类）" clearable style="width: 100%">
            <el-option v-for="cat in flatCategoryList" :key="cat.category_id" :label="cat.name" :value="cat.category_id" />
          </el-select>
        </el-form-item>
        <el-form-item label="分类名称" prop="name">
          <el-input v-model="formData.name" placeholder="请输入分类名称" />
        </el-form-item>
        <el-form-item label="排序" prop="sort">
          <el-input-number v-model="formData.sort" :min="0" :max="9999" style="width: 100%" />
        </el-form-item>
        <el-form-item label="是否显示" prop="is_show">
          <el-radio-group v-model="formData.is_show">
            <el-radio :label="1">显示</el-radio>
            <el-radio :label="0">隐藏</el-radio>
          </el-radio-group>
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
import { ref, reactive, computed, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Plus } from '@element-plus/icons-vue'
import { getCategoryList, createCategory, updateCategory, deleteCategory } from '@/api/modules/product'

const tableLoading = ref(false)
const categoryList = ref([])

const dialogVisible = ref(false)
const dialogTitle = ref('')
const submitLoading = ref(false)
const formRef = ref(null)
const parentCategoryName = ref('')

const formData = reactive({
  category_id: null,
  parent_id: null,
  name: '',
  sort: 0,
  is_show: 1
})

const formRules = {
  name: [{ required: true, message: '请输入分类名称', trigger: 'blur' }]
}

const loadData = async () => {
  tableLoading.value = true
  try {
    const res = await getCategoryList({ tree: true })
    if (res.code === 200) {
      const data = res.data
      if (Array.isArray(data)) {
        categoryList.value = data
      } else if (data && typeof data === 'object') {
        categoryList.value = data.list || data.data || []
      }
    }
  } catch (error) {
    console.error('获取分类列表失败:', error)
    ElMessage.error('获取分类列表失败')
  } finally {
    tableLoading.value = false
  }
}

const flatCategoryList = computed(() => {
  const result = []
  const flatten = (list, level = 0) => {
    for (const item of list) {
      result.push({ ...item, level })
      if (item.children && item.children.length > 0) {
        flatten(item.children, level + 1)
      }
    }
  }
  flatten(categoryList.value)
  return result
})

const findName = (list, id) => {
  for (const item of list) {
    if (item.category_id === id) return item.name
    if (item.children && item.children.length > 0) {
      const name = findName(item.children, id)
      if (name) return name
    }
  }
  return ''
}

const handleAdd = (parent) => {
  dialogTitle.value = parent ? '添加子分类' : '新增分类'
  Object.assign(formData, { category_id: null, parent_id: parent?.category_id || null, name: '', sort: 0 })
  parentCategoryName.value = parent ? parent.name : ''
  dialogVisible.value = true
}

const handleEdit = (row) => {
  dialogTitle.value = '编辑分类'
  Object.assign(formData, { 
    category_id: row.category_id, 
    parent_id: row.parent_id, 
    name: row.name, 
    sort: row.sort || 0,
    is_show: row.is_show ?? 1
  })
  if (row.parent_id) {
    parentCategoryName.value = findName(categoryList.value, row.parent_id)
  }
  dialogVisible.value = true
}

const handleDelete = async (row) => {
  try {
    await ElMessageBox.confirm(`确定要删除分类"${row.name}"吗？`, '提示', { confirmButtonText: '确定', cancelButtonText: '取消', type: 'warning' })
    const res = await deleteCategory(row.category_id)
    if (res.code === 200) {
      ElMessage.success('删除成功')
      loadData()
    } else {
      ElMessage.error(res.msg || '删除失败')
    }
  } catch (error) {
    if (error !== 'cancel') { console.error('删除分类失败:', error) }
  }
}

const handleSubmit = async () => {
  if (!formRef.value) return
  await formRef.value.validate(async (valid) => {
    if (!valid) return
    submitLoading.value = true
    try {
      if (formData.category_id) {
        await updateCategory(formData.category_id, formData)
      } else {
        await createCategory(formData)
      }
      ElMessage.success(formData.category_id ? '编辑成功' : '新增成功')
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
  Object.assign(formData, { category_id: null, parent_id: null, name: '', sort: 0, is_show: 1 })
}

onMounted(() => { loadData() })
</script>

<style scoped>
.category-container { padding: 0; }
.table-card { margin-bottom: 20px; }
.table-header { display: flex; justify-content: space-between; align-items: center; }
.table-title { font-size: 16px; font-weight: 600; color: #303133; }
:deep(.el-table) { font-size: 14px; }
</style>
