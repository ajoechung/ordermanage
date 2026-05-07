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
