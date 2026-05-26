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
    url: `/system/user/${data.id}`,
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

export function getUsersByRole(roleId) {
  return request({
    url: `/system/usersByRole/${roleId}`,
    method: 'get'
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
    url: `/system/group/${data.id}`,
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

export function getPermissionList(params) {
  return request({
    url: '/system/rules',
    method: 'get',
    params
  })
}

export function getPermissionTree() {
  return request({
    url: '/system/ruleTree',
    method: 'get'
  })
}

export function createPermission(data) {
  return request({
    url: '/system/rule',
    method: 'post',
    data
  })
}

export function updatePermission(data) {
  return request({
    url: `/system/rule/${data.id}`,
    method: 'put',
    data
  })
}

export function deletePermission(id) {
  return request({
    url: `/system/rule/${id}`,
    method: 'delete'
  })
}

export function assignPermission(data) {
  return request({
    url: `/system/group/${data.id}`,
    method: 'put',
    data: {
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
