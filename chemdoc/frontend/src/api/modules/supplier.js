import request from '@/utils/request'

export function getSupplierList(params) {
  return request({
    url: '/supplier',
    method: 'get',
    params
  })
}

export function getSupplierDetail(id) {
  return request({
    url: `/supplier/${id}`,
    method: 'get'
  })
}

export function createSupplier(data) {
  return request({
    url: '/supplier',
    method: 'post',
    data
  })
}

export function updateSupplier(id, data) {
  return request({
    url: `/supplier/${id}`,
    method: 'put',
    data
  })
}

export function deleteSupplier(id) {
  return request({
    url: `/supplier/${id}`,
    method: 'delete'
  })
}

export const getList = getSupplierList
export const create = createSupplier
export const update = updateSupplier
export const getPurchaseList = getSupplierList
export const createPurchase = createSupplier
export const updatePurchase = updateSupplier
