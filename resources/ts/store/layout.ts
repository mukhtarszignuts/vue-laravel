import { defineStore } from 'pinia'

export const layout = defineStore('layout', () => {
  // Data
  const fullScreenLoad = ref<boolean>(false)

  return {
    fullScreenLoad,
  }
})
