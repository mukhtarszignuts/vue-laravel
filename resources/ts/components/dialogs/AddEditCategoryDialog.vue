<script lang="ts" setup>
import { requiredValidator } from "@/@core/utils/validators";
import { VForm } from "vuetify/components/VForm";
// interface 
interface CategoryData{
    id?:number,
    name?:string,
    is_active?:boolean,
    display_order?:number,
}
// Interface
interface Emit {
  (e: "update:isDrawerOpen", value: boolean): void;
  (e: "categoryData", value: any): void;
  (e: "closeDialog", value: boolean): void;
}
interface Props {
  isDrawerOpen?: boolean;
  categoryData?: CategoryData | any;
  isEdit?: boolean;
  isLoading?: boolean;
  title?: string;
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

const title = ref<string>(' Category Details')

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
                isUpdate: props?.categoryData?.id ? true : null,
            };
            emit("categoryData", emitObj);
            refForm.value?.reset();
        }
    })
}

watchEffect(() => {
  if (props?.categoryData?.id) {
    id.value = props?.categoryData?.id;
    name.value = props?.categoryData?.name;
    is_active.value = props?.categoryData?.is_active;
    display_order.value = props?.categoryData?.display_order;
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
        <VCard :title="title + ' Category'">

            <VForm ref="refForm" v-model="isFormValid" @submit.prevent="onSubmit">
                <VCardText>
                    <VRow>
                        <VCol cols="12">
                            <AppTextField v-model="name" label="Name" :rules="[requiredValidator(name, 'Name')]" />
                        </VCol>

                        <VCol cols="12">
                            <AppTextField v-model="display_order" label="Display Order" type="number" />
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
