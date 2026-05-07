import request from '@/utils/request'

export function getOrderList(params) {
  return request({
    url: '/order',
    method: 'get',
    params
  })
}

export function getOrderDetail(id) {
  return request({
    url: `/order/${id}`,
    method: 'get'
  })
}

export function createOrder(data) {
  return request({
    url: '/order',
    method: 'post',
    data
  })
}

export function updateOrder(id, data) {
  return request({
    url: `/order/${id}`,
    method: 'put',
    data
  })
}

export function deleteOrder(id) {
  return request({
    url: `/order/${id}`,
    method: 'delete'
  })
}

export function updateOrderStatus(data) {
  return request({
    url: '/order/status',
    method: 'post',
    data
  })
}
