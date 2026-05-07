import request from '@/utils/request'

export function getFollowList(params) {
  return request({
    url: '/follow',
    method: 'get',
    params
  })
}

export function createFollow(data) {
  return request({
    url: '/follow',
    method: 'post',
    data
  })
}

export function deleteFollow(id) {
  return request({
    url: `/follow/${id}`,
    method: 'delete'
  })
}
