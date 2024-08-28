<script lang="ts" setup>
import { requiredValidator } from "@/@core/utils/validators";
import { VForm } from "vuetify/components/VForm";
// interface
interface CompanyData {
  id?: number;
  name?: string;
  is_active?: boolean;
  owner_id?: number;
  employee_size?: number;
  address?: string;
  logo?: string;
  image_url?: string;
}

interface OwnerData {
  id?: number;
  name?: string;
}

// Interface
interface Emit {
  (e: "update:isDrawerOpen", value: boolean): void;
  (e: "companyData", value: any): void;
  (e: "closeDialog", value: boolean): void;
}

interface Props {
  isDrawerOpen?: boolean;
  companyData?: CompanyData | any;
  owners?: OwnerData | any;
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
const id = ref<number>();
const name = ref<string>("");
const address = ref<string>("");
const logo = ref<any>();
const image_url = ref<string>("");
const employee_size = ref<number>();
const owner_id = ref<number>();
const is_active = ref<boolean>(true);
const empSizeData = [10, 25, 50, 75, 100];
const isImageUpdate = ref(false);
const ownerSearch = ref("");

const title = ref<string>("Company Details");

// 👉 drawer close
const closeNavigationDrawer = () => {
  nextTick(() => {
    refForm.value?.reset();
    refForm.value?.resetValidation();
  });
  emit("closeDialog", false);
};

const handleFileChange = (event: any) => {
  // Get the selected file from the input
  const file = event.target.files[0];
  if (file) {
    // Create a URL for the selected file
    const url = URL.createObjectURL(file);
    // Update the image URL for preview
    image_url.value = url;
    isImageUpdate.value = true;
  } else {
    image_url.value = "";
    logo.value = null;
  }
};

const ownerData = computed(() => {
  if (ownerSearch.value) {
    // get data except existing
    const ownerData =
      props.owners?.filter((item: any) => {
        return item.name
          .toLowerCase()
          .includes(ownerSearch.value.toLowerCase());
      }) || [];
    return ownerData;
  } else {
    return props.owners;
  }
});

const searchOwner = (val: any) => {
  ownerSearch.value = val;
};

const onSubmit = () => {
  refForm.value?.validate().then(({ valid }) => {
    if (valid) {
      const emitObj = {
        id: id?.value,
        name: name?.value,
        is_active: is_active?.value == true ? 1 : 0,
        owner_id: owner_id?.value,
        address: address?.value,
        employee_size: employee_size?.value,
        logo: isImageUpdate.value === true ? logo?.value[0] : null,
        isUpdate: props?.companyData?.id ? true : null,
      };
      emit("companyData", emitObj);
      isImageUpdate.value = false; // Clear image after submission
      image_url.value = "";
      // Reset form
      refForm.value?.reset();
    }
  });
};

watchEffect(() => {
  if (props?.companyData?.id) {
    id.value = props?.companyData?.id;
    name.value = props?.companyData?.name;
    is_active.value = props?.companyData?.is_active;
    address.value = props?.companyData?.address;
    // logo.value = props?.companyData?.logo;
    image_url.value = props?.companyData?.image_url;
    employee_size.value = props?.companyData?.employee_size;
    owner_id.value = props?.companyData?.owner_id;
  }
});

watch(
  async () => props?.isEdit,
  async (val) => {
    title.value = props?.isEdit ? " Edit " : " Add ";
  }
);

// Watch for changes in logo to clear the image URL when logo is cleared
watch(logo, (newValue) => {
  if (!newValue) {
    image_url.value = "";
  }
});

onMounted(() => {
  title.value = props?.isEdit ? " Edit " : " Add ";
});
</script>

<template>
  <VDialog
    v-model="props.isDrawerOpen"
    max-width="600"
    @update:is-drawer-open="emit('closeDialog', false)"
  >
    <!-- Dialog close btn -->
    <DialogCloseBtn @click="emit('closeDialog', false)" />
    <!-- Dialog Content -->
    <VCard :title="title + ' Company'">
      <VForm ref="refForm" v-model="isFormValid" @submit.prevent="onSubmit">
        <VCardText>
          <VRow>
            <VCol cols="12">
              <VFileInput
                label="Logo"
                @change="handleFileChange"
                v-model="logo"
                :multiple="false"
                type="file"
                accept="image/*"
                clearable
              />
              <div v-if="image_url" class="preview mt-2">
                <img :src="image_url" alt="Preview" width="120" height="120" />
              </div>
            </VCol>
            <VCol cols="12">
              <AppTextField
                v-model="name"
                label="Name"
                :rules="[requiredValidator(name, 'Name')]"
              />
            </VCol>
            <VCol cols="12">
              <AppTextarea
                v-model="address"
                label="Address"
                :rules="[requiredValidator(address, 'Address')]"
              />
            </VCol>

            <VCol cols="6">
              <AppSelect
                v-model="employee_size"
                :items="empSizeData"
                label="Employee Size"
                :rules="[requiredValidator(employee_size, 'Employee Size')]"
              />
            </VCol>

            <VCol cols="6" v-if="ownerData">
              <label>Company Owner</label>
              <SelectBox
                v-model="owner_id"
                :items="ownerData"
                item-title="name"
                item-value="id"
                persistent-hint
                class="pt-1 error-custom"
                :multiple="false"
                @search-data="searchOwner"
              />
            </VCol>
          </VRow>
        </VCardText>

        <VCardText class="d-flex justify-end flex-wrap gap-2">
          <VBtn v-if="props?.isLoading" loading="white" class="mx-2" />
          <VBtn v-else type="submit" class="mx-2">
            {{ props?.isEdit ? "Save" : "Submit" }}
          </VBtn>
          <VBtn
            type="reset"
            variant="tonal"
            color="secondary"
            class="mx-2"
            @click="closeNavigationDrawer"
          >
            Cancel
          </VBtn>
        </VCardText>
      </VForm>
    </VCard>
  </VDialog>
</template>
