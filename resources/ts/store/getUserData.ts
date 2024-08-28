import { defineStore } from "pinia";
import $http from "@/plugins/axios";
import CryptoJS from "crypto-js";

export const useUserData = defineStore('userDetails', () => {
    // Data
    const userData = ref<any>('')
  
    // Methods
    const getUserDetails = async () => {
      try {
        const encryptedData = localStorage.getItem('user-data')
        const user = encryptedData ? JSON.parse(CryptoJS.AES.decrypt(encryptedData || '', import.meta.env.VITE_CRYPTO_SECURE_KEY).toString(CryptoJS.enc.Utf8)) : null
        if (!user)
          return
  
        const { data: { data } } = await $http.get(`/user/${user?.id}`)
        if (data)
          userData.value = data.user
      }
      catch (e) {
        console.log(e)
      }
    }
  
    return {
      // Data
      userData,
      
      // Methods
      getUserDetails,
    }
  })