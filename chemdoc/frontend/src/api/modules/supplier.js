import request from '@/utils/request'

export function getSupplierList(params) {
  return request({ url: '/supplier', method: 'get', params })
}

export function getSupplierDetail(id) {
  return request({ url: `/supplier/${id}`, method: 'get' })
}

export function createSupplier(data) {
  return request({ url: '/supplier', method: 'post', data })
}

export function updateSupplier(id, data) {
  return request({ url: `/supplier/${id}`, method: 'put', data })
}

export function deleteSupplier(id) {
  return request({ url: `/supplier/${id}`, method: 'delete' })
}

export function getPurchaseList(params) {
  return request({ url: '/purchase', method: 'get', params })
}

export function createPurchase(data) {
  return request({ url: '/purchase', method: 'post', data })
}

export function updatePurchase(id, data) {
  return request({ url: `/purchase/${id}`, method: 'put', data })
}

export function deletePurchase(id) {
  return request({ url: `/purchase/${id}`, method: 'delete' })
}

export const getList = getSupplierList
export const create = createSupplier
export const update = updateSupplier
