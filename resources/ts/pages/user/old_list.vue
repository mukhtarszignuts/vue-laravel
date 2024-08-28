<script setup lang="ts">
import { isEmptyArray } from "@/@core/utils";
import axios from '@/plugins/axios';
import { avatarText } from '@core/utils/formatters';
import moment from 'moment';
import { toast } from 'vue3-toastify';
import { VDataTable } from "vuetify/labs/VDataTable";

// User data 
interface Users {
  id: number
  name: string
  phone: string
  email: string
  role: string
  status: string
  created_at: string
}
const users = ref<Users[]>([]);
const count = ref<number>(0);
const perPage = ref<number>(10);
const lengthPage = ref<number>(0);
const currentPage = ref(1);
const sortKey = ref('created_at');
const sortOrder = ref('desc');

const headers = [
  { title: 'NAME', key: 'name' },
  { title: 'EMAIL', key: 'email' },
  { title: 'PHONE', key: 'phone' },
  { title: 'ROLE', key: 'role' },
  { title: 'STATUS', key: 'status' },
  { title: 'CREATED AT', key: 'created_at' },
  { title: 'ACTION', key: 'action' },
]
const getUserList = async () =>{
    try {
        const input = {
            sortKey: sortKey.value,
            sortOrder: sortOrder.value,
            page: currentPage.value,
            per_page: perPage.value,
        };

        const { data } = await axios.post('user/list',input)
        
        if(data){
          console.log('users',data.data.users)
            users.value=[...users.value,...data.data.users]
            // count.value = data.data.count;
            // lengthPage.value = Math.ceil(data.data.count / perPage.value);
            // users.value = data.data.users;

            count.value=data.data.count
            lengthPage.value = Math.ceil(data.data.count / perPage.value);
        } 
    } catch (e) {
        console.log(e);
        toast.error(e.response.data.message)
    }
}


const resolveStatusVariant = (status: string) => {
  if (status === 'A')
  return { color: 'success', text: 'Active' }
  else if (status === 'I')
    return { color: 'error', text: 'Rejected' }
  else if (status === 'P')
    return { color: 'warning', text: 'Pending' }
  else
    return { color: 'info', text: 'undefine' }
}

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
      prependIcon: "tabler-eye",
      action: "Edit",
      show: true,
    },
  ];
return actions

};

const handleMoreAction = (action: any, item: any) => {
  if (action === "viewDetails") {
    // 
  } else if (action === "Edit") {
    
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

watch(
  async () => currentPage.value,
  async (val) => {
    if (await val){
       getUserList()
    } 
  }
);

watch(
  async () => perPage.value,
  async (val) => {
    if (await val) {
      currentPage.value = 1;
       getUserList()
    }
  }
);


onMounted(() => {
  getUserList()
})


</script>
<template>
  <div>
    <v-card>
      <v-card-title primary-title>
        User List
      </v-card-title>

      <v-card-text>
        <VDataTable :headers="headers" :items="users" :items-per-page="perPage" :page="currentPage">
          <template #item.name="{ item }">
            <div class="d-flex align-center">
              <VAvatar size="32" :color="item.avatar ? '' : 'primary'"
                :class="item.avatar ? '' : 'v-avatar-light-bg primary--text'"
                :variant="!item.avatar ? 'tonal' : undefined">
                <VImg v-if="item.avatar" :src="item.avatar" />
                <span v-else>{{ avatarText(item.raw.name) }}</span>
              </VAvatar>
              <div class="d-flex flex-column ms-3">
                <span class="d-block font-weight-medium text-high-emphasis text-truncate">{{
                  item.raw.name
                  }}</span>

              </div>
            </div>
          </template>

          <template #item.email="{ item }">
            {{ item.raw.email }}
          </template>

          <template #item.phone="{ item }">
            {{ item.raw.phone }}
          </template>

          <template #item.role="{ item }">
            {{ item.raw.role }}
          </template>

          <!-- status -->
          <template #item.status="{ item }">
            <VChip :color="resolveStatusVariant(item.raw.status).color" class="font-weight-medium" size="small">
              {{ resolveStatusVariant(item.raw.status).text }}
            </VChip>
          </template>

          <template #item.created_at="{ item }">
            {{ item.raw.created_at ? moment(item.raw.created_at).format("YYYY/MM/DD H:MM:ss") : '--'
            }}
          </template>

          <template #item.action="{ item }">
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
          </template>
          <template #bottom>
            <div v-if="!isEmptyArray(users)" class="d-flex mx-7 mt-5">
              <VRow v-if="!isEmptyArray(users)">
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
          </template>
        </VDataTable>
        <!-- <div v-if="!isEmptyArray(users)" class="d-flex mx-7 mt-5">
          <VRow v-if="!isEmptyArray(users)">
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
        </div> -->
      </v-card-text>
    </v-card>
  </div>
</template>