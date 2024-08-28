<script lang="ts" setup>
import CardStatisticsVertical from "@/components/CardStatisticsVertical.vue";
import CompanyConnection from "@/components/company/CompanyConnection.vue";
import CompanyProfile from "@/components/company/CompanyProfile.vue";
import CompanyProject from "@/components/company/CompanyProject.vue";
import AddEditCompanyDialog from "@/components/dialogs/AddEditCompanyDialog.vue";
import useUserData from "@/composable/useFetchUserData";
import axios from "@/plugins/axios";
import headerBG from "@images/pages/user-profile-header-bg.png";
import { useHead } from "@vueuse/head";
import moment from "moment";

interface DashboardItem {
  title: string;
  color: string;
  stats: string;
  icon: string;
}

interface OwnerData {
  id: number;
  name: string;
  email: string;
  phone: string;
}

interface CompanyData {
  id: number;
  name: string;
  address: string;
  logo: string;
  created_at: string;
  employee_size: number;
  employees_count: number;
  owner_id: number;
  is_active: boolean;
  owner: OwnerData;
  image_url: string;
}
const { fetchCompanyId, fetchUserData } = useUserData();
const companyData = ref<CompanyData[]>([]);
const isAddNewCompanyDrawerVisible = ref<boolean>(false);
const isLoading = ref<boolean>(false);
const companyId = ref<any>();
const role = ref<string>("");
const activeTab = ref("profile");

// tabs
const tabs = [
  { title: "Details", icon: "tabler-user-check", tab: "profile" },
  //   { title: 'Team', icon: 'tabler-users', tab: 'teams' },
  { title: "Projects", icon: "tabler-layout-grid", tab: "projects" },
  { title: "Connections", icon: "tabler-link", tab: "connections" },
];

const dashboardData = ref<DashboardItem[]>([]);

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
    Object.keys(input).forEach((key) => {
      if (input[key] !== null && input[key] !== undefined) {
        formData.append(key, input[key]);
      }
    });

    let response;
    if (companyData.isUpdate) {
      //
      const { data } = await axios.post("admin/company/update", formData, {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      });
      response = data;
    } else {
      const { data } = await axios.post("admin/company/create", formData, {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      });
      response = data;
    }

    if (response) {
      console.log("response", response);

      isAddNewCompanyDrawerVisible.value = false;
      isLoading.value = false;
    } else {
      isAddNewCompanyDrawerVisible.value = false;
      isLoading.value = false;
    }
  } catch (e) {
    console.error(e);
  } finally {
    fetchCompanyData(fetchCompanyId.value);
    isLoading.value = false;
  }
};

//
const getDashboardDetails = async () => {
  try {
    const { data } = await axios.get("admin/dashboard");

    if (data) {
      dashboardData.value = [
        {
          title: "Category",
          color: "error",
          stats: data.data.category,
          icon: "tabler-briefcase",
        },
        {
          title: "Sub Category",
          color: "success",
          stats: data.data.sub_category,
          icon: "tabler-message-dots",
        },
        {
          title: "Products",
          color: "success",
          stats: data.data.product,
          icon: "tabler-shopping-cart",
        },
        {
          title: "Projects",
          color: "success",
          stats: data.data.product,
          icon: "tabler-target",
        },
        {
          title: "User",
          color: "info",
          stats: data.data.users,
          icon: "tabler-users",
        },
      ];
    }
  } catch (e) {
    console.error(e);
  }
};

const fetchCompanyData = async (id: number) => {
  try {
    if (id) {
      const { data } = await axios.get(`admin/company/${id}`);
      companyData.value = data.data.company;
      console.log("company", companyData.value);
    }
  } catch (e) {
    console.log(e);
  }
};

const EditCopany = () => {
  fetchCompanyData(fetchCompanyId.value);
  isAddNewCompanyDrawerVisible.value = true;
};

onMounted(() => {
  const userData = fetchUserData();
  if (userData) {
    role.value = userData.role;
    if (userData.role === "M") {
      fetchCompanyData(userData.company_id);
    }
  }
  getDashboardDetails();
});

useHead({
  title: "Laravel-Vue | Dashboard",
});
</script>
<template>
  <div v-if="role === 'A'">
    <VRow>
      <VCol
        v-for="statistics in dashboardData"
        :key="statistics.title"
        md="2"
        cols="12"
      >
        <CardStatisticsVertical v-bind="statistics" />
      </VCol>
    </VRow>
  </div>
  <div v-else-if="role === 'M'">
    <VCard v-if="companyData" class="mb-5">
      <VImg :src="headerBG" min-height="125" max-height="250" cover />

      <VCardText
        class="d-flex align-bottom flex-sm-row flex-column justify-center gap-x-5"
      >
        <div class="d-flex h-0">
          <VAvatar
            rounded
            size="120"
            :image="companyData?.image_url"
            class="user-profile-avatar mx-auto"
          />
        </div>

        <div class="user-profile-info w-100 mt-16 pt-6 pt-sm-0 mt-sm-0">
          <h6 class="text-h6 text-center text-sm-start font-weight-medium mb-3">
            {{ companyData?.name }}
          </h6>

          <div
            class="d-flex align-center justify-center justify-sm-space-between flex-wrap gap-4"
          >
            <div
              class="d-flex flex-wrap justify-center justify-sm-start flex-grow-1 gap-2"
            >
              <span class="d-flex">
                <VIcon size="20" icon="tabler-user" class="me-1" />
                <span class="text-body-1">
                  {{ companyData?.owner?.name }}
                </span>
              </span>
              <span class="d-flex">
                <VIcon size="20" icon="tabler-users-group" class="me-1" />
                <span class="text-body-1">
                  {{ companyData?.employees_count }}
                </span>
              </span>

              <span class="d-flex align-center">
                <VIcon size="20" icon="tabler-star" class="me-2" />
                <span class="text-body-1">
                  <VChip
                    label
                    size="small"
                    :color="companyData?.is_active ? 'success' : 'error'"
                    class="text-capitalize"
                  >
                    {{ companyData?.is_active ? "Active" : "InActive" }}
                  </VChip>
                </span>
              </span>

              <span class="d-flex align-center">
                <VIcon size="20" icon="tabler-mail" class="me-2" />
                <span class="text-body-1">
                  {{ companyData?.owner?.email }}
                </span>
              </span>
              <span class="d-flex align-center">
                <VIcon size="20" icon="tabler-phone" class="me-2" />
                <span class="text-body-1">
                  {{ companyData?.owner?.phone }}
                </span>
              </span>

              <span class="d-flex align-center">
                <VIcon size="20" icon="tabler-calendar" class="me-2" />
                <span class="text-body-1">
                  {{ moment(companyData?.created_at).format("YYYY-MM-DD") }}
                </span>
              </span>
            </div>

            <VBtn prepend-icon="tabler-edit" @click="EditCopany"> Edit </VBtn>
          </div>
        </div>
      </VCardText>
    </VCard>

    <VTabs v-model="activeTab" class="v-tabs-pill">
      <VTab v-for="item in tabs" :key="item.icon" :value="item.tab">
        <VIcon size="20" start :icon="item.icon" />
        {{ item.title }}
      </VTab>
    </VTabs>

    <VWindow
      v-model="activeTab"
      class="mt-5 disable-tab-transition"
      :touch="false"
    >
      <!-- Profile -->
      <VWindowItem value="profile">
        <CompanyProfile />
      </VWindowItem>

      <!-- Teams -->
      <!-- <VWindowItem value="teams">
                <UserTeam />
            </VWindowItem> -->

      <!-- Projects -->
      <VWindowItem value="projects">
        <CompanyProject :company_id="fetchCompanyId" />
      </VWindowItem>

      <!-- Connections -->
      <VWindowItem value="connections">
        <CompanyConnection />
      </VWindowItem>
    </VWindow>

    <AddEditCompanyDialog
      v-if="isAddNewCompanyDrawerVisible"
      :company-data="companyData"
      :is-drawer-open="isAddNewCompanyDrawerVisible"
      :is-edit="true"
      :is-loading="isLoading"
      @close-dialog="isAddNewCompanyDrawerVisible = false"
      @company-data="AddNewCompany"
    />
  </div>
</template>

<style lang="scss">
.user-profile-avatar {
  border: 5px solid rgb(var(--v-theme-surface));
  background-color: rgb(var(--v-theme-surface)) !important;
  inset-block-start: -3rem;

  .v-img__img {
    border-radius: 0.125rem;
  }
}
</style>
