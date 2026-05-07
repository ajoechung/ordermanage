<template>
  <div class="permission-denied-container">
    <div class="content">
      <div class="icon-wrapper">
        <svg class="icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="currentColor" stroke-width="2"/>
          <path d="M12 8V12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
          <path d="M12 16H12.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
      </div>
      
      <h1 class="title">权限不足</h1>
      <p class="description">抱歉，您没有访问该页面的权限</p>
      <p class="hint">请联系管理员为您分配相应权限</p>
      
      <div class="actions">
        <el-button type="primary" size="large" @click="goBack">
          <el-icon><ArrowLeft /></el-icon>
          返回上一页
        </el-button>
        <el-button size="large" @click="goHome">
          <el-icon><HomeFilled /></el-icon>
          返回首页
        </el-button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useRouter, useRoute } from 'vue-router'
import { ArrowLeft, HomeFilled } from '@element-plus/icons-vue'

const router = useRouter()
const route = useRoute()

const goBack = () => {
  router.back()
}

const goHome = () => {
  router.push('/')
}
</script>

<style scoped>
.permission-denied-container {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 20px;
}

.content {
  text-align: center;
  background: rgba(255, 255, 255, 0.95);
  padding: 60px 80px;
  border-radius: 20px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
  backdrop-filter: blur(10px);
  animation: slideUp 0.5s ease-out;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.icon-wrapper {
  margin-bottom: 30px;
}

.icon {
  width: 100px;
  height: 100px;
  color: #f56c6c;
  animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.05);
  }
}

.title {
  font-size: 36px;
  font-weight: 700;
  color: #303133;
  margin: 0 0 16px 0;
}

.description {
  font-size: 18px;
  color: #606266;
  margin: 0 0 12px 0;
}

.hint {
  font-size: 14px;
  color: #909399;
  margin: 0 0 40px 0;
}

.actions {
  display: flex;
  gap: 16px;
  justify-content: center;
}

.actions :deep(.el-button) {
  padding: 16px 32px;
  font-size: 16px;
  border-radius: 8px;
  transition: all 0.3s ease;
}

.actions :deep(.el-button--primary) {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border: none;
}

.actions :deep(.el-button--primary:hover) {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
}

.actions :deep(.el-button:not(.is-link)) {
  background: #f5f7fa;
  border: none;
  color: #606266;
}

.actions :deep(.el-button:not(.is-link):hover) {
  background: #ebeef5;
  color: #409eff;
  transform: translateY(-2px);
}

@media (max-width: 768px) {
  .content {
    padding: 40px 30px;
  }

  .title {
    font-size: 28px;
  }

  .description {
    font-size: 16px;
  }

  .icon {
    width: 80px;
    height: 80px;
  }

  .actions {
    flex-direction: column;
  }

  .actions :deep(.el-button) {
    width: 100%;
  }
}
</style>
