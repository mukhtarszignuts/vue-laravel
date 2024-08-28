<script lang="ts" setup>
import { requiredValidator } from "@/@core/utils/validators";
import { VForm } from "vuetify/components/VForm";
import SelectBox from "../SelectBox.vue";
// interface 
interface CategoryData{
    id?:number,
    name?:string,
    is_active?:boolean,
    display_order?:number,
}
interface SubCategoryData{
    id?:number,
    name?:string,
    is_active?:boolean,
    display_order?:number,
    category_id?:number,
}
// Interface
interface Emit {
  (e: "update:isDrawerOpen", value: boolean): void;
  (e: "subCategoryData", value: any): void;
  (e: "closeDialog", value: boolean): void;
}
interface Props {
  isDrawerOpen?: boolean;
  categories?: CategoryData | any;
  subCategoryData?: SubCategoryData | any;
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

const title = ref<string>('SubCategory Details')
const categorySearch= ref("")


const categoryData = computed(() => {
  if (categorySearch.value) {
    // get data except existing
    const categoryData =
      props.categories?.filter((item) => {
          return item.name
            .toLowerCase()
            .includes(categorySearch.value.toLowerCase());
      }) || [];
      return categoryData
  } else {
    return props.categories;
  }
});

const searchCategory = (val: any) => {
  categorySearch.value = val;
};

// 👉 drawer close
const closeNavigationDrawer = () => {
  nextTick(() => {
    refForm.value?.reset();
    refForm.value?.resetValidation();
  });
  emit("closeDialog", false);

};

const onSubmit = () =>{
    refForm.value?.validate().then(({ valid }) => {
        if (valid) {
            const emitObj = {
                id: id?.value,
                name: name?.value,
                is_active: is_active?.value,
                display_order: display_order?.value,
                category_id: category_id?.value,
                isUpdate: props?.subCategoryData?.id ? true : null,
            };
            emit("subCategoryData", emitObj);
            refForm.value?.reset();
        }
    })
}

watchEffect(() => {
  if (props?.subCategoryData?.id) {
    id.value = props?.subCategoryData?.id;
    name.value = props?.subCategoryData?.name;
    is_active.value = props?.subCategoryData?.is_active;
    display_order.value = props?.subCategoryData?.display_order;
    category_id.value = props?.subCategoryData?.category_id;
  }
})


watch(
  async () => props?.isEdit,
  async (val) => {
    title.value=props?.isEdit?' Edit ':' Add '
  }
);

onMounted(async () => {
    title.value=props?.isEdit?' Edit ':' Add '    
})

</script>

<template>
    <VDialog v-model="props.isDrawerOpen" max-width="600" @update:is-drawer-open="emit('closeDialog', false)">
        <!-- Dialog close btn -->
        <DialogCloseBtn @click="emit('closeDialog', false)" />
        <!-- Dialog Content -->
        <VCard :title="title + ' SubCategory'">

            <VForm ref="refForm" v-model="isFormValid" @submit.prevent="onSubmit">
                <VCardText>
                    <VRow>
                        <VCol cols="12">
                            <AppTextField v-model="name" label="Name" :rules="[requiredValidator(name, 'Name')]" />
                        </VCol>

                        <VCol cols="12">
                            <AppTextField v-model="display_order" label="Display Order" type="number" />
                        </VCol>

                        <VCol cols="12">
                           
                            <label>Category</label>
                            <SelectBox v-model="category_id" :items="categoryData" item-title="name" item-value="id"
                                persistent-hint class="pt-1 error-custom" :multiple="false"
                                @search-data="searchCategory" />
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
