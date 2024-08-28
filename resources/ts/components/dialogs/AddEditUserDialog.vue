<script lang="ts" setup>
import {
  emailValidator,
  lengthValidator,
  requiredValidator,
} from "@/@core/utils/validators";
import SelectBox from "@/components/SelectBox.vue";
import avatar1 from "@images/avatars/avatar-1.png";
import { VForm } from "vuetify/components/VForm";
// interface
interface UserData {
  id?: number;
  name?: string;
  email?: string;
  phone?: string;
  status?: string;
  role?: string;
  image?: string;
  company_id?: number;
}
interface CompanyData {
  id?: number;
  name?: string;
}
// Interface
interface Emit {
  (e: "update:isDrawerOpen", value: boolean): void;
  (e: "userData", value: any): void;
  (e: "closeDialog", value: boolean): void;
}
interface Props {
  isDrawerOpen?: boolean;
  status?: object[];
  roles?: object[];
  userData?: UserData | any;
  compaines?: CompanyData | any;
  isEdit?: boolean;
  isLoading?: boolean;
  title?: string;
  companyId?: number;
}
// Props
const props = defineProps<Props>();

// Emit
const emit = defineEmits<Emit>();

const isFormValid = ref(false);
const refForm = ref<VForm>();
const id = ref<number>();
const company_id = ref<number>();
const name = ref<string>("");
const email = ref<string>("");
const phone = ref<string>("");
const role = ref<string>("");
const status = ref<string>("");
const city = ref<string>("");
const title = ref<string>(" User Profile");
const image = ref<any>("");
const preview = ref<string>(avatar1);
const companySearch = ref("");

// 👉 drawer close
const closeNavigationDrawer = () => {
  nextTick(() => {
    refForm.value?.reset();
    refForm.value?.resetValidation();
  });
  emit("closeDialog", false);
};
const refInputEl = ref<HTMLElement>();

const changeAvatar = (event: Event) => {
  const fileInput = event.target as HTMLInputElement;
  const files = fileInput.files;

  if (files && files.length > 0) {
    const fileReader = new FileReader();

    fileReader.onload = () => {
      if (typeof fileReader.result === "string") {
        preview.value = fileReader.result;
        image.value = files[0];
      }
    };

    fileReader.readAsDataURL(files[0]);
  }
};

const searchCompany = (val: any) => {
  companySearch.value = val;
};

const companyData = computed(() => {
  if (companySearch.value) {
    // get data except existing
    const categoryData =
      props.compaines?.filter((item: any) => {
        return item.name
          .toLowerCase()
          .includes(companySearch.value.toLowerCase());
      }) || [];
    return categoryData;
  } else {
    return props.compaines;
  }
});

// reset avatar image
const resetAvatar = () => {
  preview.value = avatar1;
};

const onSubmit = () => {
  refForm.value?.validate().then(({ valid }) => {
    if (valid) {
      const emitObj = {
        id: id?.value,
        name: name?.value,
        role: role?.value,
        status: status?.value,
        phone: phone?.value,
        city: city?.value,
        email: email?.value,
        image: image?.value,
        company_id: company_id?.value,
        isUpdate: props?.userData?.id ? true : null,
      };

      console.log("userData", emitObj);

      emit("userData", emitObj);
      refForm.value?.reset();
    }
  });
};

watchEffect(() => {
  if (props?.userData?.id) {
    id.value = props?.userData?.id;
    name.value = props?.userData?.name;
    email.value = props?.userData?.email;
    phone.value = props?.userData?.phone;
    role.value = props?.userData?.role;
    status.value = props?.userData?.status;
    city.value = props?.userData?.city;
    company_id.value = props?.userData?.company_id;
    preview.value = props?.userData?.image_url;
  }
});

watch(
  async () => props?.isEdit,
  async (val) => {
    title.value = props?.isEdit ? " Edit " : " Add ";
  }
);
watch(
  async () => props?.companyId,
  async (val) => {
    company_id.value = props?.companyId;
  }
);

onMounted(() => {
  title.value = props?.isEdit ? " Edit " : " Add ";
  company_id.value = props?.companyId;
})
</script>

<template>
  <VDialog
    v-model="props.isDrawerOpen"
    max-width="600"
    @update:is-drawer-open="emit('closeDialog', false)"
  >
    <!-- Dialog Content -->
    <VCard :title="title + ' User Profile'">
      <VCardText class="d-flex">
        <!-- 👉 Avatar -->
        <VAvatar rounded size="100" class="me-6" :image="preview" />

        <!-- 👉 Upload Photo -->
        <div class="d-flex flex-column justify-center gap-4">
          <div class="d-flex flex-wrap gap-2">
            <VBtn color="primary" @click="refInputEl?.click()">
              <VIcon icon="tabler-cloud-upload" class="d-sm-none" />
              <span class="d-none d-sm-block">Upload new photo</span>
            </VBtn>

            <input
              ref="refInputEl"
              type="file"
              name="file"
              accept=".jpeg,.png,.jpg,GIF"
              hidden
              @input="changeAvatar"
            />

            <VBtn
              type="reset"
              color="secondary"
              variant="tonal"
              @click="resetAvatar"
            >
              <span class="d-none d-sm-block">Reset</span>
              <VIcon icon="tabler-refresh" class="d-sm-none" />
            </VBtn>
          </div>

          <p class="text-body-1 mb-0">
            Allowed JPG, GIF or PNG. Max size of 800K
          </p>
        </div>
      </VCardText>
      <VDivider />

      <VForm ref="refForm" v-model="isFormValid" @submit.prevent="onSubmit">
        <VCardText>
          <VRow>
            <VCol cols="12">
              <label>Company</label>
              <SelectBox
                v-model="company_id"
                :readonly="props?.companyId ? true : false"
                :items="companyData"
                item-title="name"
                item-value="id"
                persistent-hint
                class="pt-1 error-custom"
                :multiple="false"
                @search-data="searchCompany"
              />
            </VCol>
            <VCol cols="12">
              <AppTextField
                v-model="name"
                label="Name"
                :rules="[requiredValidator(name, 'Name')]"
              />
            </VCol>
            <VCol cols="12">
              <AppTextField
                v-model="email"
                type="email"
                label="Email"
                :rules="[
                  requiredValidator(email, 'Email'),
                  emailValidator,
                  lengthValidator(email, 0, 128, 'Email'),
                ]"
              />
            </VCol>
            <VCol cols="12">
              <AppTextField
                v-model="phone"
                label="Phone"
                :rules="[requiredValidator(phone, 'Phone')]"
              />
            </VCol>
            <VCol cols="12">
              <AppTextField v-model="city" label="City" type="text" />
            </VCol>

            <VCol cols="6">
              <AppSelect
                v-model="role"
                label="Select Role"
                :items="props.roles"
                item-title="name"
                item-value="id"
                placeholder="Select Role"
                clearable
                :rules="[requiredValidator(role, 'role')]"
              />
            </VCol>
            <VCol cols="6">
              <AppSelect
                v-model="status"
                label="Select Status"
                :items="props.status"
                item-title="name"
                item-value="id"
                placeholder="Select Status"
                clearable
                :rules="[requiredValidator(status, 'status')]"
              />
            </VCol>
          </VRow>
        </VCardText>

        <VCardText class="d-flex justify-end flex-wrap gap-2">
          <!-- <VBtn variant=" tonal" color="secondary" @click="emit('closeDialog', false)">
                    Close
                    </VBtn>
                    <VBtn @click="emit('closeDialog', false)">
                        Save
                    </VBtn> -->

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
