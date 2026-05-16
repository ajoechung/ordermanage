<template>
  <div class="supplier-container">
    <el-card class="search-card">
      <el-form :inline="true" :model="searchForm" class="search-form">
        <el-form-item label="供应商名称">
          <el-input v-model="searchForm.name" placeholder="请输入供应商名称" clearable @keyup.enter="handleSearch" style="width: 220px" />
        </el-form-item>
        <el-form-item label="联系人">
          <el-input v-model="searchForm.contact" placeholder="请输入联系人" clearable @keyup.enter="handleSearch" style="width: 220px" />
        </el-form-item>
        <el-form-item label="合作状态">
          <el-select v-model="searchForm.cooperation_status" placeholder="请选择" clearable style="width: 150px">
            <el-option label="已合作" value="active" />
            <el-option label="待合作" value="pending" />
            <el-option label="已终止" value="terminated" />
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
          <span class="table-title">供应商列表</span>
          <el-button type="primary" @click="handleAdd"><Plus />新增供应商</el-button>
        </div>
      </template>

      <el-table v-loading="tableLoading" :data="supplierList" stripe border>
        <el-table-column prop="supplier_id" label="ID" width="80" />
        <el-table-column prop="name" label="供应商名称" min-width="180" show-overflow-tooltip />
        <el-table-column prop="contact" label="联系人" width="100" />
        <el-table-column prop="phone" label="联系电话" width="130" />
        <el-table-column prop="cooperation_status" label="合作状态" width="100">
          <template #default="{ row }">
            <el-tag :type="getStatusType(row.cooperation_status)">{{ getStatusText(row.cooperation_status) }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="address" label="地址" min-width="200" show-overflow-tooltip />
        <el-table-column prop="create_time" label="创建时间" width="160" />
        <el-table-column label="操作" width="180" fixed="right">
          <template #default="{ row }">
            <el-button type="primary" link size="small" @click="handleEdit(row)">编辑</el-button>
            <el-button type="primary" link size="small" @click="handlePurchase(row)">采购</el-button>
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
            <el-form-item label="供应商名称" prop="name">
              <el-input v-model="formData.name" placeholder="请输入供应商名称" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="联系人" prop="contact">
              <el-input v-model="formData.contact" placeholder="请输入联系人" />
            </el-form-item>
          </el-col>
        </el-row>

        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="联系电话" prop="phone">
              <el-input v-model="formData.phone" placeholder="请输入联系电话" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="合作状态" prop="cooperation_status">
              <el-select v-model="formData.cooperation_status" placeholder="请选择" style="width: 100%">
                <el-option label="已合作" value="active" />
                <el-option label="待合作" value="pending" />
                <el-option label="已终止" value="terminated" />
              </el-select>
            </el-form-item>
          </el-col>
        </el-row>

        <el-form-item label="详细地址" prop="address">
          <el-input v-model="formData.address" placeholder="请输入详细地址" />
        </el-form-item>

        <el-form-item label="主营产品" prop="main_products">
          <el-input v-model="formData.main_products" type="textarea" :rows="2" placeholder="请输入主营产品" />
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
import { useRouter } from 'vue-router'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Search, Refresh, Plus } from '@element-plus/icons-vue'
import { getList, create, update, deleteSupplier } from '@/api/modules/supplier'

const router = useRouter()

const searchForm = reactive({ name: '', contact: '', cooperation_status: '' })
const tableLoading = ref(false)
const supplierList = ref([])

const pagination = reactive({ page: 1, pageSize: 20, total: 0 })

const dialogVisible = ref(false)
const dialogTitle = ref('')
const submitLoading = ref(false)
const formRef = ref(null)

const formData = reactive({
  supplier_id: null,
  name: '',
  contact: '',
  phone: '',
  cooperation_status: 'pending',
  address: '',
  main_products: '',
  remark: ''
})

const formRules = {
  name: [{ required: true, message: '请输入供应商名称', trigger: 'blur' }],
  contact: [{ required: true, message: '请输入联系人', trigger: 'blur' }]
}

const statusMap = { active: { text: '已合作', type: 'success' }, pending: { text: '待合作', type: 'warning' }, terminated: { text: '已终止', type: 'danger' } }
const getStatusText = (s) => statusMap[s]?.text || s
const getStatusType = (s) => statusMap[s]?.type || 'info'

const loadData = async () => {
  tableLoading.value = true
  try {
    const params = { page: pagination.page, page_size: pagination.pageSize }
    if (searchForm.name) params.name = searchForm.name
    if (searchForm.contact) params.contact = searchForm.contact
    if (searchForm.cooperation_status) params.cooperation_status = searchForm.cooperation_status

    const res = await getList(params)
    if (res.code === 200) {
      supplierList.value = res.data.list || []
      pagination.total = res.data.total || 0
    }
  } catch (error) {
    console.error('获取供应商列表失败:', error)
    ElMessage.error('获取供应商列表失败')
  } finally {
    tableLoading.value = false
  }
}

const handleSearch = () => { pagination.page = 1; loadData() }
const handleReset = () => { Object.assign(searchForm, { name: '', contact: '', cooperation_status: '' }); pagination.page = 1; loadData() }
const handleAdd = () => { dialogTitle.value = '新增供应商'; dialogVisible.value = true }
const handleEdit = (row) => {
  dialogTitle.value = '编辑供应商'
  Object.keys(formData).forEach(key => { formData[key] = row[key] ?? formData[key] })
  dialogVisible.value = true
}

const handlePurchase = (row) => { router.push({ path: '/purchase/list', query: { supplier_id: row.supplier_id } }) }

const handleDelete = async (row) => {
  try {
    await ElMessageBox.confirm(`确定要删除供应商"${row.name}"吗？`, '提示', { confirmButtonText: '确定', cancelButtonText: '取消', type: 'warning' })
    const res = await deleteSupplier(row.supplier_id)
    if (res.code === 200) { ElMessage.success('删除成功'); loadData() } else { ElMessage.error(res.msg || '删除失败') }
  } catch (error) { if (error !== 'cancel') console.error('删除供应商失败:', error) }
}

const handleSubmit = async () => {
  if (!formRef.value) return
  await formRef.value.validate(async (valid) => {
    if (!valid) return
    submitLoading.value = true
    try {
      const submitData = { ...formData }
      if (submitData.supplier_id) { await update(submitData.supplier_id, submitData) }
      else { delete submitData.supplier_id; await create(submitData) }
      ElMessage.success(submitData.supplier_id ? '编辑成功' : '新增成功')
      dialogVisible.value = false
      loadData()
    } catch (error) { console.error('提交失败:', error); ElMessage.error('操作失败') }
    finally { submitLoading.value = false }
  })
}

const handleDialogClose = () => {
  formRef.value?.resetFields()
  Object.assign(formData, { supplier_id: null, name: '', contact: '', phone: '', cooperation_status: 'pending', address: '', main_products: '', remark: '' })
}

const handleSizeChange = (size) => { pagination.pageSize = size; loadData() }
const handlePageChange = (page) => { pagination.page = page; loadData() }

onMounted(() => { loadData() })
</script>

<style scoped>
.supplier-container { padding: 0; }
.search-card, .table-card { margin-bottom: 20px; }
.table-header { display: flex; justify-content: space-between; align-items: center; }
.table-title { font-size: 16px; font-weight: 600; color: #303133; }
.pagination-container { display: flex; justify-content: flex-end; margin-top: 20px; }
:deep(.el-table) { font-size: 14px; }
</style>
