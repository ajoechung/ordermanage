import request from '@/utils/request'

export function getLogList(params) {
  return request({
    url: '/log',
    method: 'get',
    params
  })
}

export function getSystemUsers(params) {
  return request({
    url: '/system/users',
    method: 'get',
    params
  })
}

export function createSystemUser(data) {
  return request({
    url: '/system/user',
    method: 'post',
    data
  })
}

export function updateSystemUser(id, data) {
  return request({
    url: `/system/user/${id}`,
    method: 'put',
    data
  })
}

export function deleteSystemUser(id) {
  return request({
    url: `/system/user/${id}`,
    method: 'delete'
  })
}

export function getGroups() {
  return request({
    url: '/system/groups',
    method: 'get'
  })
}

export function createGroup(data) {
  return request({
    url: '/system/group',
    method: 'post',
    data
  })
}

export function updateGroup(id, data) {
  return request({
    url: `/system/group/${id}`,
    method: 'put',
    data
  })
}

export function deleteGroup(id) {
  return request({
    url: `/system/group/${id}`,
    method: 'delete'
  })
}

export function getRules() {
  return request({
    url: '/system/rules',
    method: 'get'
  })
}

export function uploadImage(data) {
  return request({
    url: '/upload/image',
    method: 'post',
    data,
    headers: {
      'Content-Type': 'multipart/form-data'
    }
  })
}
