<script setup lang="ts">
import axios from "@/plugins/axios";
import useFormatting from "@/composable/useFormatting";

interface ClientData {
  name: string;
  image_url: string;
}

interface ProjectData {
  name: string;
  total_budget: string;
  budget: string;
  client: ClientData;
  end_date: string;
  start_date: string;
  location: string;
  description: string;
  status: string;
  created_at: string;
  type: string;
  daysLeft: number;
  hours: string;
  budgetSpent: string;
  thumbnil: string;
  chipColor: string;
}

interface ProfileAvatarGroup {
  name: string;
  avatar: string;
}

interface Props {
  company_id: number;
}

const props = defineProps<Props>();

const projectData = ref<ProjectData[]>([]);

const { projectVariant, typeVariant } = useFormatting();

const fetchProjectData = async () => {
  try {
    const input = {
      company_id: props?.company_id,
    };
    const { data } = await axios.post("admin/project/list", input);
    projectData.value = data.data.projects;
  } catch (e) {
    console.error(e);
  }
};

// watch(router, fetchProjectData, { immediate: true })

const moreList = [
  { title: "Rename Project", value: "Rename Project" },
  { title: "View Details", value: "View Details" },
  { title: "Add to favorites", value: "Add to favorites" },
  { type: "divider", class: "my-2" },
  { title: "Leave Project", value: "Leave Project", class: "text-error" },
];

onMounted(() => {
  fetchProjectData();
});
</script>

<template>
  <VRow v-if="projectData">
    <VCol v-for="data in projectData" :key="data.name" cols="12" sm="6" lg="4">
      <VCard>
        <VCardItem>
          <template #prepend>
            <VAvatar :image="data.thumbnil" />
          </template>

          <VCardTitle>{{ data.name }}</VCardTitle>
          <p class="mb-0">
            <span class="font-weight-medium me-1">Client:</span>
            <span>{{ data.client.name }}</span>
          </p>

          <template #append>
            <div class="mt-n8 me-n3">
              <MoreBtn item-props :menu-list="moreList" />
            </div>
          </template>
        </VCardItem>

        <VCardText>
          <div
            class="d-flex align-center justify-space-between flex-wrap gap-x-2 gap-y-4"
          >
            <div>
              <h6 class="text-base font-weight-medium">
                Type :
                <span>
                  <VChip
                    label
                    :color="typeVariant(data.type).color"
                    size="small"
                  >
                    {{ typeVariant(data.type).text }}
                  </VChip>
                </span>
              </h6>
            </div>
            <div>
              <h6 class="text-base font-weight-medium">
                Status :
                <span>
                  <VChip
                    label
                    :color="projectVariant(data.status).color"
                    size="small"
                  >
                    {{ projectVariant(data.status).text }}
                  </VChip>
                </span>
              </h6>
            </div>
          </div>
        </VCardText>

        <VCardText>
          <div
            class="d-flex align-center justify-space-between flex-wrap gap-x-2 gap-y-4"
          >
            <div class="pa-2 bg-var-theme-background rounded">
              <h6 class="text-base font-weight-medium">
                {{ data.total_budget }}
                <span class="text-body-1"> / {{ data?.budget ?? 0 }}</span>
              </h6>
              <span>Total Budget</span>
            </div>

            <div>
              <h6 class="text-base font-weight-medium">
                Start Date:
                <span class="text-body-1">{{ data.start_date }}</span>
              </h6>
              <h6 class="text-base font-weight-medium mb-1">
                Deadline: <span class="text-body-1">{{ data.end_date }}</span>
              </h6>
            </div>
          </div>

          <p class="mt-4 mb-0 clamp-text">
            {{ data.location }}
          </p>
        </VCardText>

        <VDivider />

        <VCardText>
          <div
            class="d-flex align-center justify-space-between flex-wrap gap-2"
          >
            <h6 class="text-base font-weight-medium">
              All Hours: <span class="text-body-1">{{ data.hours }}</span>
            </h6>

            <VChip label :color="data.chipColor" size="small">
              {{ data.daysLeft }} Days left
            </VChip>
          </div>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>
