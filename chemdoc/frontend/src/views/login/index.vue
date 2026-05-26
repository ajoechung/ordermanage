<template>
  <div class="login-page">
    <div class="login-container">
      <div class="login-left">
        <div class="brand-section">
          <div class="brand-logo">
            <svg viewBox="0 0 100 100" class="logo-svg">
              <defs>
                <linearGradient id="logoGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                  <stop offset="0%" style="stop-color:#667eea"/>
                  <stop offset="100%" style="stop-color:#764ba2"/>
                </linearGradient>
              </defs>
              <circle cx="50" cy="50" r="45" fill="url(#logoGradient)"/>
              <path d="M30 50 L45 65 L70 35" stroke="white" stroke-width="6" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </div>
          <h1 class="brand-title">化工单据管理系统</h1>
          <p class="brand-subtitle">Chemical Document Management System</p>
        </div>

        <div class="features-section">
          <div class="feature-item">
            <div class="feature-icon">
              <el-icon><Document /></el-icon>
            </div>
            <div class="feature-text">
              <h3>单据管理</h3>
              <p>完整的订单、采购单据管理流程</p>
            </div>
          </div>

          <div class="feature-item">
            <div class="feature-icon">
              <el-icon><User /></el-icon>
            </div>
            <div class="feature-text">
              <h3>客户管理</h3>
              <p>客户信息、销售跟进一站式管理</p>
            </div>
          </div>

          <div class="feature-item">
            <div class="feature-icon">
              <el-icon><DataAnalysis /></el-icon>
            </div>
            <div class="feature-text">
              <h3>数据分析</h3>
              <p>多维度数据统计，辅助经营决策</p>
            </div>
          </div>
        </div>

        <div class="decoration-circles">
          <div class="circle circle-1"></div>
          <div class="circle circle-2"></div>
          <div class="circle circle-3"></div>
        </div>
      </div>

      <div class="login-right">
        <div class="login-box">
          <div class="login-header">
            <h2 class="login-title">欢迎登录</h2>
            <p class="login-subtitle">请输入您的账号信息</p>
          </div>

          <el-form
            ref="loginFormRef"
            :model="loginForm"
            :rules="loginRules"
            class="login-form"
            @keyup.enter="handleLogin"
          >
            <el-form-item prop="username">
              <div class="form-item-wrapper">
                <label class="form-label">
                  <el-icon><User /></el-icon>
                  用户名
                </label>
                <el-input
                  v-model="loginForm.username"
                  placeholder="请输入用户名"
                  size="large"
                  clearable
                  autocomplete="off"
                />
              </div>
            </el-form-item>

            <el-form-item prop="password">
              <div class="form-item-wrapper">
                <label class="form-label">
                  <el-icon><Lock /></el-icon>
                  密码
                </label>
                <el-input
                  v-model="loginForm.password"
                  type="password"
                  placeholder="请输入密码"
                  size="large"
                  show-password
                  clearable
                  autocomplete="off"
                />
              </div>
            </el-form-item>

            <el-form-item prop="captcha">
              <div class="form-item-wrapper captcha-wrapper">
                <div class="captcha-input-section">
                  <label class="form-label">
                    <el-icon><CircleCheck /></el-icon>
                    验证码
                  </label>
                  <el-input
                    v-model="loginForm.captcha"
                    placeholder="请输入验证码"
                    size="large"
                    clearable
                    maxlength="4"
                  />
                </div>
                <div class="captcha-image" @click="refreshCaptcha" title="点击刷新验证码">
                  <el-icon v-if="captchaLoading" class="is-loading"><Loading /></el-icon>
                  <img v-else-if="captchaData?.image" :src="captchaData.image" alt="验证码" />
                  <span v-else class="captcha-placeholder">
                    <el-icon><RefreshRight /></el-icon>
                  </span>
                </div>
              </div>
            </el-form-item>

            <div class="form-options">
              <el-checkbox v-model="loginForm.remember">
                记住密码
              </el-checkbox>
              <a class="forgot-link" href="javascript:void(0)">忘记密码？</a>
            </div>

            <el-form-item>
              <el-button
                type="primary"
                :loading="loading"
                size="large"
                class="login-button"
                @click="handleLogin"
              >
                {{ loading ? '登录中...' : '登 录' }}
              </el-button>
            </el-form-item>
          </el-form>

          <div class="login-footer">
            <p class="demo-tip">
              <el-icon><InfoFilled /></el-icon>
              演示账号：admin / 123456
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { ElMessage } from 'element-plus'
import { User, Lock, CircleCheck, Loading, RefreshRight, Document, DataAnalysis, InfoFilled } from '@element-plus/icons-vue'
import { login as loginApi } from '@/api/modules/auth'
import { useUserStore } from '@/store/modules/user'

const router = useRouter()
const route = useRoute()
const userStore = useUserStore()

const loginFormRef = ref(null)
const loading = ref(false)
const captchaLoading = ref(false)
const captchaData = ref(null)

const loginForm = reactive({
  username: 'admin',
  password: '123456',
  captcha: '',
  captcha_key: '',
  remember: false
})

const loginRules = {
  username: [
    { required: true, message: '请输入用户名', trigger: 'blur' },
    { min: 3, max: 20, message: '用户名长度为3-20个字符', trigger: 'blur' }
  ],
  password: [
    { required: true, message: '请输入密码', trigger: 'blur' },
    { min: 6, message: '密码长度不能少于6位', trigger: 'blur' }
  ],
  captcha: [
    { required: true, message: '请输入验证码', trigger: 'blur' },
    { len: 4, message: '验证码长度为4位', trigger: 'blur' }
  ]
}

const refreshCaptcha = async () => {
  captchaLoading.value = true
  try {
    const { getCaptcha } = await import('@/api/modules/captcha')
    const res = await getCaptcha()
    if (res.code === 200 && res.data) {
      captchaData.value = res.data
      loginForm.captcha_key = res.data.key || ''
      loginForm.captcha = ''
    }
  } catch (error) {
    console.error('获取验证码失败:', error)
  } finally {
    captchaLoading.value = false
  }
}

const handleLogin = async () => {
  if (!loginFormRef.value) return

  await loginFormRef.value.validate(async (valid) => {
    if (!valid) return

    loading.value = true
    try {
      const loginData = {
        username: loginForm.username,
        password: loginForm.password,
        captcha: loginForm.captcha,
        captcha_key: loginForm.captcha_key
      }

      const res = await loginApi(loginData)

      if (res.code === 200) {
        userStore.token = res.data.token
        userStore.userInfo = res.data.user_info || {}
        userStore.groups = res.data.user_info?.groups || []
        userStore.permissions = res.data.user_info?.permissions || []

        if (loginForm.remember) {
          localStorage.setItem('username', loginForm.username)
        } else {
          localStorage.removeItem('username')
        }

        localStorage.setItem('token', res.data.token)
        localStorage.setItem('userInfo', JSON.stringify(res.data.user_info || {}))
        localStorage.setItem('permissions', JSON.stringify(res.data.user_info?.permissions || []))

        ElMessage.success('登录成功')

        const redirect = route.query.redirect || '/'
        router.push(redirect)
      } else {
        ElMessage.error(res.message || '登录失败')
        refreshCaptcha()
      }
    } catch (error) {
      console.error('登录失败:', error)
      ElMessage.error(error.response?.data?.message || '登录失败，请检查网络连接')
      refreshCaptcha()
    } finally {
      loading.value = false
    }
  })
}

watch(() => loginForm.captcha, (val) => {
  if (val && val.length === 4) {
    loginFormRef.value?.validateField('captcha')
  }
})

onMounted(() => {
  const savedUsername = localStorage.getItem('username')
  if (savedUsername) {
    loginForm.username = savedUsername
    loginForm.remember = true
  }
  refreshCaptcha()
})
</script>

<style scoped>
.login-page {
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.login-container {
  display: flex;
  min-height: 100vh;
}

.login-left {
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: center;
  padding: 60px;
  background: linear-gradient(135deg, rgba(102, 126, 234, 0.9) 0%, rgba(118, 75, 162, 0.9) 100%);
  position: relative;
  overflow: hidden;
}

.brand-section {
  position: relative;
  z-index: 1;
}

.brand-logo {
  margin-bottom: 30px;
}

.logo-svg {
  width: 100px;
  height: 100px;
  filter: drop-shadow(0 10px 20px rgba(0, 0, 0, 0.2));
  animation: float 3s ease-in-out infinite;
}

@keyframes float {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-10px); }
}

.brand-title {
  font-size: 42px;
  font-weight: 700;
  color: #ffffff;
  margin: 0 0 12px 0;
  letter-spacing: 2px;
}

.brand-subtitle {
  font-size: 18px;
  color: rgba(255, 255, 255, 0.8);
  margin: 0;
  letter-spacing: 1px;
}

.features-section {
  margin-top: 60px;
  position: relative;
  z-index: 1;
}

.feature-item {
  display: flex;
  align-items: center;
  margin-bottom: 30px;
  padding: 20px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  backdrop-filter: blur(10px);
  transition: all 0.3s ease;
}

.feature-item:hover {
  background: rgba(255, 255, 255, 0.2);
  transform: translateX(10px);
}

.feature-icon {
  width: 50px;
  height: 50px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 20px;
}

.feature-icon .el-icon {
  font-size: 24px;
  color: #ffffff;
}

.feature-text h3 {
  font-size: 18px;
  color: #ffffff;
  margin: 0 0 6px 0;
}

.feature-text p {
  font-size: 14px;
  color: rgba(255, 255, 255, 0.7);
  margin: 0;
}

.decoration-circles {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  pointer-events: none;
}

.circle {
  position: absolute;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.1);
}

.circle-1 {
  width: 300px;
  height: 300px;
  top: -100px;
  right: -100px;
  animation: pulse 4s ease-in-out infinite;
}

.circle-2 {
  width: 200px;
  height: 200px;
  bottom: 100px;
  right: 200px;
  animation: pulse 4s ease-in-out infinite 1s;
}

.circle-3 {
  width: 150px;
  height: 150px;
  bottom: -50px;
  left: 50%;
  animation: pulse 4s ease-in-out infinite 2s;
}

@keyframes pulse {
  0%, 100% { transform: scale(1); opacity: 0.5; }
  50% { transform: scale(1.1); opacity: 0.8; }
}

.login-right {
  width: 500px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #ffffff;
  padding: 40px;
}

.login-box {
  width: 100%;
  max-width: 380px;
}

.login-header {
  text-align: center;
  margin-bottom: 40px;
}

.login-title {
  font-size: 32px;
  font-weight: 600;
  color: #303133;
  margin: 0 0 12px 0;
}

.login-subtitle {
  font-size: 14px;
  color: #909399;
  margin: 0;
}

.login-form {
  margin-top: 20px;
}

.form-item-wrapper {
  width: 100%;
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

.captcha-wrapper {
  display: flex;
  gap: 16px;
  align-items: flex-end;
}

.captcha-input-section {
  flex: 1;
}

.captcha-image {
  width: 120px;
  height: 40px;
  border: 1px solid #dcdfe6;
  border-radius: 6px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f5f7fa;
  transition: all 0.3s ease;
  overflow: hidden;
}

.captcha-image:hover {
  border-color: #409eff;
  background: #ecf5ff;
}

.captcha-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.captcha-placeholder {
  color: #909399;
}

.form-options {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.forgot-link {
  color: #409eff;
  font-size: 14px;
  text-decoration: none;
  transition: color 0.3s;
}

.forgot-link:hover {
  color: #66b1ff;
}

.login-button {
  width: 100%;
  height: 48px;
  font-size: 16px;
  border-radius: 8px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border: none;
  transition: all 0.3s ease;
}

.login-button:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
}

.login-button:active {
  transform: translateY(0);
}

.login-footer {
  margin-top: 30px;
  text-align: center;
}

.demo-tip {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  font-size: 13px;
  color: #909399;
  margin: 0;
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

@media (max-width: 1024px) {
  .login-left {
    display: none;
  }

  .login-right {
    width: 100%;
  }
}

@media (max-width: 480px) {
  .login-page {
    padding: 20px;
  }

  .login-box {
    padding: 0 10px;
  }

  .brand-title {
    font-size: 28px;
  }
}
</style>
