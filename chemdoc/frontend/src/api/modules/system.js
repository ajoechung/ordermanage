import request from '@/utils/request'

// 用户相关
export function getList(params) {
  return request({
    url: '/system/users',
    method: 'get',
    params
  })
}

export function create(data) {
  return request({
    url: '/system/user',
    method: 'post',
    data
  })
}

export function update(data) {
  return request({
    url: '/system/user',
    method: 'put',
    data
  })
}

export function deleteUser(id) {
  return request({
    url: `/system/user/${id}`,
    method: 'delete'
  })
}

// 角色相关
export function getRoleList(params) {
  return request({
    url: '/system/groups',
    method: 'get',
    params
  })
}

export function createRole(data) {
  return request({
    url: '/system/group',
    method: 'post',
    data
  })
}

export function updateRole(data) {
  return request({
    url: '/system/group',
    method: 'put',
    data
  })
}

export function deleteRole(id) {
  return request({
    url: `/system/group/${id}`,
    method: 'delete'
  })
}

// 权限相关
export function assignRole(data) {
  return request({
    url: '/system/assignRole',
    method: 'post',
    data
  })
}

export function getPermissionList() {
  return request({
    url: '/system/rules',
    method: 'get'
  })
}

export function assignPermission(data) {
  return request({
    url: '/system/group',
    method: 'put',
    data: {
      id: data.id,
      rules: data.rules
    }
  })
}

// 日志相关
export function batchDeleteLog(data) {
  return request({
    url: '/log/batchDelete',
    method: 'post',
    data
  })
}

export function clearLogs() {
  return request({
    url: '/log/clear',
    method: 'post'
  })
}

export const deleteLog = getList
