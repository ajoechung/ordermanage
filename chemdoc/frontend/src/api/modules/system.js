import request from '@/utils/request'

export function getList(params) {
  return request({
    url: '/system/userList',
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

export function assignRole(data) {
  return request({
    url: '/system/assignRole',
    method: 'post',
    data
  })
}

export function getPermissionList() {
  return request({
    url: '/system/permissionList',
    method: 'get'
  })
}

export function assignPermission(data) {
  return request({
    url: '/system/assignPermission',
    method: 'post',
    data
  })
}

export function createRole(data) {
  return request({
    url: '/system/role',
    method: 'post',
    data
  })
}

export function updateRole(data) {
  return request({
    url: '/system/role',
    method: 'put',
    data
  })
}

export function deleteRole(id) {
  return request({
    url: `/system/role/${id}`,
    method: 'delete'
  })
}

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
