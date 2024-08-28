import CryptoJs from "crypto-js";

export default function useUserData() {
  const userData = ref<any>(null);

  // Methods
  const fetchUserData = () => {
    const encryptedData = localStorage.getItem("user-data");

    userData.value = encryptedData
      ? JSON.parse(
          CryptoJs.AES.decrypt(
            encryptedData || "",
            import.meta.env.VITE_CRYPTO_SECURE_KEY
          ).toString(CryptoJs.enc.Utf8)
        )
      : null;
    

    return userData.value;
  };
  

  const fetchCompanyId = computed(() => {
    const data = fetchUserData();
    
    userData.value = data;
   
    return userData.value?.role === "M" ? userData.value.company_id : null;
  });

  return { fetchCompanyId, fetchUserData };
}
