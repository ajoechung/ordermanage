<template>
  <div class="profile-container">
    <div class="profile-card">
      <div class="profile-header">
        <div class="avatar-section">
          <el-avatar :size="120" :src="userInfo.avatar">
            {{ userInfo.nickname?.charAt(0) || userInfo.username?.charAt(0) || 'U' }}
          </el-avatar>
          <h2 class="profile-name">{{ userInfo.nickname || userInfo.username }}</h2>
          <p class="profile-role">{{ userInfo.groups?.join(', ') || '普通用户' }}</p>
        </div>
      </div>

      <el-form :model="userForm" class="profile-form">
        <el-row :gutter="24">
          <el-col :span="12">
            <el-form-item label="用户名">
              <el-input v-model="userForm.username" disabled />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="真实姓名">
              <el-input v-model="userForm.realName" />
            </el-form-item>
          </el-col>
        </el-row>

        <el-row :gutter="24">
          <el-col :span="12">
            <el-form-item label="手机号码">
              <el-input v-model="userForm.phone" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="邮箱">
              <el-input v-model="userForm.email" />
            </el-form-item>
          </el-col>
        </el-row>

        <el-form-item label="备注">
          <el-input v-model="userForm.remark" type="textarea" :rows="3" />
        </el-form-item>

        <el-form-item label="最后登录时间">
          <el-input v-model="userForm.lastLoginTime" disabled />
        </el-form-item>

        <el-form-item>
          <el-button
            type="primary"
            :loading="loading"
            class="submit-button"
            @click="handleSubmit"
          >
            {{ loading ? '保存中...' : '保存修改' }}
          </el-button>
          <el-button class="cancel-button" @click="handleCancel">
            取消
          </el-button>
        </el-form-item>
      </el-form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { useUserStore } from '@/store/modules/user'

const userStore = useUserStore()
const loading = ref(false)

const userForm = reactive({
  username: '',
  realName: '',
  phone: '',
  email: '',
  remark: '',
  lastLoginTime: ''
})

const userInfo = ref({})

onMounted(() => {
  userInfo.value = userStore.userInfo || {}
  userForm.username = userStore.username
  userForm.realName = userStore.userInfo?.realname || userStore.userInfo?.nickname || ''
  userForm.phone = userStore.userInfo?.mobile || userStore.userInfo?.phone || ''
  userForm.email = userStore.userInfo?.email || ''
  userForm.remark = userStore.userInfo?.remark || ''
  userForm.lastLoginTime = userStore.userInfo?.last_login_time || ''
})

const handleSubmit = async () => {
  loading.value = true
  try {
    const response = await fetch('/api/login/updateInfo', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': 'Bearer ' + userStore.token
      },
      body: JSON.stringify({
        realName: userForm.realName,
        mobile: userForm.phone,
        email: userForm.email,
        remark: userForm.remark
      })
    })

    const res = await response.json()

    if (res.code === 200) {
      ElMessage.success('信息修改成功')
      await userStore.getInfo()
    } else {
      ElMessage.error(res.msg || '修改失败')
    }
  } catch (error) {
    console.error('修改失败:', error)
    ElMessage.error('修改失败，请检查网络连接')
  } finally {
    loading.value = false
  }
}

const handleCancel = () => {
  userForm.realName = userStore.userInfo?.realname || userStore.userInfo?.nickname || ''
  userForm.phone = userStore.userInfo?.mobile || userStore.userInfo?.phone || ''
  userForm.email = userStore.userInfo?.email || ''
  userForm.remark = userStore.userInfo?.remark || ''
}
</script>

<style scoped>
.profile-container {
  min-height: 100%;
  display: flex;
  align-items: flex-start;
  justify-content: center;
  padding: 40px 20px;
}

.profile-card {
  width: 100%;
  max-width: 600px;
  background: #ffffff;
  border-radius: 16px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
  padding: 40px;
}

.profile-header {
  text-align: center;
  padding-bottom: 32px;
  border-bottom: 1px solid #f0f0f0;
  margin-bottom: 32px;
}

.avatar-section {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.avatar-section :deep(.el-avatar) {
  margin-bottom: 16px;
  border: 4px solid #f0f0f0;
}

.profile-name {
  font-size: 24px;
  font-weight: 600;
  color: #303133;
  margin: 0 0 8px 0;
}

.profile-role {
  font-size: 14px;
  color: #909399;
  margin: 0;
}

.profile-form {
  margin-top: 16px;
}

.submit-button {
  margin-right: 16px;
  width: 120px;
}

.cancel-button {
  width: 120px;
}

:deep(.el-form-item__label) {
  font-weight: 500;
}

@media (max-width: 480px) {
  .profile-card {
    padding: 24px;
  }

  .profile-name {
    font-size: 20px;
  }
}
</style>