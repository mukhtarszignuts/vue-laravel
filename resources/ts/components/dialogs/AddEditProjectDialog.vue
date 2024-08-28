<script lang="ts" setup>
import {
  requiredValidator,
  validateDate,
  validateEndDate,
} from "@/@core/utils/validators";
import useUserData from "@/composable/useFetchUserData";
import axios from "@/plugins/axios";
import { toast } from "vue3-toastify";
import { VForm } from "vuetify/components/VForm";
import SelectBox from "../SelectBox.vue";

// interface
interface ProjectData {
  id?: any;
  name?: string;
  client_id?: number;
  company_id?: number;
  description?: string;
  total_budget?: number;
  budget?: number;
  start_date?: string;
  end_date?: string;
  status?: string;
  type: string;
  location: string;
  thumbnil: string;
  images: any;
}

interface ClientData {
  id?: number;
  name?: string;
}

interface CompanyData {
  id?: number;
  name?: string;
}

// Interface
interface Emit {
  (e: "update:isDrawerOpen", value: boolean): void;
  (e: "projectData", value: any): void;
  (e: "closeDialog", value: boolean): void;
}

interface Props {
  isDrawerOpen?: boolean;
  clients?: ClientData | any;
  projectData?: ProjectData | any;
  companies?: CompanyData | any;
  types: any;
  status: any;
  isEdit?: boolean;
  isLoading?: boolean;
}
// Props
const props = defineProps<Props>();

// Emit
const emit = defineEmits<Emit>();

const { fetchCompanyId } = useUserData();
const isFormValid = ref(false);
const refForm = ref<VForm>();
const id = ref<number>();
const name = ref<string>("");
const location = ref<string>();
const client_id = ref<number>();
const company_id = ref<number | any>();
const total_budget = ref<any>();
const budget = ref<number>();
const type = ref<any>();
const status = ref<any>();
const start_date = ref<string | null>(null);
const end_date = ref<string | null>(null);
const description = ref<string>();
const preimages = ref<any>([]);
const imageUrl = ref<string>();
const isImageUpdate = ref(false);
const today = new Date().toISOString().split("T")[0]; // Format as 'YYYY-MM-DD'
const title = ref<string>("Project Details");
const clientSearch = ref("");
const companySearch = ref("");


const images = ref([]);
const fileInput = ref(null);

const clientData = computed(() => {
  if (clientSearch.value) {
    // get data except existing
    const clientData =
      props.clients?.filter((item: any) => {
        return item.name
          .toLowerCase()
          .includes(clientSearch.value.toLowerCase());
      }) || [];
    return clientData;
  } else {
    return props.clients;
  }
});

const companyData = computed(() => {
  if (companySearch.value) {
    // get data except existing
    const companyData =
      props.companies?.filter((item: any) => {
        return item.name
          .toLowerCase()
          .includes(companySearch.value.toLowerCase());
      }) || [];
    return companyData;
  } else {
    return props.companies;
  }
});

// Config for start_date picker
const startDateConfig = computed(() => ({
  dateFormat: "Y-m-d",
  // minDate: today, // Disable dates before today
}));

// Config for end_date picker
const endDateConfig = computed(() => ({
  dateFormat: "Y-m-d",
  minDate: start_date.value, // Disable dates before start_date or today
}));

const endDateRules = computed(() => {
  return (value: any) => validateEndDate(start_date.value, value);
});

const startDateRules = computed(() => {
  return (value: any) => validateDate(value);
});

const searchClient = (val: any) => {
  clientSearch.value = val;
};

const searchCompany = (val: any) => {
  companySearch.value = val;
};

const deleteImage = async (index: any, image: any) => {
  try {
    if (image.id) {
      const input = {
        id: image.id,
        product_id: image.product_id,
      };
      const { data } = await axios.post("admin/project/image-delete", input);
      if (data) {
        toast.success(data.message);
      }
    }
    preimages.value.splice(index, 1);
  } catch (e) {
    console.error(e);
  }
};

// 👉 drawer close
const closeNavigationDrawer = () => {
  nextTick(() => {
    refForm.value?.reset();
    refForm.value?.resetValidation();
  });
  emit("closeDialog", false);
};

const handleDrop = (event: any) => {
  const files = event.dataTransfer.files;
  handleFiles(files);
};

const handleFileSelect = (event: any) => {
  const files = event.target.files;
  handleFiles(files);
};

const handleFiles = (files: any) => {
  Array.from(files).forEach((file) => {
    if (file && file.type.startsWith("image/")) {
      const reader = new FileReader();
      reader.onload = (e) => {
        images.value.push({ url: e.target.result, file });
        preimages.value.push({ image_url: e.target.result, file });
        isImageUpdate.value = true;
      };
      reader.readAsDataURL(file);
    }
  });
};

const onSubmit = () => {
  refForm.value?.validate().then(({ valid }) => {
    if (valid) {
      const emitObj = {
        id: id?.value,
        name: name?.value,
        description: description?.value,
        location: location?.value,
        type: type?.value,
        status: status?.value,
        client_id: client_id?.value,
        company_id: company_id?.value,
        total_budget: total_budget?.value,
        budget: budget?.value,
        start_date: start_date?.value,
        end_date: end_date?.value,
        isUpdate: props?.projectData?.id ? true : null,
        images: isImageUpdate.value == true ? images?.value : null,
        // thumbnil:isImageUpdate.value==true?images?.value:null
      };

      emit("projectData", emitObj);

      isImageUpdate.value = false; // Clear image after submission
      preimages.value = [];
      start_date.value = null;
      end_date.value = null;
      // Reset form
      refForm.value?.reset();
      refForm.value?.resetValidation();
    }
  });
};

watchEffect(() => {
  if (props?.projectData?.id) {
    id.value = props?.projectData?.id;
    name.value = props?.projectData?.name;
    description.value = props?.projectData?.description;
    location.value = props?.projectData?.location;
    total_budget.value = props?.projectData?.total_budget;
    budget.value = props?.projectData?.budget;
    // preimages.value    = props?.projectData.images;
    client_id.value = props?.projectData?.client_id;
    company_id.value = props?.projectData?.company_id;
    start_date.value = props?.projectData?.start_date;
    end_date.value = props?.projectData?.end_date;
    type.value = props?.projectData?.type;
    status.value = props?.projectData?.status;
    imageUrl.value = props?.projectData?.image_url; // Need to check
  }
});

watch(
  async () => props?.isEdit,
  async (val) => {
    title.value = props?.isEdit
      ? " Edit Project Details"
      : " Add Project Details";
  }
);
watch(
  async () => fetchCompanyId,
  async (val) => {
  
    if(fetchCompanyId.value){
      company_id.value = fetchCompanyId.value;
    }
  }
);
onMounted(async () => {
  title.value = props?.isEdit
    ? " Edit Project Details"
    : " Add Project Details";
  
    if(fetchCompanyId.value){
      company_id.value = fetchCompanyId.value;
    }
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
    <VCard :title="title">
      <VForm
        ref="refForm"
        v-model="isFormValid"
        enctype="multipart/form-data"
        @submit.prevent="onSubmit"
      >
        <VCardText>
          <VRow>
            <VCol cols="12">
              <div
                class="dropzone"
                @dragover.prevent
                @drop="handleDrop"
                @click="fileInput.click()"
              >
                <input
                  type="file"
                  ref="fileInput"
                  accept="image/*"
                  multiple
                  style="display: none"
                  @change="handleFileSelect"
                />
                <p v-if="!preimages.length">
                  Drag & drop images here or click to select
                </p>
                <div v-if="preimages.length" class="image-preview">
                  <div
                    v-for="(image, index) in preimages"
                    :key="index"
                    class="image-container"
                  >
                    <img :src="image.image_url" alt="" />
                    <button
                      type="button"
                      class="delete-button"
                      @click.stop="deleteImage(index, image)"
                    >
                      x
                    </button>
                  </div>
                </div>
              </div>
            </VCol>
            <VCol cols="12">
              <AppTextField
                v-model="name"
                label="Name"
                :rules="[requiredValidator(name, 'Name')]"
              />
            </VCol>

            <VCol cols="6">
              <AppSelect
                v-model="type"
                :items="props?.types"
                item-title="name"
                item-value="id"
                placeholder="Select Type"
                label="Type"
                class="w-5"
                clearable
                :rules="[requiredValidator(type, 'Type')]"
              />
            </VCol>
            <VCol cols="6">
              <AppSelect
                v-model="status"
                label="Status"
                :items="props?.status"
                item-title="name"
                item-value="id"
                placeholder="Select Status"
                class="w-5"
                clearable
                :rules="[requiredValidator(status, 'Status')]"
              />
            </VCol>
            <VCol cols="6">
              <AppTextField
                v-model="total_budget"
                label="Total Budget"
                type="number"
                :rules="[requiredValidator(total_budget, 'Total Budget')]"
              />
            </VCol>

            <VCol cols="6">
              <label>Clients</label>
              <SelectBox
                v-model="client_id"
                :items="clientData"
                item-title="name"
                item-value="id"
                persistent-hint
                class="pt-1 error-custom"
                :multiple="false"
                @search-data="searchClient"
                clearable
                :rules="[requiredValidator(client_id, 'Client id')]"
              />
            </VCol>

            <VCol cols="6">
              <AppTextField v-model="budget" label="Budget" type="number" />
            </VCol>

            <VCol cols="6">
              <label>Company</label>
              <SelectBox
                v-model="company_id"
                :readonly="fetchCompanyId ? true : false"
                :items="companyData"
                item-title="name"
                item-value="id"
                persistent-hint
                class="pt-1 error-custom"
                :multiple="false"
                @search-data="searchCompany"
                :clearable="fetchCompanyId ? false : true"
                :rules="[requiredValidator(company_id, 'company id')]"
              />
            </VCol>

            <VCol cols="6">
              <AppDateTimePicker
                v-model="start_date"
                label="Start Date"
                :clearable="true"
                :rules="[startDateRules]"
              />
            </VCol>

            <VCol cols="6">
              <AppDateTimePicker
                v-model="end_date"
                class="custom-date-picker"
                :clearable="true"
                label="End Date"
                :rules="[endDateRules]"
              />
            </VCol>

            <VCol cols="12">
              <AppTextField
                v-model="location"
                label="Location"
                type="text"
                :rules="[requiredValidator(location, 'Location')]"
              />
            </VCol>

            <VCol cols="12">
              <AppTextarea v-model="description" label="Description" />
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

<style>
.custom-date-picker .flatpickr-current-month .numInputWrapper span {
  display: block !important;
}

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
