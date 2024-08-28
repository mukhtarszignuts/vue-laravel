<script setup lang="ts">
import { isEmptyArray } from "@/@core/utils";
import AddEditCompanyDialog from "@/components/dialogs/AddEditCompanyDialog.vue";
import axios from '@/plugins/axios';
import { avatarText } from '@core/utils/formatters';
import noData from '@images/no_data.svg';
import { useHead } from '@vueuse/head';
import moment from 'moment';
import { toast } from 'vue3-toastify';
import { fetchCompanyList , AddEditCompany , getCompanydata ,deleteCompany , statusChange} from "@/services/CompanyService";

interface CompanyData{
    id: number
    name: string
    owner_id: number
    owner: object
    employee_size: number
    address:string
    is_active:boolean
    created_at: string
    logo:string
    image_url:string
    employees_count: number
}

interface OwnerData{
    id?:number,
    name?:string,
}

const isLoading = ref<boolean>(false);
const search = ref<any>();
const isEdit = ref<boolean>(false);
const companyList = ref<CompanyData[]>([])
const count = ref<number>(0);
const perPage = ref<number>(10);
const lengthPage = ref<number>(0);
const currentPage = ref<number>(1);
const sortKey = ref<string>('created_at');
const sortOrder = ref<string>('desc');
const addEditCompanyData = ref<object | null>({});
const isConfirmDialog = ref<boolean>(false)
const deletedId = ref<number>()
const isAddNewCompanyDrawerVisible = ref<boolean>(false)
const allSelectedType = ref<boolean>(false)
const selected = ref([]);
const bulkDeleteIds = ref([]);
const isDeleteDisabled= ref<boolean>(true)
const isBulkDeleteBox = ref<boolean>(false)
const ownerList = ref<OwnerData[]>([])

const pageCountList = ref<any[]>([
  {
    label: "10",
    value: 10,
  },

  {
    label: "25",
    value: 25,
  },
  {
    label: "50",
    value: 50,
  },
  {
    label: "100",
    value: 100,
  },
]);

// headers
const headers = [
  { title: 'NO', key: 'no' },
  { title: 'NAME', key: 'name' },
  // { title: 'OWNER', key: 'owner' },
  { title: 'EMPLOYEE', key: 'employee_size' },
  { title: 'ADDRESS', key: 'address' },
  { title: 'STATUS', key: 'is_active' },
  { title: 'CREATED AT', key: 'created_at' },
  { title: 'ACTION', key: 'action' },
]

// search user
const searchCompany = async (data:any) => {
  if (data.length > 2 || data.length === 0) {
    currentPage.value = 1;
    await getCompanyList();
  }
};

/// get Owner list 
const getOwnerList = async()=>{
    try {
        const input = {
            sort_feild: sortKey.value,
            sort_order: sortOrder.value,
            status: 'A',
        };

        const { data } = await axios.post('admin/user/list',input)
        
        if(data){
          ownerList.value=data.data.users
        } 
    } catch (e) {
        console.log(e);
        toast.error(e.response.data.message)
    }
}

// get Category list 
const getCompanyList = async()=>{
    try {
        const input = {
            sort_field: sortKey.value,
            sort_order: sortOrder.value,
            page: currentPage.value,
            per_page: perPage.value,
            search: search.value,
        };

        // const { data } = await axios.post('admin/company/list',input)
        const data = await fetchCompanyList(input)
        
        if(data){
            companyList.value=data.companies
            count.value=data.count
            lengthPage.value = Math.ceil(data.count / perPage.value);
        } 
    } catch (e) {
        console.log(e);
        toast.error(e.response.data.message)
    }
}

// 👉 Add new company
const AddNewCompany = async (companyData: any) => {
  try {
    isLoading.value = true;

    const input = {
      name: companyData.name,
      is_active: companyData.is_active,
      owner_id: companyData.owner_id,
      address: companyData.address,
      employee_size: companyData.employee_size,
      logo: companyData.logo,
      id: companyData.isUpdate === true ? companyData.id : null,
    };
     
    const formData = new FormData();

    // Append non-file data to FormData
    Object.keys(input).forEach(key => {
    if (input[key] !== null && input[key] !== undefined) {
      formData.append(key, input[key]);
    }
    });
 
    let response;
    if (companyData.isUpdate) {
  
      const data = await AddEditCompany(formData,true)
      response = data;
    } else {
     
      const data = await AddEditCompany(formData,false)
      response = data;
     
    }

    if (response) {
      // console.log('response',response);
      toast.success(response.message)
      isAddNewCompanyDrawerVisible.value = false;
      isLoading.value = false;
      getCompanyList();
    }else{
      
      isAddNewCompanyDrawerVisible.value = false;
      isLoading.value = false;
    }
  } catch (e) {
    console.error(e);
    toast.error(e.response.data.message);
    // isLoading.value = false;
  }
};

// Edit Company
const EditCompany = async (id:number) => {
    try {
      // const { data } = await axios.get(`admin/company/${id}`)
      const data = await getCompanydata(id)
      
      if(data){
        addEditCompanyData.value = data.company
        isEdit.value = true,
        isAddNewCompanyDrawerVisible.value = true

      }
    } catch (e) {
      console.log(e);
    }
}

// delete Company 
const companyDelete = async () => {
  try {
        isLoading.value = true
        
        const data  = await deleteCompany(deletedId.value);
                
        if(data){
            toast.success(data.message)
            await getCompanyList()
            isConfirmDialog.value = false
            isLoading.value = false
        }
    } catch (e) {
        console.log(e);
        toast.error(e)
        isConfirmDialog.value = false
        isLoading.value = false
    }
}


// status change
const companyStatusChange = async (id:number) => {
    try {
       const data = await statusChange(id)
              
       if(data){
         toast.success(data.message)
         await getCompanyList();
       }
     } catch (e) {
        toast.error(e.response.data.message)
       console.log(e);
     }
}


const allSelect = (i: boolean) => {

if (i) {
  if (companyList.value && Array.isArray(companyList.value) && companyList.value.length) {
    selected.value = []
    companyList.value.forEach((obj: any) => {
      const matchingObject = obj.id
      if (matchingObject)
        selected.value.push(matchingObject)
    })
  }
}
else {
  selected.value = []
}
}

// TODO Need to chage
const actionList = (item: any) => {

const actions = [
  
  {
    title: "Edit",
    id: "1",
    prependIcon: "tabler-edit",
    action: "Edit",
    show: true,
  },
  {
    title: "Delete",
    id: "1",
    prependIcon: "tabler-trash",
    action: "Delete",
    show: true,
  },
];
return actions

};

const handleMoreAction = (action: any, item: any) => {
if (action === "Edit") {
    EditCompany(item.id)
}else if(action === "Delete") {
  deletedId.value=item.id
  isConfirmDialog.value=true
}
};

const fromNumber = computed(() => {
return currentPage.value * perPage.value - perPage.value + 1;
});

const toNumber = computed(() => {
return count.value > currentPage.value * perPage.value
  ? currentPage.value * perPage.value
  : count.value;
});


onMounted(async () => {
    await getCompanyList()
    await getOwnerList()
})

watch(
async () => currentPage.value,
async (val) => {
  if (await val){
      await getCompanyList();
  } 
}
);

watch(
async () => perPage.value,
async (val) => {
  if (await val) {
    currentPage.value = 1;
    await getCompanyList();
  }
}
);

watch (async () => selected.value,
  async (val)=>{
    
    if(await val){
      isDeleteDisabled.value = true
      bulkDeleteIds.value = [];
      selected.value.forEach((id: any) => {
        
        const matchingDeleteObject = companyList.value.find(
          (obj: any) => obj.id === id 
        );

        if(matchingDeleteObject){
          isDeleteDisabled.value = false
          bulkDeleteIds.value.push(matchingDeleteObject.id);
        }
      });
    }
  })
  
  useHead({
      title: 'Laravel-Vue | Company List',
    });
</script>
<template>
  <div>
    <VCard title="Company List">
      <VCardText>
        <VCardText>
          <VRow>
            <VCol cols="12" class="justify-end">
              <AppTextField v-model="search" class="error-custom search-input w-25 ms-auto" placeholder="Search"
                @input="searchCompany(search)" />
            </VCol>
          </VRow>
          <VRow class="px-3">
            <VCol lg="3" md="3" sm="12" cols="12">
              <div class="d-flex align-center filter-box-width">
                Show
                <div>
                  <AppSelect v-model="perPage" :items="pageCountList" item-value="value" item-title="label"
                    class="mx-2" />
                </div>
                Entries
              </div>
            </VCol>
            <VCol md="9" lg="9" sm="12" cols="12">
              <div class="d-flex gap-3 common-filter justify-end">
                <div class="d-flex common-filter gap-3">
                  <div class="gap-3 d-flex flex-column">
                    <div class="d-flex gap-3 flex-wrap">

                      <!-- <VBtn class="mr-0" color="primary" :disabled="isDeleteDisabled" @click="isBulkDeleteBox=true">
                        Bulk Delete
                      </VBtn> -->

                      <VBtn class="mr-0" color="primary" @click="
                            (addEditCompanyData = null),
                            (isEdit = false),
                            (isAddNewCompanyDrawerVisible = true)">
                        Add Company
                      </VBtn>
                    </div>
                  </div>
                </div>
              </div>
            </VCol>
          </VRow>
        </VCardText>
        <div v-if="!isEmptyArray(companyList)">
          <VTable :items-per-page="perPage">
            <thead>
              <tr>
                <th>
                  <VCheckbox v-model="allSelectedType" @click="allSelect(!allSelectedType)" />
                </th>
                <th v-for="(item,index) in headers">
                  {{ item.title }}
                </th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item,index) in companyList" :key="item.id">
                <td>
                  <VCheckbox v-model="selected" :value="item?.id" />
                </td>
                <td>
                  {{
                  (currentPage === 1
                  ? count
                  : count - (currentPage - 1) * perPage) - index
                  }}
                </td>
                <td>
                  <div class="d-flex align-center">
                    <VAvatar size="38" :variant="!item.logo ? 'tonal' : undefined" :color="'secondary'" class="me-3">
                      <VImg v-if="item.image_url" :src="item.image_url" />
                      <span v-else>{{ avatarText(item.name) }}</span>
                    </VAvatar>
                    <div class="d-flex flex-column">
                      <h6 class="text-body-1 font-weight-medium">
                        <RouterLink :to="'/'" class="user-list-name">
                          {{ item.name }}
                        </RouterLink>
                      </h6>
                      <span class="text-sm text-disabled">{{ item?.owner?.name }}</span>
                    </div>
                  </div>
                </td>
                <td>
                  {{ item?.employee_size +' / '+ item?.employees_count }}
                </td>
                <td>
                  {{ item.address??'--'}}
                </td>
                <td>
                  <VSwitch :model-value=" item.is_active === 1 ? true : false " @click="companyStatusChange(item.id)" />
                </td>

                <td>
                  {{ item.created_at?moment(item.created_at).format('YYYY-MM-DD HH:MM:ss'):' - '}}
                </td>
                <td>
                  <div class="d-flex">
                    <IconBtn>
                      <VIcon icon="mdi-dots-vertical" />
                      <VMenu activator="parent" open-on-click>
                        <VCard>
                          <VList v-for="i in actionList(item).filter(action => action.show !== false)" :key="i?.id"
                            class="pa-1">
                            <VListItem @click="handleMoreAction(i?.action, item)">
                              <template #prepend>
                                <VIcon :icon="i.prependIcon" />
                              </template>
                              <template #title>
                                {{ i?.title }}
                              </template>
                            </VListItem>
                          </VList>
                        </VCard>
                      </VMenu>
                    </IconBtn>
                  </div>
                </td>
              </tr>
            </tbody>
          </VTable>
        </div>
        <div v-else>
          <VImg :src="noData" alt="no data" height="450px" />
        </div>

        <div v-if="!isEmptyArray(companyList)" class="d-flex mx-7 mt-5">
          <VRow v-if="!isEmptyArray(companyList)">
            <VCol class="d-flex align-center">
              <div>
                Showing {{ fromNumber }} to {{ toNumber }} of {{ count }} entries
              </div>
            </VCol>
            <VCol>
              <div class="right-aligned-pagination">
                <VPagination v-model="currentPage" :total-visible="10" :length="lengthPage" />
              </div>
            </VCol>
          </VRow>
        </div>
      </VCardText>
    </VCard>


    <!-- Single delete Category -->
    <VDialog v-model="isConfirmDialog" max-width="450" persistent class="v-dialog-sm">
      <!-- Dialog close btn -->
      <DialogCloseBtn @click="isConfirmDialog = !isConfirmDialog" />
      <!-- Dialog Content -->
      <VCard>
        <VCardText>
          <div class="text-center mt-5">
            <h2>Are You Sure You Want to delete ?</h2>
          </div>
        </VCardText>
        <VCardText class="d-flex justify-center gap-3 flex-wrap">
          <VBtn color="error" variant="tonal" @click="isConfirmDialog = false">
            No
          </VBtn>
          <VBtn @click.prevent="companyDelete" color="success">
            Yes
          </VBtn>
        </VCardText>
      </VCard>
    </VDialog>

    <!-- Bluk Delete User  -->
    <VDialog v-model="isBulkDeleteBox" persistent class="v-dialog-sm">
      <!-- Dialog close btn -->
      <DialogCloseBtn @click="isBulkDeleteBox = !isBulkDeleteBox" />
      <!-- Dialog Content -->
      <VCard>
        <v-card-title primary-title class="text-center">
          Please Confirm..!
        </v-card-title>
        <VCardText>
          <div class="text-center mt-5">
            <h2>Are You Sure You Want to delete ?</h2>
          </div>
        </VCardText>
        <VCardText class="d-flex justify-center gap-3 flex-wrap">
          <VBtn color="error" variant="tonal" @click="isBulkDeleteBox = false">
            No
          </VBtn>
          <VBtn @click="blukDelete()" color="success">
            Yes
          </VBtn>
        </VCardText>
      </VCard>
    </VDialog>

    <AddEditCompanyDialog :company-data="addEditCompanyData" :is-drawer-open="isAddNewCompanyDrawerVisible"
      :is-edit="isEdit" :owners="ownerList" :is-loading="isLoading" @close-dialog="isAddNewCompanyDrawerVisible = false"
      @company-data="AddNewCompany" />

  </div>
</template>