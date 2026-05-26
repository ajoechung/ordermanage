<template>
  <div class="password-container">
    <div class="password-card">
      <div class="password-header">
        <div class="header-icon">
          <el-icon><Lock /></el-icon>
        </div>
        <h2 class="password-title">修改密码</h2>
        <p class="password-subtitle">请输入您的密码信息进行修改</p>
      </div>

      <el-form
        ref="passwordFormRef"
        :model="passwordForm"
        :rules="passwordRules"
        class="password-form"
      >
        <el-form-item prop="oldPassword">
          <label class="form-label">
            <el-icon><Key /></el-icon>
            旧密码
          </label>
          <el-input
            v-model="passwordForm.oldPassword"
            type="password"
            placeholder="请输入旧密码"
            size="large"
            show-password
            clearable
            autocomplete="off"
          />
        </el-form-item>

        <el-form-item prop="newPassword">
          <label class="form-label">
            <el-icon><Lock /></el-icon>
            新密码
          </label>
          <el-input
            v-model="passwordForm.newPassword"
            type="password"
            placeholder="请输入新密码（至少6位）"
            size="large"
            show-password
            clearable
            autocomplete="off"
          />
        </el-form-item>

        <el-form-item prop="confirmPassword">
          <label class="form-label">
            <el-icon><CircleCheck /></el-icon>
            确认密码
          </label>
          <el-input
            v-model="passwordForm.confirmPassword"
            type="password"
            placeholder="请再次输入新密码"
            size="large"
            show-password
            clearable
            autocomplete="off"
          />
        </el-form-item>

        <el-form-item>
          <el-button
            type="primary"
            :loading="loading"
            size="large"
            class="submit-button"
            @click="handleSubmit"
          >
            {{ loading ? '修改中...' : '确认修改' }}
          </el-button>
        </el-form-item>
      </el-form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { ElMessage } from 'element-plus'
import { Lock, Key, CircleCheck } from '@element-plus/icons-vue'
import { useUserStore } from '@/store/modules/user'

const router = useRouter()
const userStore = useUserStore()

const passwordFormRef = ref(null)
const loading = ref(false)

const passwordForm = reactive({
  oldPassword: '',
  newPassword: '',
  confirmPassword: ''
})

const passwordRules = {
  oldPassword: [
    { required: true, message: '请输入旧密码', trigger: 'blur' },
    { min: 6, message: '密码长度不能少于6位', trigger: 'blur' }
  ],
  newPassword: [
    { required: true, message: '请输入新密码', trigger: 'blur' },
    { min: 6, message: '密码长度不能少于6位', trigger: 'blur' }
  ],
  confirmPassword: [
    { required: true, message: '请确认新密码', trigger: 'blur' },
    {
      validator: (rule, value, callback) => {
        if (value !== passwordForm.newPassword) {
          callback(new Error('两次输入的密码不一致'))
        } else {
          callback()
        }
      },
      trigger: 'blur'
    }
  ]
}

const handleSubmit = async () => {
  if (!passwordFormRef.value) return

  await passwordFormRef.value.validate(async (valid) => {
    if (!valid) return

    loading.value = true
    try {
      const response = await fetch('/api/user/change-password', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer ' + userStore.token
        },
        body: JSON.stringify({
          old_password: passwordForm.oldPassword,
          new_password: passwordForm.newPassword
        })
      })

      const res = await response.json()

      if (res.code === 200) {
        ElMessage.success('密码修改成功，请重新登录')
        userStore.logout()
        localStorage.removeItem('token')
        localStorage.removeItem('userInfo')
        router.push('/login')
      } else {
        ElMessage.error(res.message || '密码修改失败')
      }
    } catch (error) {
      console.error('密码修改失败:', error)
      ElMessage.error(error.response?.data?.message || '密码修改失败，请检查网络连接')
    } finally {
      loading.value = false
    }
  })
}
</script>

<style scoped>
.password-container {
  min-height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 40px 20px;
}

.password-card {
  width: 100%;
  max-width: 450px;
  background: #ffffff;
  border-radius: 16px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
  padding: 48px;
}

.password-header {
  text-align: center;
  margin-bottom: 40px;
}

.header-icon {
  width: 72px;
  height: 72px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 20px;
  box-shadow: 0 8px 24px rgba(102, 126, 234, 0.4);
}

.header-icon .el-icon {
  font-size: 32px;
  color: #ffffff;
}

.password-title {
  font-size: 28px;
  font-weight: 600;
  color: #303133;
  margin: 0 0 12px 0;
}

.password-subtitle {
  font-size: 14px;
  color: #909399;
  margin: 0;
}

.password-form {
  margin-top: 20px;
}

.form-label {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 14px;
  color: #606266;
  margin-bottom: 8px;
}

.form-label .el-icon {
  color: #909399;
}

.submit-button {
  width: 100%;
  height: 48px;
  font-size: 16px;
  border-radius: 8px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border: none;
  transition: all 0.3s ease;
}

.submit-button:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
}

.submit-button:active {
  transform: translateY(0);
}

:deep(.el-input__wrapper) {
  padding: 12px 16px;
  border-radius: 6px;
}

:deep(.el-input__inner) {
  font-size: 15px;
}

:deep(.el-form-item__error) {
  padding-top: 4px;
}

@media (max-width: 480px) {
  .password-card {
    padding: 32px 20px;
  }

  .password-title {
    font-size: 24px;
  }
}
</style>