<script lang="ts" setup>
import { requiredValidator } from "@/@core/utils/validators";
import axios from "@/plugins/axios";
import { toast } from "vue3-toastify";
import { VForm } from "vuetify/components/VForm";
import SelectBox from "../SelectBox.vue";
import ImageUploder from "../ImageUploader.vue"

// interface 
interface ProductData{
    id?:any,
    name?:string,
    description?:string,
    price?:any,
    is_active?:any,
    stock?:any,
    display_order?:any,
    category:any,
    subcategory:any,
    image:string,
    images:any,
}

interface CategoryData{
    id?:number,
    name?:string,
}
interface SubCategoryData{
    id?:number,
    name?:string,
    category_id?:number,
}
// Interface
interface Emit {
  (e: "update:isDrawerOpen", value: boolean): void;
  (e: "productData", value: any): void;
  (e: "closeDialog", value: boolean): void;
}
interface Props {
  isDrawerOpen?: boolean;
  categories?: CategoryData | any;
  sub_categories?: SubCategoryData | any;
  productData?: ProductData | any;
  isEdit?: boolean;
  isLoading?: boolean;
}
// Props
const props = defineProps<Props>();

// Emit
const emit = defineEmits<Emit>();

const isFormValid = ref(false);
const refForm = ref<VForm>();
const id = ref<number>()
const name = ref<string>('')
const is_active = ref<boolean>(true)
const display_order = ref<number>(0)
const category_id = ref<number>()
const sub_category_id = ref<number>()
const stock = ref<number>(0)
const price = ref<any>()
const description = ref<string>()
const preimages = ref<any>([])
const imageUrl = ref<string>()
const isImageUpdate = ref(false);

const title = ref<string>('Product Details')
const categorySearch= ref("")
const subCategorySearch= ref("")

const images = ref([]);
const fileInput = ref(null);


const categoryData = computed(() => {
  if (categorySearch.value) {
    // get data except existing
    const categoryData =
      props.categories?.filter((item:any) => {
          return item.name
            .toLowerCase()
            .includes(categorySearch.value.toLowerCase());
      }) || [];
      return categoryData
  } else {
    return props.categories;
  }
});

const subCategoryData = computed(() => {
  if (subCategorySearch.value) {
    // get data except existing
    const subCategoryData =
      props.sub_categories?.filter((item:any) => {
          return item.name
            .toLowerCase()
            .includes(subCategorySearch.value.toLowerCase());
      }) || [];
      return subCategoryData
  } else {
    return props.sub_categories;
  }
});

const searchCategory = (val: any) => {
  categorySearch.value = val;
};
const searchSubCategory = (val: any) => {
  subCategorySearch.value = val;
};

const deleteImage = async (index:any,image:any) => {
  try {
        
    if(image.id){
      const input = {
        id:image.id,
        product_id:image.product_id,
      }
      const { data } = await axios.post('admin/product/image-delete',input);
      if(data){
        console.log(data);
        
        toast.success(data.message)
      }
    }
    preimages.value.splice(index, 1);
    
  } catch (e) {
    console.error(e);
  }
  
}

// 👉 drawer close
const closeNavigationDrawer = () => {
  nextTick(() => {
    refForm.value?.reset();
    refForm.value?.resetValidation();
  });
  emit("closeDialog", false);

};
const handleDrop = (event:any) => {
      const files = event.dataTransfer.files;
      handleFiles(files);
    };

    const handleFileSelect = (event:any) => {
      const files = event.target.files;
      handleFiles(files);
    };

    const handleFiles = (files:any) => {
      Array.from(files).forEach(file => {
       
          if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = (e) => {
              images.value.push({ url: e.target.result, file });
              preimages.value.push({ image_url: e.target.result, file });
              isImageUpdate.value=true
              console.log('files',files.length);
              
            };
            reader.readAsDataURL(file);
            // Optionally upload the file
            // uploadFile(file);
          }
        
      });
    };

  


// const handleFileChange = (event:any) => {
//   // Get the selected file from the input
//   const file = event.target.files[0];
//   if (file) {
//     // Create a URL for the selected file
//     const url = URL.createObjectURL(file);
//     // Update the image URL for preview
//     imageUrl.value = url;
//     isImageUpdate.value=true
    
//   }
// };

const onSubmit = () =>{
    refForm.value?.validate().then(({ valid }) => {
        if (valid) {
            const emitObj = {
                id: id?.value,
                name: name?.value,
                description: description?.value,
                price: price?.value,
                stock: stock?.value,
                // image: isImageUpdate.value===true?image?.value[0]:null,
                is_active: is_active?.value==true?1:0,
                display_order: display_order?.value,
                category_id: category_id?.value,
                sub_category_id: sub_category_id?.value,
                isUpdate: props?.productData?.id ? true : null,
                images:isImageUpdate.value==true?images?.value:null
            };
            console.log('productdata',emitObj);
                                  
            emit('productData', emitObj);

            isImageUpdate.value = false; // Clear image after submission
            preimages.value = []
            // Reset form
            refForm.value?.reset();
        }
    })
}

watchEffect(() => {
  if (props?.productData?.id) {
      id.value              = props?.productData?.id;
      name.value            = props?.productData?.name;
      description.value     = props?.productData?.description;
      price.value           = props?.productData?.price;
      stock.value           = props?.productData?.stock;
      preimages.value       = props?.productData.images;
      is_active.value       = props?.productData?.is_active;
      display_order.value   = props?.productData?.display_order;
      category_id.value     = props?.productData?.category_id;
      sub_category_id.value = props?.productData?.sub_category_id;
      imageUrl.value        = props?.productData?.image_url; // Need to check 
  }
})


watch(
  async () => props?.isEdit,
  async (val) => {
    title.value=props?.isEdit?' Edit Product Details':' Add Product Details'
  }
);

onMounted(async () => {
    title.value=props?.isEdit?' Edit Product Details':' Add Product Details'    
})

</script>

<template>
  <VDialog v-model="props.isDrawerOpen" max-width="600" @update:is-drawer-open="emit('closeDialog', false)">
    <!-- Dialog close btn -->
    <DialogCloseBtn @click="emit('closeDialog', false)" />
    <!-- Dialog Content -->
    <VCard :title="title">

      <VForm ref="refForm" v-model="isFormValid" enctype="multipart/form-data" @submit.prevent="onSubmit">
        <VCardText>
          <VRow>

            <!-- <VCol cols="12">
             <ImageUploder />
            </VCol> -->
            <VCol cols="12">
             
              <div class="dropzone" @dragover.prevent @drop="handleDrop" @click="fileInput.click()">
                <input type="file" ref="fileInput" accept="image/*" multiple style="display: none"
                  @change="handleFileSelect" />
                <p v-if="!preimages.length">Drag & drop images here or click to select</p>
                <div v-if="preimages.length" class="image-preview">
                  <div v-for="(image, index) in preimages" :key="index" class="image-container">
                    <img :src="image.image_url" alt="" />
                    <button type="button" class="delete-button" @click.stop="deleteImage(index,image)">x</button>
                  </div>
                </div>
              </div>

            </VCol>
            <VCol cols="12">
              <AppTextField v-model="name" label="Name" :rules="[requiredValidator(name, 'Name')]" />
            </VCol>

            <VCol cols="4">
              <AppTextField v-model="display_order" label="Display Order" type="number" />
            </VCol>
            <VCol cols="4">
              <AppTextField v-model="stock" label="Stock" type="number" />
            </VCol>
            <VCol cols="4">
              <AppTextField v-model="price" label="Price" type="number" />
            </VCol>

            <VCol cols="12">
              <label>Category</label>
              <SelectBox v-model="category_id" :items="categoryData" item-title="name" item-value="id" persistent-hint
                class="pt-1 error-custom" :multiple="false" @search-data="searchCategory" />
            </VCol>

            <VCol cols="12">
              <label>SubCategory</label>
              <SelectBox v-model="sub_category_id" :items="subCategoryData" item-title="name" item-value="id"
                persistent-hint class="pt-1 error-custom" :multiple="false" @search-data="searchSubCategory" />
            </VCol>

            <VCol cols="12">
              <AppTextarea v-model="description" label="Description"
                :rules="[requiredValidator(description, 'Description')]" />
            </VCol>
          </VRow>

        </VCardText>

        <VCardText class="d-flex justify-end flex-wrap gap-2">
          <VBtn v-if="props?.isLoading" loading="white" class="mx-2" />
          <VBtn v-else type="submit" class="mx-2">
            {{ props?.isEdit?'Save':'Submit' }}
          </VBtn>
          <VBtn type="reset" variant="tonal" color="secondary" class="mx-2" @click="closeNavigationDrawer">
            Cancel
          </VBtn>
        </VCardText>
      </VForm>
    </VCard>
  </VDialog>
</template>

<style scoped>
/* .dropzone {
  border: 2px dashed #ccc;
  padding: 20px;
  text-align: center;
  cursor: pointer;
}

.image-preview {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.image-preview img {
  max-width: 100px;
  max-height: 100px;
  object-fit: cover;
} */



.dropzone {
  border: 2px dashed #ccc;
  padding: 20px;
  text-align: center;
  cursor: pointer;
}

.image-preview {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-top: 10px;
}

.image-container {
  position: relative;
  display: inline-block;
  max-width: 150px;
}

.image-container img {
  max-width: 100px;
  max-height: 100px;
}

.delete-button {
  position: absolute;
  top: 0;
  right: 0;
  background: red;
  color: white;
  border: none;
  border-radius: 50%;
  cursor: pointer;
  padding: 0px 6px;
  font-size: 14px;
}
</style>