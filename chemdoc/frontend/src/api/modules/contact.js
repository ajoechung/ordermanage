import request from '@/utils/request'

export function getContactList(params) {
  return request({
    url: '/contact',
    method: 'get',
    params
  })
}

export function getContactDetail(id) {
  return request({
    url: `/contact/${id}`,
    method: 'get'
  })
}

export function createContact(data) {
  return request({
    url: '/contact',
    method: 'post',
    data
  })
}

export function updateContact(id, data) {
  return request({
    url: `/contact/${id}`,
    method: 'put',
    data
  })
}

export function deleteContact(id) {
  return request({
    url: `/contact/${id}`,
    method: 'delete'
  })
}
