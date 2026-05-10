<template>
  <div class="app-wrapper">
    <el-container class="app-container">
      <el-aside
        :width="isCollapse ? '64px' : '220px'"
        class="sidebar-container"
      >
        <div class="logo-container">
          <img v-if="!isCollapse" src="@/assets/logo.png" alt="logo" class="logo" />
          <span v-if="!isCollapse" class="logo-title">化工单据</span>
          <el-icon v-else class="collapse-icon"><Box /></el-icon>
        </div>
        
        <el-menu
          :default-active="activeMenu"
          :collapse="isCollapse"
          :collapse-transition="false"
          router
          class="sidebar-menu"
          background-color="#304156"
          text-color="#bfcbd9"
          active-text-color="#409eff"
        >
          <template v-for="menuItem in menuItems" :key="menuItem.key">
            <el-sub-menu v-if="menuItem.type === 'submenu'" :index="menuItem.path">
              <template #title>
                <el-icon><component :is="getIcon(menuItem.icon)" /></el-icon>
                <span>{{ menuItem.title }}</span>
              </template>
              <el-menu-item
                v-for="child in menuItem.children"
                :key="child.path"
                :index="child.path"
              >
                {{ child.title }}
              </el-menu-item>
            </el-sub-menu>
            
            <el-menu-item v-else :index="menuItem.path">
              <el-icon><component :is="getIcon(menuItem.icon)" /></el-icon>
              <span>{{ menuItem.title }}</span>
            </el-menu-item>
          </template>
        </el-menu>
      </el-aside>
      
      <el-container>
        <el-header class="header-container">
          <div class="header-left">
            <el-icon class="collapse-btn" @click="toggleCollapse">
              <Fold v-if="!isCollapse" />
              <Expand v-else />
            </el-icon>
            
            <el-breadcrumb separator="/">
              <el-breadcrumb-item :to="{ path: '/' }">首页</el-breadcrumb-item>
              <el-breadcrumb-item v-for="item in breadcrumb" :key="item.path">
                {{ item.meta?.title }}
              </el-breadcrumb-item>
            </el-breadcrumb>
          </div>
          
          <div class="header-right">
            <el-dropdown @command="handleCommand" trigger="click">
              <div class="user-info">
                <el-avatar :size="32" :src="userInfo.avatar">
                  {{ userInfo.nickname?.charAt(0) || 'U' }}
                </el-avatar>
                <span class="username">{{ userInfo.nickname || userInfo.username }}</span>
                <el-icon class="el-icon--right"><ArrowDown /></el-icon>
              </div>
              <template #dropdown>
                <el-dropdown-menu>
                  <el-dropdown-item command="profile">
                    <el-icon><User /></el-icon>
                    个人中心
                  </el-dropdown-item>
                  <el-dropdown-item command="password">
                    <el-icon><Lock /></el-icon>
                    修改密码
                  </el-dropdown-item>
                  <el-dropdown-item divided command="logout">
                    <el-icon><SwitchButton /></el-icon>
                    退出登录
                  </el-dropdown-item>
                </el-dropdown-menu>
              </template>
            </el-dropdown>
          </div>
        </el-header>
        
        <el-main class="main-container">
          <router-view v-slot="{ Component }">
            <keep-alive :include="keepAliveRoutes">
              <component :is="Component" />
            </keep-alive>
          </router-view>
        </el-main>
      </el-container>
    </el-container>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, markRaw } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { ElMessageBox, ElMessage } from 'element-plus'
import { Fold, Expand, Box, User, Lock, ArrowDown, SwitchButton, HomeFilled, OfficeBuilding, Goods, ShoppingCart, Document, DataAnalysis, Setting, Operation, Menu } from '@element-plus/icons-vue'
import { useUserStore } from '@/store/modules/user'
import { usePermissionStore } from '@/store/modules/permission'
import { logout } from '@/api/modules/auth'
import { constantRoutes } from '@/router/index.js'

const router = useRouter()
const route = useRoute()
const userStore = useUserStore()
const permissionStore = usePermissionStore()

const isCollapse = ref(false)
const keepAliveRoutes = ['CustomerList', 'OrderList', 'ProductList', 'SupplierList', 'ContactList']

const userInfo = computed(() => userStore.userInfo || {})

const iconComponents = {
  'HomeFilled': markRaw(HomeFilled),
  'OfficeBuilding': markRaw(OfficeBuilding),
  'Goods': markRaw(Goods),
  'Box': markRaw(ShoppingCart),
  'Document': markRaw(Document),
  'ShoppingCart': markRaw(ShoppingCart),
  'DataAnalysis': markRaw(DataAnalysis),
  'Setting': markRaw(Setting),
  'Operation': markRaw(Operation),
  'Menu': markRaw(Menu)
}

const getIcon = (iconName) => {
  return iconComponents[iconName] || Menu
}

const menuItems = computed(() => {
  const items = []
  const routes = permissionStore.routes.length > 0 ? permissionStore.routes : constantRoutes
  
  for (const route of routes) {
    if (route.meta?.hidden || ['/login', '/404', '/403'].includes(route.path)) {
      continue
    }
    
    if (route.path === '/') {
      if (route.children && route.children.length > 0) {
        const child = route.children[0]
        items.push({
          key: child.path,
          type: 'menu',
          path: '/' + child.path,
          title: child.meta?.title || '首页',
          icon: child.meta?.icon || 'HomeFilled'
        })
      }
    } else if (route.children && route.children.length > 1) {
      const children = route.children.map(child => ({
        path: route.path + '/' + child.path,
        title: child.meta?.title || child.path
      }))
      items.push({
        key: route.path,
        type: 'submenu',
        path: route.path,
        title: route.meta?.title || route.path,
        icon: route.meta?.icon || 'Menu',
        children: children
      })
    } else if (route.children && route.children.length === 1) {
      const child = route.children[0]
      items.push({
        key: route.path,
        type: 'menu',
        path: route.path + '/' + child.path,
        title: route.meta?.title || child.meta?.title || route.path,
        icon: route.meta?.icon || 'Menu'
      })
    } else {
      items.push({
        key: route.path,
        type: 'menu',
        path: route.path,
        title: route.meta?.title || route.path,
        icon: route.meta?.icon || 'Menu'
      })
    }
  }
  
  return items
})

onMounted(async () => {
  if (permissionStore.routes.length === 0) {
    const groups = userStore.groups || []
    await permissionStore.generateRoutes(groups)
  }
})

const activeMenu = computed(() => {
  const { path } = route
  return path
})

const breadcrumb = computed(() => {
  return route.matched.filter(item => item.meta?.title && item.path !== '/').slice(1)
})

const toggleCollapse = () => {
  isCollapse.value = !isCollapse.value
}

const handleCommand = async (command) => {
  switch (command) {
    case 'profile':
      router.push('/profile')
      break
    case 'password':
      router.push('/password')
      break
    case 'logout':
      try {
        await ElMessageBox.confirm('确定要退出登录吗？', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        })
        
        await logout()
        userStore.logout()
        ElMessage.success('已退出登录')
        router.push('/login')
      } catch (error) {
        if (error !== 'cancel') {
          userStore.logout()
          router.push('/login')
        }
      }
      break
  }
}

watch(() => route.path, () => {
  if (route.path === '/login') {
    userStore.logout()
  }
})
</script>

<style scoped>
.app-wrapper {
  width: 100%;
  height: 100%;
}

.app-container {
  width: 100%;
  height: 100%;
}

.sidebar-container {
  background-color: #304156;
  transition: width 0.3s;
  overflow: hidden;
}

.logo-container {
  height: 60px;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #2b3a4a;
  padding: 0 16px;
}

.logo {
  width: 32px;
  height: 32px;
  margin-right: 8px;
}

.logo-title {
  font-size: 18px;
  font-weight: 600;
  color: #ffffff;
  white-space: nowrap;
}

.collapse-icon {
  font-size: 24px;
  color: #ffffff;
}

.sidebar-menu {
  border-right: none;
  height: calc(100vh - 60px);
  overflow-y: auto;
}

.sidebar-menu:not(.el-menu--collapse) {
  width: 220px;
}

.header-container {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 20px;
  background-color: #ffffff;
  box-shadow: 0 1px 4px rgba(0, 21, 41, 0.08);
}

.header-left {
  display: flex;
  align-items: center;
  gap: 16px;
}

.collapse-btn {
  font-size: 20px;
  cursor: pointer;
  color: #606266;
}

.collapse-btn:hover {
  color: #409eff;
}

.header-right {
  display: flex;
  align-items: center;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  padding: 8px 12px;
  border-radius: 4px;
  transition: background-color 0.3s;
}

.user-info:hover {
  background-color: #f5f7fa;
}

.username {
  font-size: 14px;
  color: #606266;
}

.main-container {
  padding: 20px;
  background-color: #f0f2f5;
  overflow-y: auto;
}

:deep(.el-menu--vertical) {
  border-right: none;
}

:deep(.el-dropdown-menu__item) {
  display: flex;
  align-items: center;
  gap: 8px;
}
</style>
