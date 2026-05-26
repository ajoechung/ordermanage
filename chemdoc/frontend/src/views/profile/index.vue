<template>
  <div class="profile-container">
    <div class="profile-card">
      <div class="avatar-section">
        <el-avatar :size="100" :src="userInfo.avatar || defaultAvatar" />
        <h2>{{ userInfo.realname || userInfo.username }}</h2>
        <p>{{ userInfo.email }}</p>
      </div>
      
      <el-divider />
      
      <el-form :model="form" label-width="100px" class="profile-form">
        <el-form-item label="用户名">
          <el-input v-model="form.username" disabled />
        </el-form-item>
        
        <el-form-item label="真实姓名">
          <el-input v-model="form.realname" />
        </el-form-item>
        
        <el-form-item label="手机号">
          <el-input v-model="form.mobile" />
        </el-form-item>
        
        <el-form-item label="邮箱">
          <el-input v-model="form.email" />
        </el-form-item>
        
        <el-form-item>
          <el-button type="primary" @click="handleUpdate">保存修改</el-button>
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
const userInfo = ref({})
const defaultAvatar = 'https://cube.elemecdn.com/3/7c/3ea6beec64369c2642b92c6726f1epng.png'

const form = reactive({
  username: '',
  realname: '',
  mobile: '',
  email: ''
})

onMounted(() => {
  userInfo.value = userStore.userInfo || {}
  Object.assign(form, {
    username: userInfo.value.username || '',
    realname: userInfo.value.realname || '',
    mobile: userInfo.value.mobile || '',
    email: userInfo.value.email || ''
  })
})

const handleUpdate = async () => {
  try {
    await userStore.updateInfo(form)
    ElMessage.success('更新成功')
  } catch (error) {
    ElMessage.error('更新失败')
  }
}
</script>

<style scoped>
.profile-container {
  padding: 20px;
}

.profile-card {
  max-width: 600px;
  margin: 0 auto;
  background: #fff;
  border-radius: 8px;
  padding: 30px;
  box-shadow: 0 2px 12px 0 rgba(0, 0, 0, 0.1);
}

.avatar-section {
  text-align: center;
  margin-bottom: 30px;
}

.avatar-section h2 {
  margin: 15px 0 5px;
  font-size: 20px;
  color: #303133;
}

.avatar-section p {
  color: #909399;
  font-size: 14px;
}

.profile-form {
  margin-top: 20px;
}
</style>
