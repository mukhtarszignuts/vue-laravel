<script setup lang="ts">
import { isEmptyArray } from "@/@core/utils";
import AddEditCategoryDialog from "@/components/dialogs/AddEditCategoryDialog.vue";
import axios from '@/plugins/axios';
import noData from '@images/no_data.svg';
import { useHead } from '@vueuse/head';
import moment from 'moment';
import { toast } from 'vue3-toastify';

interface CategoryData{
    id: number
    name: string
    display_order: number
    products_count: number
    is_active:number
    created_at: string
}

const isLoading = ref<boolean>(false);
const search = ref<string>();
const isEdit = ref<boolean>(false);
const categoryList = ref<CategoryData[]>([])
const count = ref<number>(0);
const perPage = ref<number>(5);
const lengthPage = ref<number>(0);
const currentPage = ref(1);
const sortKey = ref('created_at');
const sortOrder = ref('desc');
const addEditCategoryData = ref<object | null>({});
const isConfirmDialog = ref<boolean>(false)
const deletedId = ref<number>()
const isAddNewCategoryDrawerVisible = ref<boolean>(false)
const allSelectedType = ref<boolean>(false)
const selected = ref([]);
const bulkDeleteIds = ref([]);
const isDeleteDisabled= ref<boolean>(true)
const isBulkDeleteBox = ref<boolean>(false)

const pageCountList = ref<any[]>([
  {
    label: "5",
    value: 5,
  },
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
  { title: 'IS ACTIVE', key: 'is_active' },
  { title: 'PRODUCT', key: 'products_count' },
  { title: 'CREATED AT', key: 'created_at' },
  { title: 'ACTION', key: 'action' },
]

// search user
const searchCategory = async (data:any) => {
  if (data.length > 2 || data.length === 0) {
    currentPage.value = 1;
    await getCategoryList();
  }
};

// get Category list 
const getCategoryList = async()=>{
    try {
        const input = {
            sortKey: sortKey.value,
            sortOrder: sortOrder.value,
            page: currentPage.value,
            per_page: perPage.value,
            search: search.value,
        };

        const { data } = await axios.post('admin/category/list',input)
        
        if(data){
            categoryList.value=data.data.category
            count.value=data.data.count
            lengthPage.value = Math.ceil(data.data.count / perPage.value);
        } 
    } catch (e) {
        console.log(e);
        toast.error(e.response.data.message)
    }
}

// 👉 Add new category
const AddNewCategory = async (categoryData: any) => {
  try {
    isLoading.value = true;

    const input = {
      name: categoryData.name,
      is_active: categoryData.is_active,
      display_order: categoryData.display_order,
      id: categoryData.isUpdate === true ? categoryData.id : null,
    };
    
    
    let response;
    if (categoryData.isUpdate) {
      //
      toast.success('Category Update SuccessFully..!')
      const { data } = await axios.post("admin/category/update", input);
      response = data;
    } else {
      
      const { data } = await axios.post("admin/category/create", input);
       response = data;
       toast.success('Category Create SuccessFully..!')
    }

    if (response) {
      
      isAddNewCategoryDrawerVisible.value = false;
      getCategoryList();
      isLoading.value = false;
    }else{
      
      isAddNewCategoryDrawerVisible.value = false;
      isLoading.value = false;
    }
  } catch (e) {
    toast.error(e.response.data.message);
    isLoading.value = false;
  }
};

// Edit Category
const EditCategory = async (id:number) => {
    try {
      const { data } = await axios.get(`admin/category/${id}`)
      
      if(data){
        addEditCategoryData.value = data.data.category
        isEdit.value = true,
        isAddNewCategoryDrawerVisible.value = true
      }
    } catch (e) {
      console.log(e);
    }
}

// delete Category 
const deleteCategory = async () => {
    try {
        isLoading.value = true
        const path = `/admin/category/delete/${deletedId.value}`;

        const { data } = await axios.get(`${path}`);
        
        if(data){
            toast.success(`Category Delete Successfully...!`)
            await getCategoryList()
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

// bluk delete 
const blukDelete = async () => {
  try {
    const input = {
            ids: bulkDeleteIds.value,
        };

    const { data } = await axios.post('admin/category/bulk-delete',input)
    
    if(data){
      isDeleteDisabled.value = false;
      isBulkDeleteBox.value = false;
      selected.value = [];
      bulkDeleteIds.value = [];
      await getCategoryList();
      toast.success('Category Delete Successfully..!')
    }
  } catch (e) {
    console.log(e);
  }
}

// status change
const stausChange = async (id:number) => {
    try {
       const { data } = await axios.get(`admin/category/status-change/${id}`)
       if(data){
         toast.success(data.message)
         await getCategoryList();
       }
     } catch (e) {
        toast.error(e.response.data.message)
       console.log(e);
     }
}


const allSelect = (i: boolean) => {

if (i) {
  if (categoryList.value && Array.isArray(categoryList.value) && categoryList.value.length) {
    selected.value = []
    categoryList.value.forEach((obj: any) => {
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
    EditCategory(item.id)
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


onMounted(() => {
    getCategoryList()
})

watch(
async () => currentPage.value,
async (val) => {
  if (await val){
      await getCategoryList();
  } 
}
);

watch(
async () => perPage.value,
async (val) => {
  if (await val) {
    currentPage.value = 1;
    await getCategoryList();
  }
}
);

watch (async () => selected.value,
  async (val)=>{
    
    if(await val){
      isDeleteDisabled.value = true
      bulkDeleteIds.value = [];
      selected.value.forEach((id: any) => {
        
        const matchingDeleteObject = categoryList.value.find(
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
      title: 'Laravel-Vue | Category List',
    });
</script>
<template>
  <div>
    <VCard title="Category List">
      <VCardText>
        <VCardText>
          <VRow>
            <VCol cols="12" class="justify-end">
              <AppTextField v-model="search" class="error-custom search-input w-25 ms-auto" placeholder="Search"
                @input="searchCategory(search)" />
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

                      <VBtn class="mr-0" color="primary" :disabled="isDeleteDisabled" @click="isBulkDeleteBox=true">
                        Bulk Delete
                      </VBtn>

                      <VBtn class="mr-0" color="primary" @click="
                                                (addEditCategoryData = null),
                                                (isEdit = false),
                                                (isAddNewCategoryDrawerVisible = true)">
                        Add Category
                      </VBtn>
                    </div>
                  </div>
                </div>
              </div>
            </VCol>
          </VRow>
        </VCardText>
        <div v-if="!isEmptyArray(categoryList)">
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
              <tr v-for="(item,index) in categoryList" :key="item.id">
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
                  {{item.name??' - '}}
                </td>
                <td>
                  <VSwitch :model-value=" item.is_active === 1 ? true : false " @click="stausChange(item.id)" />
                </td>
                <td>
                  {{ item.products_count??0}}
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

        <div v-if="!isEmptyArray(categoryList)" class="d-flex mx-7 mt-5">
          <VRow v-if="!isEmptyArray(categoryList)">
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
    <VDialog v-model="isConfirmDialog" persistent class="v-dialog-sm">
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
          <VBtn @click="deleteCategory()" color="success">
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

    <AddEditCategoryDialog :category-data="addEditCategoryData" :is-drawer-open="isAddNewCategoryDrawerVisible"
      :is-edit="isEdit" @close-dialog="isAddNewCategoryDrawerVisible = false" @category-data="AddNewCategory" />

  </div>
</template>