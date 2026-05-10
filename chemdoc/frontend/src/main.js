import { createApp } from 'vue'
import { createPinia } from 'pinia'
import ElementPlus from 'element-plus'
import * as ElementPlusIconsVue from '@element-plus/icons-vue'
import 'element-plus/dist/index.css'
import zhCn from 'element-plus/dist/locale/zh-cn.mjs'

import App from './App.vue'
import router from './router'

import './styles/index.scss'

console.log('=== Vue App Initializing ===')

try {
  const app = createApp(App)

  for (const [key, component] of Object.entries(ElementPlusIconsVue)) {
    app.component(key, component)
  }

  const pinia = createPinia()

  app.use(pinia)
  app.use(router)
  app.use(ElementPlus, { locale: zhCn })

  console.log('=== Mounting App ===')
  app.mount('#app')
  console.log('=== App Mounted Successfully ===')
  
} catch (error) {
  console.error('=== Vue App Initialization Error ===')
  console.error(error)
  console.error(error.stack)
}
