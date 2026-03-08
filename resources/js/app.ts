import './lib/axios';
import { createApp } from 'vue'
import { createPinia } from 'pinia'
import router from '@/js/router'
import App from '@/js/App.vue'
import 'v-calendar/style.css';
import { Money3Directive } from 'v-money3'
import { initTheme } from '@/js/lib/theme'

const pinia = createPinia()
const app = createApp(App)

initTheme()
app.use(router)
app.use(pinia)
app.directive('money', Money3Directive)
app.mount('body')
