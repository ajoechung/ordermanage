import request from '@/utils/request'

export function getDashboard() {
  return request({
    url: '/dashboard',
    method: 'get'
  })
}

export function getCustomerStats(params) {
  return request({
    url: '/statistics/customer',
    method: 'get',
    params
  })
}

export function getOrderStats(params) {
  return request({
    url: '/statistics/order',
    method: 'get',
    params
  })
}

export const getDashboardStatistics = getDashboard
export const getSalesTrend = getOrderStats
export const getCustomerDistribution = getCustomerStats
export const getCustomerStatistics = getCustomerStats
export const getOrderStatistics = getOrderStats
