import request from '@/utils/request'

export function getDictTypeList(params) {
  return request({
    url: '/dict/types',
    method: 'get',
    params
  })
}

export function getDictTypeDetail(id) {
  return request({
    url: `/dict/type/${id}`,
    method: 'get'
  })
}

export function createDictType(data) {
  return request({
    url: '/dict/type',
    method: 'post',
    data
  })
}

export function updateDictType(id, data) {
  return request({
    url: `/dict/type/${id}`,
    method: 'put',
    data
  })
}

export function deleteDictType(id) {
  return request({
    url: `/dict/type/${id}`,
    method: 'delete'
  })
}

export function getDictDataList(params) {
  return request({
    url: '/dict/data',
    method: 'get',
    params
  })
}

export function getDictDataDetail(id) {
  return request({
    url: `/dict/data/${id}`,
    method: 'get'
  })
}

export function createDictData(data) {
  return request({
    url: '/dict/data',
    method: 'post',
    data
  })
}

export function updateDictData(id, data) {
  return request({
    url: `/dict/data/${id}`,
    method: 'put',
    data
  })
}

export function deleteDictData(id) {
  return request({
    url: `/dict/data/${id}`,
    method: 'delete'
  })
}

export function getDictByCode(code) {
  return request({
    url: `/dict/code/${code}`,
    method: 'get'
  })
}
