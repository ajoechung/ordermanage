// 测试菜单逻辑
const constantRoutes = [
  { path: '/', redirect: '/dashboard', children: [{ path: 'dashboard', meta: { title: '首页', icon: 'HomeFilled' } }] },
  { path: '/customer', meta: { title: '客户管理', icon: 'OfficeBuilding' }, children: [
    { path: 'list', meta: { title: '客户列表' } },
    { path: 'contact', meta: { title: '联系人列表' } },
    { path: 'follow', meta: { title: '跟进记录' } }
  ]},
  { path: '/product', meta: { title: '产品管理', icon: 'Goods' }, children: [
    { path: 'category', meta: { title: '产品分类' } },
    { path: 'list', meta: { title: '产品信息' } }
  ]},
  { path: '/supplier', meta: { title: '供应商管理', icon: 'Box' }, children: [
    { path: 'list', meta: { title: '供应商列表' } }
  ]}
]

const menuItems = []
const routes = constantRoutes

for (const route of routes) {
  if (route.meta?.hidden || ['/login', '/404', '/403'].includes(route.path)) {
    continue
  }
  
  const icon = route.meta?.icon || 'Menu'
  
  if (route.path === '/') {
    if (route.children && route.children.length > 0) {
      const child = route.children[0]
      menuItems.push({
        key: child.path,
        type: 'menu',
        path: '/' + child.path,
        title: child.meta?.title || '首页',
        icon: icon
      })
    }
  } else if (route.children && route.children.length > 1) {
    const children = route.children.map(child => ({
      path: route.path + '/' + child.path,
      title: child.meta?.title || child.path
    }))
    menuItems.push({
      key: route.path,
      type: 'submenu',
      path: route.path,
      title: route.meta?.title || route.path,
      icon: icon,
      children: children
    })
  } else if (route.children && route.children.length === 1) {
    const child = route.children[0]
    menuItems.push({
      key: route.path,
      type: 'menu',
      path: route.path + '/' + child.path,
      title: route.meta?.title || child.meta?.title || route.path,
      icon: icon
    })
  } else {
    menuItems.push({
      key: route.path,
      type: 'menu',
      path: route.path,
      title: route.meta?.title || route.path,
      icon: icon
    })
  }
}

console.log('=== 菜单生成结果 ===')
console.log(JSON.stringify(menuItems, null, 2))
