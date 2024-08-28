<script setup lang="ts">
import { isEmptyArray } from "@/@core/utils";
import AddEditProductDialog from "@/components/dialogs/AddEditProductDialog.vue";
import axios from '@/plugins/axios';
import { avatarText } from '@core/utils/formatters';
import noData from '@images/no_data.svg';
import { useHead } from '@vueuse/head';
import moment from 'moment';
import { useRouter } from "vue-router";
import { toast } from 'vue3-toastify';

// interface 

interface ProductData{
    id:number
    name: string
    price:any
    description:string
    display_order:number
    is_active:number
    stock:number
    image:string
    images:any
    image_url:string
    created_at:string
}

interface SubCategoryData{
    id: number
    name: string
}

interface CategoryData{
    id: number
    name: string
}

const router = useRouter();
const isLoading = ref<boolean>(false);
const search = ref<string>();
const isEdit = ref<boolean>(false);
const subCategoryList = ref<SubCategoryData[]>([])
const categoryList = ref<CategoryData[]>([])
const productList = ref<ProductData[]>([])
const count = ref<number>(0);
const perPage = ref<number>(10);
const lengthPage = ref<number>(0);
const currentPage = ref(1);
const sortKey = ref('created_at');
const sortOrder = ref('desc');

const addEditProductData = ref<object | null>({});
const isConfirmDialog = ref<boolean>(false)
const deletedId = ref<number>()
const isAddNewProductDrawerVisible = ref<boolean>(false)
const allSelectedType = ref<boolean>(false)
const selected = ref([]);
const bulkDeleteIds = ref([]);
const isDeleteDisabled= ref<boolean>(true)
const isBulkDeleteBox = ref<boolean>(false)

const bulkStatusIds = ref([]);
const isBulkStatusDisabled= ref<boolean>(true)
const isBulkStatusBox = ref<boolean>(false)

const statusFilter = ref<any>()
    
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
  { title: 'CATEGORY', key: 'category' },
  { title: 'DESCRIPTION', key: 'description' },
  { title: 'PRICE', key: 'price' },
  { title: 'STOCK', key: 'stock' },
  { title: 'STATUS', key: 'is_active' },
  { title: 'CREATED AT', key: 'created_at' },
  { title: 'ACTION', key: 'action' },
];

const statusOptions = ref<any[]>([
 
  {
    id: '1',
    name: "Active",
  },
  {
    id: '0',
    name: "In Active",
  },
 
]);
const ResetFilter = () => {
  search.value = undefined;
  statusFilter.value = undefined;
  getProductList();
};

// search user
const searchProduct = async (data:any) => {
  if (data.length > 2 || data.length === 0) {
    currentPage.value = 1;
    await getProductList();
  }
};

// get Product list 
const getProductList = async() =>{
    try {
        const input = {
            sortKey: sortKey.value,
            sortOrder: sortOrder.value,
            page: currentPage.value,
            per_page: perPage.value,
            search: search.value,
            status: statusFilter.value,
        };

        const { data } = await axios.post('admin/product/list',input)
        
        if(data){
            productList.value=data.data.products
            count.value=data.data.count
            lengthPage.value = Math.ceil(data.data.count / perPage.value);
        } 
    } catch (e) {
        console.log(e);
        toast.error(e.response.data.message)
    }
}

// get Category list 
const getCategoryList = async()=>{
    try {
        const input = {
            sortKey: sortKey.value,
            sortOrder: sortOrder.value,
        };

        const { data } = await axios.post('admin/category/list',input)
        
        if(data){
            categoryList.value=data.data.category
        } 
    } catch (e) {
        console.log(e);
        toast.error(e.response.data.message)
    }
}
// get SubCategory list 
const getSubCategoryList = async()=>{
    try {
        const input = {
            sortKey: sortKey.value,
            sortOrder: sortOrder.value,
        };

        const { data } = await axios.post('admin/subcategory/list',input)
        
        if(data){
            subCategoryList.value=data.data.subcategory
        } 
    } catch (e) {
        console.log(e);
        toast.error(e.response.data.message)
    }
}

// 👉 Add New Product
const AddNewProduct = async (productData: any) => {
  try {
    isLoading.value = true;
     
    const input = {
      name: productData.name,
      description: productData.description,
      is_active: productData.is_active,
      display_order: productData.display_order,
      price: productData.price,
      stock: productData.stock,
    //   image: productData.image,
    //   images: productData.images,
      category_id: productData.category_id,
      sub_category_id: productData.sub_category_id,
      id: productData.isUpdate == true ? productData.id : null,
    };

    const formData = new FormData();

     // Append non-file data to FormData
     Object.keys(input).forEach(key => {
      if (input[key] !== null && input[key] !== undefined) {
        formData.append(key, input[key]);
      }
    });
    
     
     // Append images if they exist
     if (productData.images && productData.images.length > 0) {
         productData.images.forEach((item: any, index: number) => {
             formData.append(`images[${index}]`, item.file);
      });
    }

    // console.log('formData',formData);
    
    let response;
    if (productData.isUpdate) {
      const { data } = await axios.post("admin/product/update", formData,{
        headers: {
            'Content-Type': 'multipart/form-data'
          }
      });
      response = data;
    } else {
      const { data } = await axios.post("admin/product/create", formData,{
        headers: {
            'Content-Type': 'multipart/form-data'
          }
      });
       response = data;  
    }
    if (response) {
        toast.success(response.message)
        isAddNewProductDrawerVisible.value = false;
        getProductList();
        isLoading.value = false;
    }else{
      isAddNewProductDrawerVisible.value = false;
      isLoading.value = false;
    }
  } catch (e) {
    console.log(e);
    toast.error(e.response.data.message);
    isLoading.value = false;
  }
};

// Edit Product
const EditProduct = async (id:number) => {
    try {
       
      const { data } = await axios.get(`admin/product/${id}`)

      if(data){
        
        addEditProductData.value = data.data.product
        isEdit.value = true,
        isAddNewProductDrawerVisible.value = true
      }
    } catch (e) {
      console.log(e);
    }
}

// delete product 
const deleteProduct = async () => {
    try {
        isLoading.value = true
        const path = `/admin/product/delete/${deletedId.value}`;

        const { data } = await axios.get(`${path}`);
        
        if(data){
            toast.success(`Product Delete Successfully...!`)
            await getProductList()
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

    const { data } = await axios.post('admin/product/bulk-delete',input)
    
    if(data){
      isDeleteDisabled.value = false;
      isBulkDeleteBox.value = false;
      selected.value = [];
      bulkDeleteIds.value = [];
      await getProductList();
      toast.success('SubCategory Delete Successfully..!')
    }
  } catch (e) {
    console.log(e);
  }
}

// bluk delete 
const blukStatus = async (status:boolean) => {
  try {
    //! We Need to Change Status Dynamic 
    const input = {
            ids: bulkStatusIds.value,
            status:status
        };

    const { data } = await axios.post('admin/product/bulk-status',input)
    
    if(data){        
      isBulkStatusDisabled.value = false;
      isBulkStatusBox.value = false;
      selected.value = [];
      bulkStatusIds.value = [];
      await getProductList();
      toast.success(data.message)
    }
  } catch (e) {
    console.log(e);
  }
}

// status change
const stausChange = async (id:number) => {
    try {
       const { data } = await axios.get(`admin/product/status-change/${id}`)
       if(data){
         toast.success(data.message)
         await getProductList();
       }
     } catch (e) {
        toast.error(e.response.data.message)
       console.log(e);
     }
}

const allSelect = (i: boolean) => {

if (i) {
  if (productList.value && Array.isArray(productList.value) && productList.value.length) {
    selected.value = []
    productList.value.forEach((obj: any) => {
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
    title: "View",
    id: "1",
    prependIcon: "tabler-eye",
    action: "View",
    show: true,
  },
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
if(action==='View'){
    // router.push({
    //   path: `/admin/products/${item.id}`,
    // });
}else if (action === "Edit") {
    EditProduct(item.id)
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
    getProductList()
    getCategoryList()
    getSubCategoryList()
})

watch(
async () => currentPage.value,
async (val) => {
  if (await val){
      await getProductList();
  } 
}
);

watch(async () => perPage.value,
async (val) => {
  if (await val) {
    currentPage.value = 1;
    await getProductList();
  }
}
);

watch (async () => selected.value,
  async (val)=>{
    
    if(await val){
        // bulk delete
      isDeleteDisabled.value = true
      // bulk status 
      isBulkStatusDisabled.value = true
      bulkDeleteIds.value = [];

      bulkStatusIds.value = [];
      selected.value.forEach((id: any) => {
        
        const matchingDeleteObject = productList.value.find(
            (obj: any) => obj.id === id 
        );

        if(matchingDeleteObject){
          isDeleteDisabled.value = false
          isBulkStatusDisabled.value = false
          bulkDeleteIds.value.push(matchingDeleteObject.id);
          bulkStatusIds.value.push(matchingDeleteObject.id);
        }
      });
    }
})

watch(
  async () => statusFilter.value,
  async (val) => {
    console.log('statusFilter',statusFilter.value)
    currentPage.value = 1;
    await getProductList();
  });
useHead({
    title: 'Laravel-Vue | Product list',
});
</script>

<template>
    <div>
        <VCard title="Product List">
            <VCardText>
                <VCardText>
                    <VRow>
                        <VCol cols="12" class="justify-end">
                            <AppTextField v-model="search" class="error-custom search-input w-25 ms-auto"
                                placeholder="Search" @input="searchProduct(search)" />
                        </VCol>
                    </VRow>
                    <VRow class="px-3">
                        <VCol lg="3" md="3" sm="12" cols="12">
                            <div class="d-flex align-center filter-box-width">
                                Show
                                <div>
                                    <AppSelect v-model="perPage" :items="pageCountList" item-value="value"
                                        item-title="label" class="mx-2" />
                                </div>
                                Entries
                            </div>
                        </VCol>
                        <VCol md="9" lg="9" sm="12" cols="12">
                            <div class="d-flex gap-3 common-filter justify-end">
                                <div class="d-flex common-filter gap-3">
                                    <AppSelect v-model="statusFilter" :items="statusOptions" item-title="name"
                                        item-value="id" placeholder="Filter by Status" class="w-5" clearable />
                                    <div class="gap-3 d-flex flex-column">
                                        <div class="d-flex gap-3 flex-wrap">
                                            <VBtn :disabled="!statusFilter" @click="ResetFilter">
                                                Reset Filter
                                            </VBtn>
                                            <VBtn class="mr-0" color="primary" :disabled="isBulkStatusDisabled"
                                                @click="isBulkStatusBox=true">
                                                Bulk Status Manage
                                            </VBtn>
                                            <VBtn class="mr-0" color="primary" :disabled="isDeleteDisabled"
                                                @click="isBulkDeleteBox=true">
                                                Bulk Delete
                                            </VBtn>
                                            <VBtn class="mr-0" color="primary" @click="
                                                (addEditProductData = null),
                                                (isEdit = false),
                                                (isAddNewProductDrawerVisible = true)">
                                                Add Product
                                            </VBtn>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </VCol>
                    </VRow>
                </VCardText>
                <div v-if="!isEmptyArray(productList)">
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
                            <tr v-for="(item,index) in productList" :key="item.id">
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
                                        <VAvatar size="38" :variant="!item.image ? 'tonal' : undefined"
                                            :color="'secondary'" class="me-3">
                                            <VImg v-if="item.image_url" :src="item.image_url" />
                                            <span v-else>{{ avatarText(item.name) }}</span>
                                        </VAvatar>
                                        <div class="d-flex flex-column">
                                            <h6 class="text-body-1 font-weight-medium">
                                                <RouterLink :to="'/'" class="user-list-name">
                                                    {{ item.name }}
                                                </RouterLink>
                                            </h6>
                                            <!-- <span class="text-sm text-disabled">{{ item.name }}</span> -->
                                        </div>
                                    </div>
                                    <!-- {{item.name??' - '}} -->
                                </td>
                                <td>
                                    {{item.category?.name??' --- '}}
                                </td>
                                <td>
                                    <!-- {{item.description??' --- '}} -->
                                    <div>
                                        <VTooltip max-width="500px" v-if="item.description.length > 20"
                                            activator="parent" location="top" open-delay="500"
                                            :text="item.description" />
                                        {{
                                        item.description
                                        ? item.description.length > 20
                                        ? `${item.description.slice(0, 20)}...`
                                        : item.description
                                        : "--"
                                        }}
                                    </div>
                                </td>
                                <td>
                                    {{item.price??' --- '}}
                                </td>
                                <td>
                                    {{item.stock??'0'}}
                                </td>
                                <td>
                                    <VSwitch :model-value=" item.is_active === 1 ? true : false "
                                        @click="stausChange(item.id)" />
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
                                                    <VList
                                                        v-for="i in actionList(item).filter(action => action.show !== false)"
                                                        :key="i?.id" class="pa-1">
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

                <div v-if="!isEmptyArray(subCategoryList)" class="d-flex mx-7 mt-5">
                    <VRow v-if="!isEmptyArray(subCategoryList)">
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
                    <VBtn @click="deleteProduct()" color="success">
                        Yes
                    </VBtn>
                </VCardText>
            </VCard>
        </VDialog>

        <!-- Bluk Delete Product  -->
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

        <!-- Bluk Status  Product  -->
        <VDialog v-model="isBulkStatusBox" persistent class="v-dialog-sm">
            <!-- Dialog close btn -->
            <DialogCloseBtn @click="isBulkStatusBox = !isBulkStatusBox" />
            <!-- Dialog Content -->
            <VCard>

                <VCardText>
                    <div class="text-center mt-5">
                        <h2>Confirmation Required?</h2>
                        <p class="mt-4 w-75 text-center" style="margin-left: 70px;">
                            Are You Sure You Want to Change Status ?
                        </p>
                    </div>
                </VCardText>
                <VCardText class="d-flex justify-center gap-3 flex-wrap">
                    <VBtn color="error" variant="tonal" @click="blukStatus(false)">
                        Reject
                    </VBtn>
                    <VBtn @click="blukStatus(true)" color="success">
                        Approve
                    </VBtn>
                </VCardText>
            </VCard>
        </VDialog>

        <AddEditProductDialog :product-data="addEditProductData" :is-drawer-open="isAddNewProductDrawerVisible"
            :is-edit="isEdit" :categories="categoryList" :sub_categories="subCategoryList"
            @close-dialog="isAddNewProductDrawerVisible = false" @product-data="AddNewProduct" />

    </div>
</template>
<style lang="scss">
.common-filter .app-select.flex-grow-1 {
    min-width: 160px !important;
    max-width: 160px !important;
}
</style>