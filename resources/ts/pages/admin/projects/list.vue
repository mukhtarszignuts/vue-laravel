<script setup lang="ts">
import { isEmptyArray } from "@/@core/utils";
import AddEditProjectDialog from "@/components/dialogs/AddEditProjectDialog.vue";
import useFormatting from "@/composable/useFormatting";
import { avatarText } from "@core/utils/formatters";
import noData from "@images/no_data.svg";
import { useHead } from "@vueuse/head";
import moment from "moment";
import { useRouter } from "vue-router";
import { toast } from "vue3-toastify";
import { fetchCompanyList , fetchUserRoleList } from '@/services/GeneralService'
import { fetchProjectList, getProjectdata , AddEditProject , deleteProject } from '@/services/ProjectService'

const router = useRouter();

interface ProjectsData {
  id: number;
  name: string;
  location: string;
  start_date: string;
  end_date: string;
  type: string;
  status: string;
  created_at: string;
  total_budget: number;
  client_id: number;
  client: object;
}

interface ClientData {
  id?: number;
  name?: string;
}

interface CompanyData {
  id: number;
  name: string;
}

const search = ref<string>();
const typeFilter = ref<string>();
const statusFilter = ref<string>();
const isEdit = ref<boolean>(false);
const projectsList = ref<ProjectsData[]>([]);
const clientList = ref<ClientData[]>([]);
const companyData = ref<CompanyData[]>([]);
const count = ref<number>(0);
const perPage = ref<number>(10);
const lengthPage = ref<number>(0);
const currentPage = ref<number>(1);
const sortKey = ref<string>("created_at");
const sortOrder = ref<string>("desc");
const selected = ref([]);
const allSelectedType = ref<boolean>(false);
const deletedId = ref<number>();
const isConfirmDialog = ref<boolean>(false);
const isAddNewProjectDrawerVisible = ref<boolean>(false);
const isLoading = ref<boolean>(false);
const addEditProjectData = ref<object | null>({});
const filerDate = ref<string | any>();

const { projectVariant, typeVariant, types, projectStatus } = useFormatting();

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
  { title: "NO", key: "no" },
  { title: "TITLE", key: "name" },
  { title: "CLIENT", key: "client" },
  { title: "START DATE", key: "start_date" },
  { title: "END DATE", key: "end_date" },
  { title: "BUDGET", key: "total_budget" },
  { title: "TYPE", key: "type" },
  { title: "STATUS", key: "status" },
  { title: "CREATED AT", key: "created_at" },
  { title: "ACTION", key: "action" },
];

// search user
const searchUser = async (data: any) => {
  if (data.length > 2 || data.length === 0) {
    currentPage.value = 1;
    await getProjectsList();
  }
};

const getProjectsList = async () => {
  try {
    const input = {
      sort_field: sortKey.value,
      sort_order: sortOrder.value,
      page: currentPage.value,
      per_page: perPage.value,
      search: search.value,
      status: statusFilter.value,
      type: typeFilter.value,
      start_date: filerDate.value
        ? filerDate.value.includes("to")
          ? filerDate.value.split(" to ")[0]
          : filerDate?.value
        : "",
      end_date: filerDate.value
        ? filerDate.value.includes("to")
          ? filerDate.value.split(" to ")[1]
          : filerDate?.value
        : "",
    };

    // const { data } = await axios.post("admin/project/list", input);
    const data = await fetchProjectList(input)

    if (data) {
      projectsList.value = data.projects;
      count.value = data.count;
      lengthPage.value = Math.ceil(data.count / perPage.value);
    }
  } catch (e) {
    console.log(e);
    toast.error(e.response.data.message);
  }
};

const getClientList = async () => {
  try {
    const data = await fetchUserRoleList('C');
    clientList.value = data.users;
  } catch (e) {
    console.error(e);
  }
};

const getCompanyList = async () => {
  try {
    const data = await fetchCompanyList();
    companyData.value = data.companies;
  } catch (e) {
    console.error(e);
  }
};

// delete Project
const projectDelete = async () => {
  try {
    isLoading.value = true;
    // const path = `/admin/project/delete/${deletedId.value}`;

    const data = await deleteProject(deletedId.value);

    if (data) {
      toast.success(data.message);
      await getProjectsList();
      isConfirmDialog.value = false;
      isLoading.value = false;
    }
  } catch (e) {
    console.log(e);
    toast.error(e);
    isConfirmDialog.value = false;
    isLoading.value = false;
  }
};

// TODO Need to chage
const actionList = (item: any) => {
  const actions = [
    {
      title: "Details",
      id: "1",
      prependIcon: "tabler-eye",
      action: "viewDetails",
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
  return actions;
};

const handleMoreAction = (action: any, item: any) => {
  if (action === "viewDetails") {
    router.push({
      path: `/admin/user/${item.id}`,
    });
  } else if (action === "Edit") {
    EditProject(item.id);
  } else if (action === "Delete") {
    deletedId.value = item.id;
    isConfirmDialog.value = true;
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

const ResetFilter = () => {
  search.value = undefined;
  typeFilter.value = undefined;
  statusFilter.value = undefined;
  filerDate.value = undefined;
  getProjectsList();
};

const allSelect = (i: boolean) => {
  if (i) {
    if (
      projectsList.value &&
      Array.isArray(projectsList.value) &&
      projectsList.value.length
    ) {
      selected.value = [];
      projectsList.value.forEach((obj: any) => {
        const matchingObject = obj.id;
        if (matchingObject) selected.value.push(matchingObject);
      });
    }
  } else {
    selected.value = [];
  }
};

// 👉 Add new project
const AddNewProject = async (projectData: any) => {
  try {
    isLoading.value = true;

    const input = {
      name: projectData.name,
      location: projectData.location,
      type: projectData.type,
      description: projectData.description,
      status: projectData.status,
      start_date: projectData.start_date,
      end_date: projectData.end_date,
      total_budget: projectData.total_budget,
      budget: projectData.budget,
      client_id: projectData.client_id,
      company_id: projectData.company_id,
      thumbnil: projectData.thumbnil,
      id: projectData.isUpdate === true ? projectData.id : null,
    };

    const formData = new FormData();

    // Append non-file data to FormData
    Object.keys(input).forEach(key => {
    if (input[key] !== null && input[key] !== undefined) {
      formData.append(key, input[key]);
    }
    });

    let response;
    if (projectData.isUpdate) {
      // const { data } = await axios.post("admin/project/update", input);
      const data = await AddEditProject(formData,true)
      response = data;
    } else {
      const data = await AddEditProject(formData,false)
      response = data;
    }

    if (response) {
      toast.success(response.message);
      isAddNewProjectDrawerVisible.value = false;
      getProjectsList();
      isLoading.value = false;
    } else {
      isAddNewProjectDrawerVisible.value = false;
      isLoading.value = false;
    }
  } catch (e) {
    toast.error(e.response.data.message);
    isLoading.value = false;
  }
};

// Edit Project
const EditProject = async (id: number) => {
  try {
    // const { data } = await axios.get(`admin/project/${id}`);
    const data = await getProjectdata(id);
    if (data) {
      addEditProjectData.value = data.project;
      (isEdit.value = true), (isAddNewProjectDrawerVisible.value = true);
    }
  } catch (e) {
    console.log(e);
  }
};

// export data
onMounted(() => {
  getProjectsList();
  getClientList();
  getCompanyList();
});

watch(
  async () => currentPage.value,
  async (val) => {
    if (await val) {
      await getProjectsList();
    }
  }
);

watch(
  async () => filerDate.value,
  async (val) => {
    if (await val) {
      await getProjectsList();
    }
  }
);

watch(
  async () => perPage.value,
  async (val) => {
    if (await val) {
      currentPage.value = 1;
      await getProjectsList();
    }
  }
);

watch(
  async () => typeFilter.value,
  async (val) => {
    currentPage.value = 1;
    await getProjectsList();
  }
);

watch(
  async () => statusFilter.value,
  async (val) => {
    currentPage.value = 1;
    await getProjectsList();
  }
);

useHead({
  title: "Laravel-Vue | Projects List",
});
</script>
<template>
  <div>
    <VCard title="Projects List">
      <VCardText>
        <VCardText>
          <VRow>
            <VCol cols="12" class="justify-end">
              <AppTextField
                v-model="search"
                class="error-custom search-input w-25 ms-auto"
                placeholder="Search"
                @input="searchUser(search)"
              />
            </VCol>
          </VRow>
          <VRow class="px-3">
            <VCol lg="2" md="3" sm="12" cols="12">
              <div class="d-flex align-center filter-box-width">
                Show
                <div>
                  <AppSelect
                    v-model="perPage"
                    :items="pageCountList"
                    item-value="value"
                    item-title="label"
                    class="mx-2"
                  />
                </div>
                Entries
              </div>
            </VCol>
            <VCol md="9" lg="10" sm="12" cols="12">
              <div class="d-flex gap-3 common-filter justify-end">
                <AppDateTimePicker
                  v-model="filerDate"
                  class="w-100"
                  placeholder="Select date"
                  :config="{ mode: 'range' }"
                />
                <!--  by Username, Email, Contact number -->
                <AppSelect
                  v-model="typeFilter"
                  :items="types"
                  item-title="name"
                  item-value="id"
                  placeholder="Filter by Type"
                  class="w-5"
                  clearable
                />
                <AppSelect
                  v-model="statusFilter"
                  :items="projectStatus"
                  item-title="name"
                  item-value="id"
                  placeholder="Filter by Status"
                  class="status-filter"
                  clearable
                />

                <div class="d-flex common-filter gap-3">
                  <VBtn
                    :disabled="!typeFilter && !statusFilter && !filerDate"
                    @click="ResetFilter"
                  >
                    Reset Filter
                  </VBtn>
                  <div class="gap-3 d-flex flex-column">
                    <div class="d-flex gap-3 flex-wrap">
                      <VBtn
                        class="mr-0"
                        color="primary"
                        @click="
                          (addEditProjectData = null),
                            (isEdit = false),
                            (isAddNewProjectDrawerVisible = true)
                        "
                      >
                        Add Project
                      </VBtn>
                    </div>
                  </div>
                </div>
              </div>
            </VCol>
          </VRow>
        </VCardText>
        <div v-if="!isEmptyArray(projectsList)" class="table-container">
          <VTable :items-per-page="perPage">
            <thead>
              <tr>
                <th>
                  <VCheckbox
                    v-model="allSelectedType"
                    @click="allSelect(!allSelectedType)"
                  />
                </th>
                <th v-for="(item, index) in headers">
                  {{ item.title }}
                </th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, index) in projectsList" :key="item.id">
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
                  {{ item.name ?? " - " }}
                </td>
                <td>
                  <div class="d-flex align-center">
                    <VAvatar
                      size="38"
                      :variant="!item?.client?.image ? 'tonal' : undefined"
                      :color="'secondary'"
                      class="me-3"
                    >
                      <VImg
                        v-if="item?.client?.image_url"
                        :src="item?.client?.image_url"
                      />
                      <span v-else>{{ avatarText(item?.client?.name) }}</span>
                    </VAvatar>
                    <div class="d-flex flex-column">
                      <h6 class="text-body-1 font-weight-medium">
                        <div class="user-list-name">
                          {{ item.client?.name }}
                        </div>
                      </h6>
                      <span class="text-sm text-disabled">{{
                        item.client?.phone
                      }}</span>
                    </div>
                  </div>
                </td>
                <td>
                  {{
                    item.start_date
                      ? moment(item.start_date).format("YYYY-MM-DD")
                      : " - "
                  }}
                </td>
                <td>
                  {{
                    item.end_date
                      ? moment(item.end_date).format("YYYY-MM-DD")
                      : " - "
                  }}
                </td>
                <td>
                  {{ item.total_budget ?? " - " }}
                </td>
                <td>
                  <VChip
                    label
                    :color="typeVariant(item.type).color"
                    class="font-weight-medium"
                    size="small"
                  >
                    {{ typeVariant(item.type).text }}
                  </VChip>
                </td>
                <td>
                  <VChip
                    :color="projectVariant(item.status).color"
                    class="font-weight-medium"
                    size="small"
                  >
                    {{ projectVariant(item.status).text }}
                  </VChip>
                </td>
                <td>
                  {{
                    item.created_at
                      ? moment(item.created_at).format("YYYY-MM-DD HH:MM:ss")
                      : " - "
                  }}
                </td>
                <td>
                  <div class="d-flex">
                    <IconBtn>
                      <VIcon icon="mdi-dots-vertical" />
                      <VMenu activator="parent" open-on-click>
                        <VCard>
                          <VList
                            v-for="i in actionList(item).filter(
                              (action) => action.show !== false
                            )"
                            :key="i?.id"
                            class="pa-1"
                          >
                            <VListItem
                              @click="handleMoreAction(i?.action, item)"
                            >
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

        <div v-if="!isEmptyArray(projectsList)" class="d-flex mx-7 mt-5">
          <VRow v-if="!isEmptyArray(projectsList)">
            <VCol class="d-flex align-center">
              <div>
                Showing {{ fromNumber }} to {{ toNumber }} of
                {{ count }} entries
              </div>
            </VCol>
            <VCol>
              <div class="right-aligned-pagination">
                <VPagination
                  v-model="currentPage"
                  :total-visible="10"
                  :length="lengthPage"
                />
              </div>
            </VCol>
          </VRow>
        </div>
      </VCardText>
    </VCard>

    <!-- Single delete user -->
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
          <VBtn @click="projectDelete" color="success"> Yes </VBtn>
        </VCardText>
      </VCard>
    </VDialog>

    <!-- User info Update  -->
    <AddEditProjectDialog
      v-if="isAddNewProjectDrawerVisible"
      :is-drawer-open="isAddNewProjectDrawerVisible"
      :is-edit="isEdit"
      :project-data="addEditProjectData"
      :companies="companyData"
      :types="types"
      :status="projectStatus"
      :clients="clientList"
      @close-dialog="isAddNewProjectDrawerVisible = false"
      @project-data="AddNewProject"
    />
  </div>
</template>
<style lang="scss">
.common-filter .app-select.flex-grow-1 {
  min-width: 160px;
  max-width: 160px;
}
.common-filter .status-filter {
  min-width: 170px !important;
  max-width: 170px !important;
}

.app-picker-field {
  min-width: 150px !important;
  max-width: 380px !important;
}

.common-filter {
  flex-wrap: wrap;
}

.table-container {
  overflow-x: auto;
}

.table-container table {
  width: 100%;
  min-width: 800px;
  /* Adjust this to fit your table's minimum width */
}

.table-container th,
.table-container td {
  white-space: nowrap;
  /* Prevents content from wrapping and maintains a single line */
}
</style>
