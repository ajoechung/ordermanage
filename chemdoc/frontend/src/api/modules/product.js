import request from '@/utils/request'

export function getProductList(params) {
  return request({
    url: '/product',
    method: 'get',
    params
  })
}

export function getProductDetail(id) {
  return request({
    url: `/product/${id}`,
    method: 'get'
  })
}

export function createProduct(data) {
  return request({
    url: '/product',
    method: 'post',
    data
  })
}

export function updateProduct(id, data) {
  return request({
    url: `/product/${id}`,
    method: 'put',
    data
  })
}

export function deleteProduct(id) {
  return request({
    url: `/product/${id}`,
    method: 'delete'
  })
}

export function getProductCategories(params) {
  return request({
    url: '/product/categories',
    method: 'get',
    params
  })
}

export function createCategory(data) {
  return request({
    url: '/product/category',
    method: 'post',
    data
  })
}

export function updateCategory(id, data) {
  return request({
    url: `/product/category/${id}`,
    method: 'put',
    data
  })
}

export function deleteCategory(id) {
  return request({
    url: `/product/category/${id}`,
    method: 'delete'
  })
}

export const getList = getProductList
export const create = createProduct
export const update = updateProduct
export const getCategoryList = getProductCategories
export const getAll = getProductList
