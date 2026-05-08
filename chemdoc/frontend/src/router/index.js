import { createRouter, createWebHistory } from 'vue-router'
import { useUserStore } from '@/store/modules/user'
import { ElMessage } from 'element-plus'

export const constantRoutes = [
  {
    path: '/login',
    name: 'Login',
    component: () => import('@/views/login/index.vue'),
    meta: { title: '登录', hidden: true }
  },
  {
    path: '/404',
    name: 'NotFound',
    component: () => import('@/views/error/404.vue'),
    meta: { title: '404', hidden: true }
  },
  {
    path: '/403',
    name: 'Forbidden',
    component: () => import('@/views/error/403.vue'),
    meta: { title: '无权限', hidden: true }
  }
]

export const asyncRoutes = [
  {
    path: '/',
    component: () => import('@/components/layout/index.vue'),
    redirect: '/dashboard',
    children: [
      {
        path: 'dashboard',
        name: 'Dashboard',
        component: () => import('@/views/dashboard/index.vue'),
        meta: { title: '首页', icon: 'HomeFilled', permission: ['dashboard', 'admin'] }
      }
    ]
  },
  {
    path: '/customer',
    component: () => import('@/components/layout/index.vue'),
    redirect: '/customer/list',
    meta: { title: '客户管理', icon: 'OfficeBuilding', permission: ['customer', 'admin'] },
    children: [
      {
        path: 'list',
        name: 'CustomerList',
        component: () => import('@/views/customer/list.vue'),
        meta: { title: '客户列表', permission: ['customer', 'admin'] }
      },
      {
        path: 'contact',
        name: 'ContactList',
        component: () => import('@/views/customer/contact.vue'),
        meta: { title: '联系人列表', permission: ['customer', 'admin'] }
      },
      {
        path: 'follow',
        name: 'CustomerFollow',
        component: () => import('@/views/customer/follow.vue'),
        meta: { title: '跟进记录', permission: ['customer', 'admin'] }
      }
    ]
  },
  {
    path: '/product',
    component: () => import('@/components/layout/index.vue'),
    redirect: '/product/category',
    meta: { title: '产品管理', icon: 'Goods', permission: ['product', 'admin'] },
    children: [
      {
        path: 'category',
        name: 'ProductCategory',
        component: () => import('@/views/product/category.vue'),
        meta: { title: '产品分类', permission: ['product', 'admin'] }
      },
      {
        path: 'list',
        name: 'ProductList',
        component: () => import('@/views/product/list.vue'),
        meta: { title: '产品信息', permission: ['product', 'admin'] }
      }
    ]
  },
  {
    path: '/supplier',
    component: () => import('@/components/layout/index.vue'),
    redirect: '/supplier/list',
    meta: { title: '供应商管理', icon: 'Box', permission: ['supplier', 'admin'] },
    children: [
      {
        path: 'list',
        name: 'SupplierList',
        component: () => import('@/views/supplier/list.vue'),
        meta: { title: '供应商列表', permission: ['supplier', 'admin'] }
      }
    ]
  },
  {
    path: '/order',
    component: () => import('@/components/layout/index.vue'),
    redirect: '/order/list',
    meta: { title: '订单管理', icon: 'Document', permission: ['order', 'admin'] },
    children: [
      {
        path: 'list',
        name: 'OrderList',
        component: () => import('@/views/order/list.vue'),
        meta: { title: '订单列表', permission: ['order', 'admin'] }
      }
    ]
  },
  {
    path: '/purchase',
    component: () => import('@/components/layout/index.vue'),
    redirect: '/purchase/list',
    meta: { title: '采购单管理', icon: 'ShoppingCart', permission: ['purchase', 'admin'] },
    children: [
      {
        path: 'list',
        name: 'PurchaseList',
        component: () => import('@/views/purchase/list.vue'),
        meta: { title: '采购单列表', permission: ['purchase', 'admin'] }
      }
    ]
  },
  {
    path: '/statistics',
    component: () => import('@/components/layout/index.vue'),
    redirect: '/statistics/customer',
    meta: { title: '数据统计', icon: 'DataAnalysis', permission: ['statistics', 'admin'] },
    children: [
      {
        path: 'customer',
        name: 'CustomerStats',
        component: () => import('@/views/statistics/customer.vue'),
        meta: { title: '客户统计', permission: ['statistics', 'admin'] }
      },
      {
        path: 'order',
        name: 'OrderStats',
        component: () => import('@/views/statistics/order.vue'),
        meta: { title: '订单统计', permission: ['statistics', 'admin'] }
      }
    ]
  },
  {
    path: '/system',
    component: () => import('@/components/layout/index.vue'),
    redirect: '/system/user',
    meta: { title: '系统管理', icon: 'Setting', permission: ['system', 'admin'] },
    children: [
      {
        path: 'user',
        name: 'SystemUser',
        component: () => import('@/views/system/user.vue'),
        meta: { title: '用户管理', permission: ['system', 'admin'] }
      },
      {
        path: 'role',
        name: 'SystemRole',
        component: () => import('@/views/system/role.vue'),
        meta: { title: '角色管理', permission: ['system', 'admin'] }
      }
    ]
  },
  {
    path: '/log',
    component: () => import('@/components/layout/index.vue'),
    redirect: '/log/list',
    meta: { title: '操作日志', icon: 'Operation', permission: ['operation', 'admin'] },
    children: [
      {
        path: 'list',
        name: 'LogList',
        component: () => import('@/views/system/log.vue'),
        meta: { title: '日志列表', permission: ['operation', 'admin'] }
      }
    ]
  }
]

function hasPermission(permissions, route) {
  if (route.meta && route.meta.permission) {
    return permissions.some((permission) => route.meta.permission.includes(permission))
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

const router = createRouter({
  history: createWebHistory(),
  routes: constantRoutes
})

export function resetRouter() {
  const asyncRouterNames = asyncRoutes.flatMap(route => getRouteNames(route))
  asyncRouterNames.forEach(name => {
    if (router.hasRoute(name)) {
      router.removeRoute(name)
    }
  })
}

function getRouteNames(route) {
  const names = [route.name]
  if (route.children) {
    route.children.forEach(child => {
      names.push(...getRouteNames(child))
    })
  }
  return names
}

router.beforeEach(async (to, from, next) => {
  const userStore = useUserStore()
  const hasToken = userStore.token

  if (hasToken) {
    if (to.path === '/login') {
      next({ path: '/' })
    } else {
      if (Object.keys(userStore.userInfo).length === 0) {
        try {
          await userStore.getInfo()
        } catch (error) {
          userStore.resetToken()
          ElMessage.error('获取用户信息失败，请重新登录')
          next(`/login?redirect=${to.path}`)
          return
        }
      }

      if (to.meta.permission) {
        const hasPermission = to.meta.permission.some((p) =>
          userStore.permissions.includes(p) || userStore.groups.includes(1)
        )
        if (!hasPermission) {
          if (to.path !== '/403') {
            next({ path: '/403' })
          } else {
            next()
          }
          return
        }
      }

      const addedRoutes = router.getRoutes().map(r => r.path)
      if (!addedRoutes.includes(to.path) && to.path !== '/') {
        const permissions = userStore.permissions || []
        const filteredRoutes = filterAsyncRoutes(asyncRoutes, permissions)

        filteredRoutes.forEach(route => {
          if (!router.hasRoute(route.name)) {
            router.addRoute(route)
          }
        })

        router.addRoute({
          path: '/:pathMatch(.*)*',
          redirect: '/404',
          meta: { hidden: true }
        })

        const fullPath = router.resolve(to.path).matched.length > 0 ? to.path : to.fullPath
        next({ path: fullPath, replace: true })
        return
      }

      next()
    }
  } else {
    if (to.path === '/login') {
      next()
    } else {
      next(`/login`)
    }
  }
})

export default router
