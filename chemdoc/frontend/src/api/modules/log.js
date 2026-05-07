import request from '@/utils/request'

export function getLogList(params) {
  return request({
    url: '/log/list',
    method: 'get',
    params
  })
}

export function getLogDetail(id) {
  return request({
    url: `/log/${id}`,
    method: 'get'
  })
}

export function deleteLog(id) {
  return request({
    url: `/log/${id}`,
    method: 'delete'
  })
}

export function batchDeleteLog(data) {
  return request({
    url: '/log/batch-delete',
    method: 'post',
    data
  })
}

export const getList = getLogList
