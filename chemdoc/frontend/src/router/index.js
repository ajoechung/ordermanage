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
  },
  {
    path: '/',
    component: () => import('@/components/layout/index.vue'),
    redirect: '/dashboard',
    children: [
      {
        path: 'dashboard',
        name: 'Dashboard',
        component: () => import('@/views/dashboard/index.vue'),
        meta: { title: '首页', icon: 'HomeFilled' }
      },
      {
        path: 'profile',
        name: 'Profile',
        component: () => import('@/views/profile/index.vue'),
        meta: { title: '个人中心', hidden: true }
      },
      {
        path: 'password',
        name: 'Password',
        component: () => import('@/views/password/index.vue'),
        meta: { title: '修改密码', hidden: true }
      },
      {
        path: 'customer',
        redirect: '/customer/list',
        meta: { title: '客户管理', icon: 'OfficeBuilding' },
        children: [
          {
            path: 'list',
            name: 'CustomerList',
            component: () => import('@/views/customer/list.vue'),
            meta: { title: '客户列表' }
          },
          {
            path: 'contact',
            name: 'ContactList',
            component: () => import('@/views/customer/contact.vue'),
            meta: { title: '联系人列表' }
          },
          {
            path: 'follow',
            name: 'CustomerFollow',
            component: () => import('@/views/customer/follow.vue'),
            meta: { title: '跟进记录' }
          }
        ]
      },
      {
        path: 'product',
        redirect: '/product/category',
        meta: { title: '产品管理', icon: 'Goods' },
        children: [
          {
            path: 'category',
            name: 'ProductCategory',
            component: () => import('@/views/product/category.vue'),
            meta: { title: '产品分类' }
          },
          {
            path: 'list',
            name: 'ProductList',
            component: () => import('@/views/product/list.vue'),
            meta: { title: '产品信息' }
          }
        ]
      },
      {
        path: 'supplier',
        redirect: '/supplier/list',
        meta: { title: '供应商管理', icon: 'Box' },
        children: [
          {
            path: 'list',
            name: 'SupplierList',
            component: () => import('@/views/supplier/list.vue'),
            meta: { title: '供应商列表' }
          }
        ]
      },
      {
        path: 'order',
        redirect: '/order/list',
        meta: { title: '订单管理', icon: 'Document' },
        children: [
          {
            path: 'list',
            name: 'OrderList',
            component: () => import('@/views/order/list.vue'),
            meta: { title: '订单列表' }
          }
        ]
      },
      {
        path: 'purchase',
        redirect: '/purchase/list',
        meta: { title: '采购单管理', icon: 'ShoppingCart' },
        children: [
          {
            path: 'list',
            name: 'PurchaseList',
            component: () => import('@/views/purchase/list.vue'),
            meta: { title: '采购单列表' }
          }
        ]
      },
      {
        path: 'statistics',
        redirect: '/statistics/customer',
        meta: { title: '数据统计', icon: 'DataAnalysis' },
        children: [
          {
            path: 'customer',
            name: 'CustomerStats',
            component: () => import('@/views/statistics/customer.vue'),
            meta: { title: '客户统计' }
          },
          {
            path: 'order',
            name: 'OrderStats',
            component: () => import('@/views/statistics/order.vue'),
            meta: { title: '订单统计' }
          }
        ]
      },
      {
        path: 'system',
        redirect: '/system/user',
        meta: { title: '系统管理', icon: 'Setting' },
        children: [
          {
            path: 'user',
            name: 'SystemUser',
            component: () => import('@/views/system/user.vue'),
            meta: { title: '用户管理' }
          },
          {
            path: 'role',
            name: 'SystemRole',
            component: () => import('@/views/system/role.vue'),
            meta: { title: '角色管理' }
          },
          {
            path: 'permission',
            name: 'SystemPermission',
            component: () => import('@/views/system/permission.vue'),
            meta: { title: '权限管理' }
          }
        ]
      },
      {
        path: 'dict',
        redirect: '/dict/type',
        meta: { title: '字典管理', icon: 'List' },
        children: [
          {
            path: 'type',
            name: 'DictType',
            component: () => import('@/views/system/dict-type.vue'),
            meta: { title: '字典类型' }
          },
          {
            path: 'data',
            name: 'DictData',
            component: () => import('@/views/system/dict-data.vue'),
            meta: { title: '字典数据' }
          }
        ]
      },
      {
        path: 'log',
        redirect: '/log/list',
        meta: { title: '操作日志', icon: 'Operation' },
        children: [
          {
            path: 'list',
            name: 'LogList',
            component: () => import('@/views/system/log.vue'),
            meta: { title: '日志列表' }
          }
        ]
      }
    ]
  },
  {
    path: '/:pathMatch(.*)*',
    redirect: '/404',
    meta: { hidden: true }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes: constantRoutes
})

router.beforeEach(async (to, from, next) => {
  console.log('路由守卫:', to.path)
  
  const userStore = useUserStore()
  const hasToken = userStore.token

  if (hasToken) {
    if (to.path === '/login') {
      next({ path: '/' })
    } else {
      next()
    }
  } else {
    if (to.path === '/login') {
      next()
    } else {
      next('/login')
    }
  }
})

export default router
