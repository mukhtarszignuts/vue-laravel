/* eslint-disable import/order */
import '@/@iconify/icons-bundle'
import App from '@/App.vue'
import layoutsPlugin from '@/plugins/layouts'
import vuetify from '@/plugins/vuetify'
import { loadFonts } from '@/plugins/webfontloader'
import router from '@/router'
import '@core-scss/template/index.scss'
import '@styles/styles.scss'
import { createPinia } from 'pinia'
import { createApp } from 'vue'
import VueCryptojs from 'vue-cryptojs'
import Vue3Toasity, { type ToastContainerOptions } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'
import { createHead } from '@vueuse/head';
import { initializeMessageListener } from "@/firebase/index"

const toastConfig = {
    autoClose: 2000,
    closeButton: true,
    hideProgressBar: true,
    theme: 'auto',
    type: 'default',
  
    // position: toast.POSITION.TOP_LEFT,
  }

loadFonts()

initializeMessageListener()

// Create vue app
const app = createApp(App)
const head = createHead();

// Use plugins
app.use(head);
app.use(vuetify)
app.use(createPinia())
app.use(router)
app.use(layoutsPlugin)
app.use(Vue3Toasity, toastConfig as ToastContainerOptions)
app.use(VueCryptojs)

// Mount vue app
app.mount('#app')
