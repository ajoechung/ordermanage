import { defineStore } from 'pinia'
import router from '@/router/index.js'

function hasPermission(groups, route) {
  if (route.meta && route.meta.permission) {
    if (groups.includes(1)) {
      return true
    }
    return route.meta.permission.some((p) => groups.includes(p))
  }
  return true
}

function filterAsyncRoutes(routes, permissions) {
  const res = []
  routes.forEach((route) => {
    const tmp = { ...route }
    if (hasPermission(permissions, tmp)) {
      if (tmp.children) {
        tmp.children = filterAsyncRoutes(tmp.children, permissions)
      }
      res.push(tmp)
    }
  })
  return res
}

export const usePermissionStore = defineStore('permission', {
  state: () => ({
    routes: [],
    dynamicRoutes: [],
    permissions: []
  }),

  actions: {
    async generateRoutes(permissions = []) {
      try {
        this.permissions = permissions
        
        const allRoutes = [
          {
            path: '/dashboard',
            name: 'Dashboard',
            component: () => import('@/views/dashboard/index.vue'),
            meta: { title: '首页', icon: 'HomeFilled', permission: ['dashboard'] }
          },
          {
            path: '/customer',
            name: 'Customer',
            meta: { title: '客户管理', icon: 'OfficeBuilding' },
            children: [
              {
                path: 'list',
                name: 'CustomerList',
                component: () => import('@/views/customer/list.vue'),
                meta: { title: '客户列表', permission: ['customer'] }
              },
              {
                path: 'contact',
                name: 'ContactList',
                component: () => import('@/views/customer/contact.vue'),
                meta: { title: '联系人列表', permission: ['customer'] }
              },
              {
                path: 'follow',
                name: 'CustomerFollow',
                component: () => import('@/views/customer/follow.vue'),
                meta: { title: '跟进记录', permission: ['customer'] }
              }
            ]
          },
          {
            path: '/product',
            name: 'Product',
            meta: { title: '产品管理', icon: 'Goods' },
            children: [
              {
                path: 'category',
                name: 'ProductCategory',
                component: () => import('@/views/product/category.vue'),
                meta: { title: '产品分类', permission: ['product'] }
              },
              {
                path: 'list',
                name: 'ProductList',
                component: () => import('@/views/product/list.vue'),
                meta: { title: '产品信息', permission: ['product'] }
              }
            ]
          },
          {
            path: '/supplier',
            name: 'Supplier',
            meta: { title: '供应商管理', icon: 'Box' },
            children: [
              {
                path: 'list',
                name: 'SupplierList',
                component: () => import('@/views/supplier/list.vue'),
                meta: { title: '供应商列表', permission: ['supplier'] }
              }
            ]
          },
          {
            path: '/order',
            name: 'Order',
            meta: { title: '订单管理', icon: 'Document' },
            children: [
              {
                path: 'list',
                name: 'OrderList',
                component: () => import('@/views/order/list.vue'),
                meta: { title: '订单列表', permission: ['order'] }
              }
            ]
          },
          {
            path: '/purchase',
            name: 'Purchase',
            meta: { title: '采购单管理', icon: 'ShoppingCart' },
            children: [
              {
                path: 'list',
                name: 'PurchaseList',
                component: () => import('@/views/purchase/list.vue'),
                meta: { title: '采购单列表', permission: ['purchase'] }
              }
            ]
          },
          {
            path: '/statistics',
            name: 'Statistics',
            meta: { title: '数据统计', icon: 'DataAnalysis' },
            children: [
              {
                path: 'customer',
                name: 'CustomerStats',
                component: () => import('@/views/statistics/customer.vue'),
                meta: { title: '客户统计', permission: ['statistics'] }
              },
              {
                path: 'order',
                name: 'OrderStats',
                component: () => import('@/views/statistics/order.vue'),
                meta: { title: '订单统计', permission: ['statistics'] }
              }
            ]
          },
          {
            path: '/system',
            name: 'System',
            meta: { title: '系统管理', icon: 'Setting' },
            children: [
              {
                path: 'user',
                name: 'SystemUser',
                component: () => import('@/views/system/user.vue'),
                meta: { title: '用户管理', permission: ['system'] }
              },
              {
                path: 'role',
                name: 'SystemRole',
                component: () => import('@/views/system/role.vue'),
                meta: { title: '角色管理', permission: ['system'] }
              }
            ]
          },
          {
            path: '/log',
            name: 'Log',
            meta: { title: '操作日志', icon: 'Operation' },
            children: [
              {
                path: 'list',
                name: 'LogList',
                component: () => import('@/views/system/log.vue'),
                meta: { title: '日志列表', permission: ['operation'] }
              }
            ]
          }
        ]

        const accessedRoutes = filterAsyncRoutes(allRoutes, permissions)

        this.dynamicRoutes = accessedRoutes
        this.routes = accessedRoutes

        return accessedRoutes
      } catch (error) {
        console.error('Generate routes error:', error)
        return []
      }
    },

    resetRoutes() {
      this.routes = []
      this.dynamicRoutes = []
      this.permissions = []
    }
  }
})
