<template>
  <div class="product-container">
    <el-card class="search-card">
      <el-form :inline="true" :model="searchForm" class="search-form">
        <el-form-item label="产品名称">
          <el-input v-model="searchForm.keyword" placeholder="请输入产品名称" clearable @keyup.enter="handleSearch" />
        </el-form-item>
        <el-form-item label="产品分类">
          <el-select v-model="searchForm.category_id" placeholder="请选择" clearable>
            <el-option v-for="c in categoryList" :key="c.category_id" :label="c.name" :value="c.category_id" />
          </el-select>
        </el-form-item>
        <el-form-item label="状态">
          <el-select v-model="searchForm.status" placeholder="请选择" clearable style="width: 100px">
            <el-option label="上架" :value="1" />
            <el-option label="下架" :value="0" />
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
          <span class="table-title">产品列表</span>
          <el-button type="primary" @click="handleAdd"><Plus />新增产品</el-button>
        </div>
      </template>

      <el-table v-loading="tableLoading" :data="productList" stripe border>
        <el-table-column prop="product_id" label="ID" width="80" />
        <el-table-column prop="name" label="产品名称" min-width="200" show-overflow-tooltip />
        <el-table-column prop="category_name" label="产品分类" width="120" />
        <el-table-column prop="code" label="产品编码" width="120" />
        <el-table-column prop="spec" label="规格" width="100" />
        <el-table-column prop="unit" label="单位" width="80" align="center" />
        <el-table-column prop="price" label="单价" width="100" align="right">
          <template #default="{ row }">¥{{ Number(row.price || 0).toFixed(2) }}</template>
        </el-table-column>
        <el-table-column prop="status" label="状态" width="80">
          <template #default="{ row }">
            <el-tag :type="row.status === 1 ? 'success' : 'danger'" size="small">
              {{ row.status === 1 ? '上架' : '下架' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="create_time" label="创建时间" width="160" />
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

    <el-dialog v-model="dialogVisible" :title="dialogTitle" width="700px" :close-on-click-modal="false" @close="handleDialogClose">
      <el-form ref="formRef" :model="formData" :rules="formRules" label-width="100px">
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="产品名称" prop="name">
              <el-input v-model="formData.name" placeholder="请输入产品名称" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="产品分类" prop="category_id">
              <el-select v-model="formData.category_id" placeholder="请选择" style="width: 100%">
                <el-option v-for="c in categoryList" :key="c.category_id" :label="c.name" :value="c.category_id" />
              </el-select>
            </el-form-item>
          </el-col>
        </el-row>

        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="产品编码" prop="code">
              <el-input v-model="formData.code" placeholder="请输入产品编码" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="规格" prop="spec">
              <el-input v-model="formData.spec" placeholder="请输入规格型号" />
            </el-form-item>
          </el-col>
        </el-row>

        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="单位" prop="unit">
              <el-select v-model="formData.unit" placeholder="请选择" style="width: 100%">
                <el-option label="吨" value="吨" />
                <el-option label="千克" value="千克" />
                <el-option label="升" value="升" />
                <el-option label="桶" value="桶" />
                <el-option label="箱" value="箱" />
                <el-option label="件" value="件" />
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="单价" prop="price">
              <el-input-number v-model="formData.price" :min="0" :precision="2" :controls="false" style="width: 100%" placeholder="请输入单价" />
            </el-form-item>
          </el-col>
        </el-row>

        <el-form-item label="产品描述" prop="description">
          <el-input v-model="formData.description" type="textarea" :rows="3" placeholder="请输入产品描述" />
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
import { getList, create, update, deleteProduct, getCategoryList } from '@/api/modules/product'

const searchForm = reactive({
  keyword: '',
  category_id: '',
  status: ''
})

const tableLoading = ref(false)
const productList = ref([])
const categoryList = ref([])

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
  product_id: null,
  name: '',
  category_id: null,
  code: '',
  spec: '',
  unit: '',
  price: 0,
  description: ''
})

const formRules = {
  name: [{ required: true, message: '请输入产品名称', trigger: 'blur' }],
  price: [{ required: true, message: '请输入单价', trigger: 'blur' }]
}

const loadData = async () => {
  tableLoading.value = true
  try {
    const params = {
      page: pagination.page,
      page_size: pagination.pageSize
    }
    if (searchForm.keyword) params.keyword = searchForm.keyword
    if (searchForm.category_id) params.category_id = searchForm.category_id
    if (searchForm.status !== '') params.status = searchForm.status

    const res = await getList(params)
    if (res.code === 200) {
      productList.value = res.data.list || []
      pagination.total = res.data.total || 0
    }
  } catch (error) {
    console.error('获取产品列表失败:', error)
    ElMessage.error('获取产品列表失败')
  } finally {
    tableLoading.value = false
  }
}

const loadCategories = async () => {
  try {
    const res = await getCategoryList()
    if (res.code === 200) {
      categoryList.value = res.data || []
    }
  } catch (error) {
    console.error('获取分类列表失败:', error)
  }
}

const handleSearch = () => {
  pagination.page = 1
  loadData()
}

const handleReset = () => {
  Object.assign(searchForm, { keyword: '', category_id: '', status: '' })
  pagination.page = 1
  loadData()
}

const handleAdd = () => {
  dialogTitle.value = '新增产品'
  dialogVisible.value = true
}

const handleEdit = (row) => {
  dialogTitle.value = '编辑产品'
  Object.keys(formData).forEach(key => {
    formData[key] = row[key] ?? formData[key]
  })
  dialogVisible.value = true
}

const handleDelete = async (row) => {
  try {
    await ElMessageBox.confirm(`确定要删除产品"${row.name}"吗？`, '提示', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    })

    const res = await deleteProduct(row.product_id)
    if (res.code === 200) {
      ElMessage.success('删除成功')
      loadData()
    } else {
      ElMessage.error(res.msg || '删除失败')
    }
  } catch (error) {
    if (error !== 'cancel') {
      console.error('删除产品失败:', error)
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
      if (submitData.product_id) {
        await update(submitData.product_id, submitData)
      } else {
        delete submitData.product_id
        await create(submitData)
      }
      ElMessage.success(submitData.product_id ? '编辑成功' : '新增成功')
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
    product_id: null,
    name: '',
    category_id: null,
    code: '',
    spec: '',
    unit: '',
    price: 0,
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
  loadCategories()
  loadData()
})
</script>

<style scoped>
.product-container { padding: 0; }
.search-card, .table-card { margin-bottom: 20px; }
.table-header { display: flex; justify-content: space-between; align-items: center; }
.table-title { font-size: 16px; font-weight: 600; color: #303133; }
.pagination-container { display: flex; justify-content: flex-end; margin-top: 20px; }
:deep(.el-table) { font-size: 14px; }
</style>
