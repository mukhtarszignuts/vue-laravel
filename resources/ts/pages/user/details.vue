<script setup lang="ts">
import ChangePassword from '@/components/ChangePassword.vue';
import axios from '@/plugins/axios';
import { useUserData } from '@/store/getUserData';
import { useHead } from '@vueuse/head';
import { toast } from 'vue3-toastify';

// composable
const user = useUserData()
const { getUserDetails } = useUserData()
const { userData: loginUser } = storeToRefs(user)
const isAddNewUserDrawerVisible = ref<boolean>(false)
const isLoading = ref<boolean>(false)
const isEdit = ref<boolean>(true)

// role
const roles = ref<any[]>([
  {
    id: "A",
    name: "Admin",
  },
  {
    id: "C",
    name: "Customer",
  },
  {
    id: "M",
    name: "Manager",
  },
]);

const statusOptions = ref<any[]>([
  {
    id: "P",
    name: "Pending",
  },
  {
    id: "A",
    name: "Active",
  },
  {
    id: "I",
    name: "Rejected",
  },
 
]);

const EditUser = async (userData: any) => {
  try {
    isLoading.value = true;

    const input = {
      name: userData.name,
      image: userData.image,
      phone: userData.phone,
      role: userData.role,
      email: userData.email,
      status: userData.status,
      city: userData.city,
      id: userData.id,
    };
    

    const formData = new FormData();

     // Append non-file data to FormData
     Object.keys(input).forEach(key => {
      if (input[key] !== null && input[key] !== undefined) {
        formData.append(key, input[key]);
      }
    });
    // formData.append(`image`, userData.file);
   
    if (userData.isUpdate) {
      
        const { data } = await axios.post("user/update", formData,{
            headers: {
              'Content-Type': 'multipart/form-data'
            }
        });

        if(data){
            toast.success(data.message)
            isAddNewUserDrawerVisible.value = false;
            isLoading.value = false;
            getUserDetails()
        }
     
    } 
  } catch (e) {
    toast.error(e.response.data.message);
    isLoading.value = false;
  }
};

const setUserDetails = async (val:boolean) =>{
    const { data } = await axios.get(`user/${loginUser.value.id}`)
    if(data){
        isEdit.value = true
        loginUser.value=data.data.user;
        isAddNewUserDrawerVisible.value=val
    }

}

onMounted(async () => {
  await getUserDetails()
})

useHead({
  title:'Laravel-Vue | User Details'
})
</script>
<template>
  <div>
    <VRow>
      <VCol cols="4">
        <PresonalInfo :user-data="loginUser" @isDrawerOpen="setUserDetails" />
      </VCol>
      <VCol cols="8">
        <ChangePassword />
      </VCol>
    </VRow>

    <AddEditUserDialog :is-drawer-open="isAddNewUserDrawerVisible" :is-edit="isEdit" :user-data="loginUser"
      :roles="roles" :status="statusOptions" @close-dialog="isAddNewUserDrawerVisible = false" @user-data="EditUser" />
  </div>
</template>