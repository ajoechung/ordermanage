<template>
  <div class="purchase-container">
    <el-card class="search-card">
      <el-form :inline="true" :model="searchForm">
        <el-form-item label="采购单号">
          <el-input v-model="searchForm.order_no" placeholder="请输入采购单号" clearable @keyup.enter="handleSearch" />
        </el-form-item>
        <el-form-item label="供应商">
          <el-select v-model="searchForm.supplier_id" placeholder="请选择" clearable filterable>
            <el-option v-for="s in supplierList" :key="s.supplier_id" :label="s.name" :value="s.supplier_id" />
          </el-select>
        </el-form-item>
        <el-form-item label="状态">
          <el-select v-model="searchForm.status" placeholder="请选择" clearable>
            <el-option label="草稿" value="draft" />
            <el-option label="待审核" value="pending" />
            <el-option label="已审核" value="approved" />
            <el-option label="已完成" value="completed" />
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
          <span class="table-title">采购单列表</span>
          <el-button type="primary" @click="handleAdd"><Plus />新增采购单</el-button>
        </div>
      </template>

      <el-table v-loading="tableLoading" :data="purchaseList" stripe border>
        <el-table-column prop="purchase_id" label="ID" width="80" />
        <el-table-column prop="order_no" label="采购单号" width="160" />
        <el-table-column prop="supplier_name" label="供应商" min-width="180" show-overflow-tooltip />
        <el-table-column prop="contact" label="联系人" width="100" />
        <el-table-column prop="total_amount" label="金额" width="120" align="right">
          <template #default="{ row }"><span class="amount">¥{{ Number(row.total_amount || 0).toLocaleString() }}</span></template>
        </el-table-column>
        <el-table-column prop="status" label="状态" width="100">
          <template #default="{ row }"><el-tag :type="getStatusType(row.status)">{{ getStatusText(row.status) }}</el-tag></template>
        </el-table-column>
        <el-table-column prop="create_time" label="创建时间" width="160" />
        <el-table-column label="操作" width="180" fixed="right">
          <template #default="{ row }">
            <el-button type="primary" link size="small" @click="handleView(row)">查看</el-button>
            <el-button v-if="row.status === 'draft'" type="primary" link size="small" @click="handleEdit(row)">编辑</el-button>
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

    <el-dialog v-model="dialogVisible" :title="dialogTitle" width="800px" :close-on-click-modal="false" @close="handleDialogClose">
      <el-form ref="formRef" :model="formData" :rules="formRules" label-width="100px">
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="供应商" prop="supplier_id">
              <el-select v-model="formData.supplier_id" filterable placeholder="请选择" style="width: 100%" @change="handleSupplierChange">
                <el-option v-for="s in supplierList" :key="s.supplier_id" :label="s.name" :value="s.supplier_id" />
              </el-select>
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
            <el-form-item label="联系电话" prop="phone"><el-input v-model="formData.phone" placeholder="请输入电话" /></el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="预计到货">
              <el-date-picker v-model="formData.expected_date" type="date" value-format="YYYY-MM-DD" style="width: 100%" />
            </el-form-item>
          </el-col>
        </el-row>
        <el-form-item label="备注"><el-input v-model="formData.remark" type="textarea" :rows="3" placeholder="请输入备注" /></el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" :loading="submitLoading" @click="handleSubmit">确定</el-button>
      </template>
    </el-dialog>

    <el-dialog v-model="detailVisible" title="采购单详情" width="700px">
      <el-descriptions :column="2" border>
        <el-descriptions-item label="采购单号">{{ currentPurchase.order_no }}</el-descriptions-item>
        <el-descriptions-item label="状态"><el-tag :type="getStatusType(currentPurchase.status)">{{ getStatusText(currentPurchase.status) }}</el-tag></el-descriptions-item>
        <el-descriptions-item label="供应商">{{ currentPurchase.supplier_name }}</el-descriptions-item>
        <el-descriptions-item label="联系人">{{ currentPurchase.contact }}</el-descriptions-item>
        <el-descriptions-item label="联系电话">{{ currentPurchase.phone }}</el-descriptions-item>
        <el-descriptions-item label="预计到货">{{ currentPurchase.expected_date }}</el-descriptions-item>
        <el-descriptions-item label="采购总额" :span="2"><span class="amount">¥{{ Number(currentPurchase.total_amount || 0).toFixed(2) }}</span></el-descriptions-item>
        <el-descriptions-item label="备注" :span="2">{{ currentPurchase.remark }}</el-descriptions-item>
      </el-descriptions>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Search, Refresh, Plus } from '@element-plus/icons-vue'
import { getPurchaseList, createPurchase, updatePurchase, deletePurchase } from '@/api/modules/supplier'
import { getList as getSupplierList } from '@/api/modules/supplier'

const route = useRoute()

const searchForm = reactive({ order_no: '', supplier_id: '', status: '' })
const tableLoading = ref(false)
const purchaseList = ref([])
const supplierList = ref([])

const pagination = reactive({ page: 1, pageSize: 20, total: 0 })

const dialogVisible = ref(false)
const detailVisible = ref(false)
const dialogTitle = ref('')
const submitLoading = ref(false)
const formRef = ref(null)
const currentPurchase = ref({})

const formData = reactive({ purchase_id: null, supplier_id: null, supplier_name: '', contact: '', phone: '', expected_date: '', remark: '' })

const formRules = {
  supplier_id: [{ required: true, message: '请选择供应商', trigger: 'change' }],
  contact: [{ required: true, message: '请输入联系人', trigger: 'blur' }]
}

const statusMap = { draft: { text: '草稿', type: 'info' }, pending: { text: '待审核', type: 'warning' }, approved: { text: '已审核', type: 'success' }, completed: { text: '已完成', type: 'success' }, cancelled: { text: '已取消', type: 'danger' } }
const getStatusText = (s) => statusMap[s]?.text || s
const getStatusType = (s) => statusMap[s]?.type || 'info'

const loadData = async () => {
  tableLoading.value = true
  try {
    const params = { page: pagination.page, page_size: pagination.pageSize }
    if (searchForm.order_no) params.order_no = searchForm.order_no
    if (searchForm.supplier_id) params.supplier_id = searchForm.supplier_id
    if (searchForm.status) params.status = searchForm.status

    const res = await getPurchaseList(params)
    if (res.code === 200) { purchaseList.value = res.data.list || []; pagination.total = res.data.total || 0 }
  } catch (error) { console.error('获取采购单列表失败:', error); ElMessage.error('获取采购单列表失败') }
  finally { tableLoading.value = false }
}

const loadSuppliers = async () => {
  try { const res = await getSupplierList(); if (res.code === 200) supplierList.value = res.data || [] }
  catch (error) { console.error('获取供应商列表失败:', error) }
}

const handleSearch = () => { pagination.page = 1; loadData() }
const handleReset = () => { Object.assign(searchForm, { order_no: '', supplier_id: '', status: '' }); pagination.page = 1; loadData() }
const handleAdd = () => { dialogTitle.value = '新增采购单'; dialogVisible.value = true }
const handleEdit = (row) => {
  dialogTitle.value = '编辑采购单'
  Object.keys(formData).forEach(key => { formData[key] = row[key] ?? formData[key] })
  dialogVisible.value = true
}
const handleView = (row) => { currentPurchase.value = row; detailVisible.value = true }

const handleDelete = async (row) => {
  try {
    await ElMessageBox.confirm(`确定要删除采购单"${row.order_no}"吗？`, '提示', { confirmButtonText: '确定', cancelButtonText: '取消', type: 'warning' })
    const res = await deletePurchase(row.purchase_id)
    if (res.code === 200) { ElMessage.success('删除成功'); loadData() }
  } catch (error) { if (error !== 'cancel') console.error('删除失败:', error) }
}

const handleSupplierChange = (id) => {
  const s = supplierList.value.find(x => x.supplier_id === id)
  if (s) { formData.supplier_name = s.name; formData.contact = s.contact || ''; formData.phone = s.phone || '' }
}

const handleSubmit = async () => {
  if (!formRef.value) return
  await formRef.value.validate(async (valid) => {
    if (!valid) return
    submitLoading.value = true
    try {
      const submitData = { ...formData }
      if (submitData.purchase_id) { await updatePurchase(submitData.purchase_id, submitData) }
      else { delete submitData.purchase_id; await createPurchase(submitData) }
      ElMessage.success(submitData.purchase_id ? '编辑成功' : '新增成功')
      dialogVisible.value = false
      loadData()
    } catch (error) { console.error('提交失败:', error); ElMessage.error('操作失败') }
    finally { submitLoading.value = false }
  })
}

const handleDialogClose = () => {
  formRef.value?.resetFields()
  Object.assign(formData, { purchase_id: null, supplier_id: null, supplier_name: '', contact: '', phone: '', expected_date: '', remark: '' })
}

const handleSizeChange = (size) => { pagination.pageSize = size; loadData() }
const handlePageChange = (page) => { pagination.page = page; loadData() }

onMounted(() => {
  loadSuppliers()
  if (route.query.supplier_id) searchForm.supplier_id = Number(route.query.supplier_id)
  loadData()
})
</script>

<style scoped>
.purchase-container { padding: 0; }
.search-card, .table-card { margin-bottom: 20px; }
.table-header { display: flex; justify-content: space-between; align-items: center; }
.table-title { font-size: 16px; font-weight: 600; color: #303133; }
.pagination-container { display: flex; justify-content: flex-end; margin-top: 20px; }
.amount { color: #f56c6c; font-weight: 600; }
:deep(.el-table) { font-size: 14px; }
</style>
