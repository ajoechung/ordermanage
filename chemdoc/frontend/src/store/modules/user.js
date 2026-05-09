import { defineStore } from 'pinia'
import { login as loginApi, logout as logoutApi, getUserInfo } from '@/api/modules/auth'
import router from '@/router/index.js'

export const useUserStore = defineStore('user', {
  state: () => ({
    token: localStorage.getItem('token') || '',
    userInfo: JSON.parse(localStorage.getItem('userInfo') || '{}'),
    groups: JSON.parse(localStorage.getItem('groups') || '[]'),
    permissions: JSON.parse(localStorage.getItem('permissions') || '[]')
  }),

  getters: {
    isLoggedIn: (state) => !!state.token,
    username: (state) => state.userInfo?.username || '',
    nickname: (state) => state.userInfo?.nickname || '',
    userId: (state) => state.userInfo?.id || 0,
    avatar: (state) => state.userInfo?.avatar || ''
  },

  actions: {
    async login(loginData) {
      try {
        const res = await loginApi(loginData)
        this.token = res.data.token
        this.userInfo = res.data.user_info || {}
        this.groups = res.data.user_info?.groups || []

        localStorage.setItem('token', this.token)
        localStorage.setItem('userInfo', JSON.stringify(this.userInfo))
        localStorage.setItem('groups', JSON.stringify(this.groups))

        return res
      } catch (error) {
        throw error
      }
    },

    async getInfo() {
      try {
        const res = await getUserInfo()
        if (res.code === 200) {
          this.userInfo = res.data || {}
          this.groups = res.data?.groups || []

          localStorage.setItem('userInfo', JSON.stringify(this.userInfo))
          localStorage.setItem('groups', JSON.stringify(this.groups))
        }
        return res
      } catch (error) {
        throw error
      }
    },

    async logout() {
      try {
        await logoutApi()
      } catch (error) {
        console.error('Logout error:', error)
      } finally {
        this.resetToken()
        router.push('/login')
      }
    },

    resetToken() {
      this.token = ''
      this.userInfo = {}
      this.groups = []
      this.permissions = []
      localStorage.removeItem('token')
      localStorage.removeItem('userInfo')
      localStorage.removeItem('groups')
      localStorage.removeItem('permissions')
    }
  }
})
