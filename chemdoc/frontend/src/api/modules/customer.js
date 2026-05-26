import request from '@/utils/request'

export function getCustomerList(params) {
  return request({
    url: '/customer',
    method: 'get',
    params
  })
}

export function getCustomerDetail(id) {
  return request({
    url: `/customer/${id}`,
    method: 'get'
  })
}

export function getCustomerFullDetail(id) {
  return request({
    url: `/customer/full-detail/${id}`,
    method: 'get'
  })
}

export function createCustomer(data) {
  return request({
    url: '/customer',
    method: 'post',
    data
  })
}

export function updateCustomer(id, data) {
  return request({
    url: `/customer/${id}`,
    method: 'put',
    data
  })
}

export function deleteCustomer(id, force = false) {
  return request({
    url: `/customer/${id}`,
    method: 'delete',
    params: { force }
  })
}

export const getList = getCustomerList
export const getAll = getCustomerList
export const create = createCustomer
export const update = updateCustomer
export const getDetail = getCustomerDetail
export const getFullDetail = getCustomerFullDetail
