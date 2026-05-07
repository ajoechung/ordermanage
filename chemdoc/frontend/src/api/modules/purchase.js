import request from '@/utils/request'

export function getPurchaseList(params) {
  return request({
    url: '/purchase',
    method: 'get',
    params
  })
}

export function getPurchaseDetail(id) {
  return request({
    url: `/purchase/${id}`,
    method: 'get'
  })
}

export function createPurchase(data) {
  return request({
    url: '/purchase',
    method: 'post',
    data
  })
}

export function updatePurchase(id, data) {
  return request({
    url: `/purchase/${id}`,
    method: 'put',
    data
  })
}

export function deletePurchase(id) {
  return request({
    url: `/purchase/${id}`,
    method: 'delete'
  })
}

export function updatePurchaseStatus(data) {
  return request({
    url: '/purchase/status',
    method: 'post',
    data
  })
}
