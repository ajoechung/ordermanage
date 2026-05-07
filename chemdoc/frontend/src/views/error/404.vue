<template>
  <div class="error-container">
    <div class="error-content">
      <h1 class="error-code">404</h1>
      <p class="error-message">抱歉，您访问的页面不存在</p>
      <p class="error-description">
        请检查URL是否正确，或点击下方按钮返回首页
      </p>
      <el-button type="primary" size="large" @click="goHome">
        返回首页
      </el-button>
    </div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'

const router = useRouter()

const goHome = () => {
  router.push('/')
}
</script>

<style scoped>
.error-container {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.error-content {
  text-align: center;
  color: #ffffff;
}

.error-code {
  font-size: 120px;
  font-weight: 700;
  margin: 0;
  text-shadow: 2px 2px 10px rgba(0, 0, 0, 0.2);
}

.error-message {
  font-size: 32px;
  margin: 20px 0;
  font-weight: 600;
}

.error-description {
  font-size: 16px;
  margin-bottom: 30px;
  opacity: 0.9;
}
</style>
