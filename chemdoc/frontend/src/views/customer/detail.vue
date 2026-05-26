<template>
  <el-drawer
    v-model="visible"
    :title="customerName"
    size="50%"
    direction="rtl"
    :destroy-on-close="true"
    class="customer-detail-drawer"
  >
    <el-tabs v-model="activeTab" class="detail-tabs">
      <el-tab-pane label="基本信息" name="basic">
        <el-card shadow="never">
          <el-descriptions :column="2" border>
            <el-descriptions-item label="客户名称">{{ currentCustomer.name || '-' }}</el-descriptions-item>
            <el-descriptions-item label="客户编码">{{ currentCustomer.code || '-' }}</el-descriptions-item>
            <el-descriptions-item label="行业">{{ currentCustomer.industry || '-' }}</el-descriptions-item>
            <el-descriptions-item label="客户等级">
              <el-tag :type="getLevelTag(currentCustomer.level)" size="small">
                {{ getLevelText(currentCustomer.level) }}
              </el-tag>
            </el-descriptions-item>
            <el-descriptions-item label="客户来源">{{ currentCustomer.source || '-' }}</el-descriptions-item>
            <el-descriptions-item label="客户规模">{{ currentCustomer.scale || '-' }}</el-descriptions-item>
            <el-descriptions-item label="状态">
              <el-tag :type="currentCustomer.status === 1 ? 'success' : 'info'" size="small">
                {{ currentCustomer.status === 1 ? '正常' : '禁用' }}
              </el-tag>
            </el-descriptions-item>
            <el-descriptions-item label="负责人">{{ currentCustomer.owner_name || '-' }}</el-descriptions-item>
            <el-descriptions-item label="详细地址" :span="2">{{ currentCustomer.address || '-' }}</el-descriptions-item>
            <el-descriptions-item label="客户描述" :span="2">{{ currentCustomer.description || '-' }}</el-descriptions-item>
            <el-descriptions-item label="创建时间">{{ currentCustomer.create_time || '-' }}</el-descriptions-item>
            <el-descriptions-item label="更新时间">{{ currentCustomer.update_time || '-' }}</el-descriptions-item>
          </el-descriptions>
        </el-card>
      </el-tab-pane>

      <el-tab-pane label="联系人" name="contact">
        <el-card shadow="never">
          <template #header>
            <div class="tab-header">
              <span>联系人列表</span>
              <el-button type="primary" link @click="handleAddContact">新增联系人</el-button>
            </div>
          </template>
          <el-table :data="contactList" stripe border>
            <el-table-column prop="name" label="姓名" width="120" />
            <el-table-column prop="position" label="职位" width="120" />
            <el-table-column prop="mobile" label="手机号" width="130" />
            <el-table-column prop="email" label="邮箱" min-width="180" />
            <el-table-column prop="wechat" label="微信" width="120" />
            <el-table-column prop="is_default" label="主联系人" width="100">
              <template #default="{ row }">
                <el-tag v-if="row.is_default" type="success" size="small">是</el-tag>
                <el-tag v-else type="info" size="small">否</el-tag>
              </template>
            </el-table-column>
            <el-table-column label="操作" width="160" fixed="right">
              <template #default="{ row }">
                <el-button type="primary" link size="small" @click="handleEditContact(row)">编辑</el-button>
                <el-button type="danger" link size="small" @click="handleDeleteContact(row)">删除</el-button>
              </template>
            </el-table-column>
          </el-table>
        </el-card>
      </el-tab-pane>

      <el-tab-pane label="跟进记录" name="follow">
        <el-card shadow="never">
          <template #header>
            <div class="tab-header">
              <span>跟进记录</span>
              <el-button type="primary" link @click="handleAddFollow">新增跟进</el-button>
            </div>
          </template>
          <el-timeline>
            <el-timeline-item
              v-for="item in followList"
              :key="item.follow_id"
              :timestamp="item.create_time"
              placement="top"
            >
              <el-card>
                <div class="follow-item">
                  <div class="follow-header">
                    <span class="follow-type">{{ getFollowTypeText(item.type) }}</span>
                    <span class="follow-user">{{ item.create_user_name }}</span>
                  </div>
                  <div class="follow-content">{{ item.content }}</div>
                  <div v-if="item.attachment" class="follow-attachment">
                    <el-link type="primary" :href="item.attachment" target="_blank">查看附件</el-link>
                  </div>
                  <div class="follow-actions">
                    <el-button type="primary" link size="small" @click="handleEditFollow(item)">编辑</el-button>
                    <el-button type="danger" link size="small" @click="handleDeleteFollow(item)">删除</el-button>
                  </div>
                </div>
              </el-card>
            </el-timeline-item>
          </el-timeline>
          <el-empty v-if="followList.length === 0" description="暂无跟进记录" />
        </el-card>
      </el-tab-pane>

      <el-tab-pane label="历史订单" name="order">
        <el-card shadow="never">
          <el-table :data="orderList" stripe border>
            <el-table-column prop="order_no" label="订单号" width="160" />
            <el-table-column prop="customer_name" label="客户名称" min-width="150" />
            <el-table-column prop="total_amount" label="订单金额" width="120" align="right">
              <template #default="{ row }">
                ¥{{ Number(row.total_amount || 0).toLocaleString() }}
              </template>
            </el-table-column>
            <el-table-column prop="order_status" label="状态" width="100">
              <template #default="{ row }">
                <el-tag :type="getOrderStatusTag(row.order_status)" size="small">
                  {{ getOrderStatusText(row.order_status) }}
                </el-tag>
              </template>
            </el-table-column>
            <el-table-column prop="create_time" label="创建时间" width="160" />
            <el-table-column label="操作" width="120" fixed="right">
              <template #default="{ row }">
                <el-button type="primary" link size="small" @click="handleViewOrder(row)">查看详情</el-button>
              </template>
            </el-table-column>
          </el-table>
          <el-empty v-if="orderList.length === 0" description="暂无订单" />
        </el-card>
      </el-tab-pane>

      <el-tab-pane label="交易产品" name="product">
        <el-card shadow="never">
          <el-table :data="productList" stripe border>
            <el-table-column prop="product_name" label="产品名称" min-width="180" />
            <el-table-column prop="spec" label="规格" width="150" />
            <el-table-column prop="unit" label="单位" width="80" />
            <el-table-column prop="price" label="单价" width="120" align="right">
              <template #default="{ row }">
                ¥{{ Number(row.price || 0).toLocaleString() }}
              </template>
            </el-table-column>
            <el-table-column prop="quantity" label="交易数量" width="100" align="center" />
            <el-table-column prop="total_amount" label="交易金额" width="120" align="right">
              <template #default="{ row }">
                ¥{{ Number(row.total_amount || 0).toLocaleString() }}
              </template>
            </el-table-column>
            <el-table-column prop="last_trade_time" label="最后交易时间" width="160" />
          </el-table>
          <el-empty v-if="productList.length === 0" description="暂无交易产品" />
        </el-card>
      </el-tab-pane>

      <el-tab-pane label="操作日志" name="log">
        <el-card shadow="never">
          <el-table :data="logList" stripe border>
            <el-table-column prop="action" label="操作类型" width="120" />
            <el-table-column prop="description" label="操作内容" min-width="200" />
            <el-table-column prop="create_user_name" label="操作人" width="120" />
            <el-table-column prop="create_time" label="操作时间" width="160" />
          </el-table>
          <el-empty v-if="logList.length === 0" description="暂无操作日志" />
        </el-card>
      </el-tab-pane>
    </el-tabs>

    <!-- 联系人编辑/新增弹窗 -->
    <el-dialog
      v-model="contactDialogVisible"
      :title="contactDialogTitle"
      width="600px"
      :close-on-click-modal="false"
      @close="handleContactDialogClose"
    >
      <el-form ref="contactFormRef" :model="contactFormData" :rules="contactFormRules" label-width="100px">
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="姓名" prop="name">
              <el-input v-model="contactFormData.name" placeholder="请输入联系人姓名" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="职位" prop="position">
              <el-input v-model="contactFormData.position" placeholder="请输入职位" />
            </el-form-item>
          </el-col>
        </el-row>
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="手机号" prop="mobile">
              <el-input v-model="contactFormData.mobile" placeholder="请输入手机号" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="座机" prop="phone">
              <el-input v-model="contactFormData.phone" placeholder="请输入座机号码" />
            </el-form-item>
          </el-col>
        </el-row>
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="邮箱" prop="email">
              <el-input v-model="contactFormData.email" placeholder="请输入邮箱" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="微信" prop="wechat">
              <el-input v-model="contactFormData.wechat" placeholder="请输入微信号" />
            </el-form-item>
          </el-col>
        </el-row>
        <el-form-item label="性别" prop="gender">
          <el-radio-group v-model="contactFormData.gender">
            <el-radio :label="1">男</el-radio>
            <el-radio :label="0">女</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item label="主联系人" prop="is_default">
          <el-switch v-model="contactFormData.is_default" />
        </el-form-item>
        <el-form-item label="备注" prop="remark">
          <el-input v-model="contactFormData.remark" type="textarea" :rows="3" placeholder="请输入备注" />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="contactDialogVisible = false">取消</el-button>
        <el-button type="primary" :loading="contactSubmitLoading" @click="handleContactSubmit">确定</el-button>
      </template>
    </el-dialog>

    <!-- 跟进记录编辑/新增弹窗 -->
    <el-dialog
      v-model="followDialogVisible"
      :title="followDialogTitle"
      width="600px"
      :close-on-click-modal="false"
      @close="handleFollowDialogClose"
    >
      <el-form ref="followFormRef" :model="followFormData" :rules="followFormRules" label-width="100px">
        <el-form-item label="跟进方式" prop="method">
          <el-select v-model="followFormData.method" placeholder="请选择" style="width: 100%">
            <el-option label="电话" value="电话" />
            <el-option label="拜访" value="拜访" />
            <el-option label="邮件" value="邮件" />
            <el-option label="其他" value="其他" />
          </el-select>
        </el-form-item>
        <el-form-item label="跟进内容" prop="content">
          <el-input v-model="followFormData.content" type="textarea" :rows="5" placeholder="请输入跟进内容" />
        </el-form-item>
        <el-form-item label="下次跟进">
          <el-date-picker v-model="followFormData.next_time" type="datetime" placeholder="选择时间" value-format="YYYY-MM-DD HH:mm:ss" style="width: 100%" />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="followDialogVisible = false">取消</el-button>
        <el-button type="primary" :loading="followSubmitLoading" @click="handleFollowSubmit">确定</el-button>
      </template>
    </el-dialog>

    <!-- 订单详情抽屉 -->
    <order-detail
      v-model="orderDetailVisible"
      :order-id="currentOrderId"
    />
  </el-drawer>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { getCustomerFullDetail } from '@/api/modules/customer'
import { createContact, updateContact, deleteContact } from '@/api/modules/contact'
import { createFollow, deleteFollow } from '@/api/modules/follow'
import OrderDetail from '../order/detail.vue'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  customerId: {
    type: [Number, String],
    default: null
  }
})

const emit = defineEmits(['update:modelValue'])

const visible = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val)
})

const activeTab = ref('basic')
const currentCustomer = ref({})
const contactList = ref([])
const followList = ref([])
const orderList = ref([])
const productList = ref([])
const logList = ref([])

// 联系人相关
const contactDialogVisible = ref(false)
const contactDialogTitle = ref('')
const contactFormRef = ref(null)
const contactSubmitLoading = ref(false)
const contactFormData = ref({
  contact_id: null,
  customer_id: null,
  name: '',
  position: '',
  mobile: '',
  phone: '',
  email: '',
  wechat: '',
  gender: 1,
  is_default: false,
  remark: ''
})

const contactFormRules = {
  name: [{ required: true, message: '请输入联系人姓名', trigger: 'blur' }],
  mobile: [{ required: true, message: '请输入手机号', trigger: 'blur' }]
}

// 跟进记录相关
const followDialogVisible = ref(false)
const followDialogTitle = ref('')
const followFormRef = ref(null)
const followSubmitLoading = ref(false)
const followFormData = ref({
  follow_id: null,
  customer_id: null,
  method: '',
  content: '',
  next_time: ''
})

const followFormRules = {
  method: [{ required: true, message: '请选择跟进方式', trigger: 'change' }],
  content: [{ required: true, message: '请输入跟进内容', trigger: 'blur' }]
}

// 订单详情相关
const orderDetailVisible = ref(false)
const currentOrderId = ref(null)

// 监听customerId变化，当customerId改变且抽屉开时加载数据
watch(() => props.customerId, async (newVal) => {
  if (newVal && visible.value) {
    await loadCustomerDetail(newVal)
  }
})

// 监听visible变化
watch(visible, async (newVal) => {
  if (newVal && props.customerId) {
    await loadCustomerDetail(props.customerId)
  } else if (!newVal) {
    currentCustomer.value = {}
    contactList.value = []
    followList.value = []
    orderList.value = []
    productList.value = []
    logList.value = []
    activeTab.value = 'basic'
  }
})

const customerName = computed(() => currentCustomer.value.name || '客户详情')

const getLevelText = (level) => ({ 1: '普通', 2: '重要', 3: '核心' }[level] || '未知')
const getLevelTag = (level) => ({ 1: '', 2: 'warning', 3: 'danger' }[level] || 'info')

const getFollowTypeText = (type) => {
  const typeMap = { 1: '电话', 2: '微信', 3: '邮件', 4: '面谈', 5: '其他' }
  return typeMap[type] || '其他'
}

const getOrderStatusText = (status) => {
  const statusMap = { 0: '草稿', 1: '待审核', 2: '已审核', 3: '已发货', 4: '已完成', 5: '已取消' }
  return statusMap[status] || '未知'
}

const getOrderStatusTag = (status) => {
  const tagMap = { 0: 'info', 1: 'warning', 2: 'primary', 3: 'success', 4: 'success', 5: 'danger' }
  return tagMap[status] || 'info'
}

const loadCustomerDetail = async (id) => {
  try {
    console.log('loadCustomerDetail called with id:', id)
    const res = await getCustomerFullDetail(id)
    console.log('API response:', res)
    if (res.code === 200) {
      const data = res.data
      console.log('Customer data:', data)
      
      currentCustomer.value = {
        customer_id: data.customer_id,
        name: data.name || '',
        code: data.code || '',
        industry: data.industry || '',
        level: data.level || 1,
        source: data.source || '',
        scale: data.scale || '',
        address: data.address || '',
        description: data.description || '',
        status: data.status || 1,
        owner_name: data.owner_name || '',
        create_time: data.create_time || '',
        update_time: data.update_time || ''
      }

      contactList.value = data.contacts || []
      followList.value = data.follows || []
      orderList.value = data.orders || []
      productList.value = data.products || []
      logList.value = data.logs || []
    } else {
      ElMessage.error(res.msg || '获取客户详情失败')
    }
  } catch (error) {
    console.error('获取客户详情失败:', error)
    ElMessage.error('获取客户详情失败')
  }
}

// 联系人操作
const handleAddContact = () => {
  contactDialogTitle.value = '新增联系人'
  Object.assign(contactFormData.value, {
    contact_id: null,
    customer_id: props.customerId,
    name: '',
    position: '',
    mobile: '',
    phone: '',
    email: '',
    wechat: '',
    gender: 1,
    is_default: false,
    remark: ''
  })
  contactDialogVisible.value = true
}

const handleEditContact = (row) => {
  contactDialogTitle.value = '编辑联系人'
  Object.assign(contactFormData.value, {
    contact_id: row.contact_id,
    customer_id: props.customerId,
    name: row.name || '',
    position: row.position || '',
    mobile: row.mobile || '',
    phone: row.phone || '',
    email: row.email || '',
    wechat: row.wechat || '',
    gender: row.gender ?? 1,
    is_default: !!row.is_default,
    remark: row.remark || ''
  })
  contactDialogVisible.value = true
}

const handleDeleteContact = async (row) => {
  try {
    await ElMessageBox.confirm(`确定要删除联系人"${row.name}"吗？`, '提示', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    })

    const res = await deleteContact(row.contact_id)
    if (res.code === 200) {
      ElMessage.success('删除成功')
      loadCustomerDetail(props.customerId)
    } else {
      ElMessage.error(res.msg || '删除失败')
    }
  } catch (error) {
    if (error !== 'cancel') {
      console.error('删除联系人失败:', error)
    }
  }
}

const handleContactSubmit = async () => {
  if (!contactFormRef.value) return

  await contactFormRef.value.validate(async (valid) => {
    if (!valid) return

    contactSubmitLoading.value = true
    try {
      if (contactFormData.value.contact_id) {
        await updateContact(contactFormData.value.contact_id, contactFormData.value)
        ElMessage.success('编辑成功')
      } else {
        await createContact(contactFormData.value)
        ElMessage.success('新增成功')
      }
      contactDialogVisible.value = false
      loadCustomerDetail(props.customerId)
    } catch (error) {
      console.error('提交失败:', error)
      ElMessage.error('操作失败')
    } finally {
      contactSubmitLoading.value = false
    }
  })
}

const handleContactDialogClose = () => {
  contactFormRef.value?.resetFields()
  Object.assign(contactFormData.value, {
    contact_id: null,
    customer_id: null,
    name: '',
    position: '',
    mobile: '',
    phone: '',
    email: '',
    wechat: '',
    gender: 1,
    is_default: false,
    remark: ''
  })
}

// 跟进记录操作
const handleAddFollow = () => {
  followDialogTitle.value = '新增跟进'
  Object.assign(followFormData.value, {
    follow_id: null,
    customer_id: props.customerId,
    method: '',
    content: '',
    next_time: ''
  })
  followDialogVisible.value = true
}

const handleEditFollow = (row) => {
  followDialogTitle.value = '编辑跟进'
  Object.assign(followFormData.value, {
    follow_id: row.follow_id,
    customer_id: props.customerId,
    method: row.method || '',
    content: row.content || '',
    next_time: row.next_time || ''
  })
  followDialogVisible.value = true
}

const handleDeleteFollow = async (row) => {
  try {
    await ElMessageBox.confirm('确定要删除该跟进记录吗？', '提示', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    })

    const res = await deleteFollow(row.follow_id)
    if (res.code === 200) {
      ElMessage.success('删除成功')
      loadCustomerDetail(props.customerId)
    } else {
      ElMessage.error(res.msg || '删除失败')
    }
  } catch (error) {
    if (error !== 'cancel') {
      console.error('删除跟进记录失败:', error)
    }
  }
}

const handleFollowSubmit = async () => {
  if (!followFormRef.value) return

  await followFormRef.value.validate(async (valid) => {
    if (!valid) return

    followSubmitLoading.value = true
    try {
      await createFollow(followFormData.value)
      ElMessage.success(followFormData.value.follow_id ? '编辑成功' : '新增成功')
      followDialogVisible.value = false
      loadCustomerDetail(props.customerId)
    } catch (error) {
      console.error('提交失败:', error)
      ElMessage.error('操作失败')
    } finally {
      followSubmitLoading.value = false
    }
  })
}

const handleFollowDialogClose = () => {
  followFormRef.value?.resetFields()
  Object.assign(followFormData.value, {
    follow_id: null,
    customer_id: null,
    method: '',
    content: '',
    next_time: ''
  })
}

// 订单操作
const handleViewOrder = (row) => {
  currentOrderId.value = row.order_id
  orderDetailVisible.value = true
}

onMounted(() => {
  if (visible.value && props.customerId) {
    loadCustomerDetail(props.customerId)
  }
})

defineExpose({
  loadCustomerDetail
})
</script>

<style scoped>
.detail-tabs {
  height: 100%;
}

.detail-tabs :deep(.el-tabs__content) {
  padding-top: 20px;
}

.tab-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.follow-item {
  padding: 8px 0;
}

.follow-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 8px;
}

.follow-type {
  font-weight: 600;
  color: #409eff;
}

.follow-user {
  color: #909399;
  font-size: 14px;
}

.follow-content {
  color: #303133;
  line-height: 1.6;
  margin-bottom: 8px;
}

.follow-attachment {
  margin-top: 8px;
}

.follow-actions {
  margin-top: 12px;
  padding-top: 12px;
  border-top: 1px solid #ebeef5;
}
</style>
