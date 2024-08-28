<script setup lang="ts">
import { avatarText, kFormatter } from '@core/utils/formatters'

interface Props {
  userData: {
    id: number
    name: string
    role: string
    phone: string
    email: string
    city: string
    currentPlan: string
    status: string
    image: string
    image_url: string
    taskDone: number
    projectDone: number
    
  },
  
}

interface Emit {
  (e: "isDrawerOpen", value: boolean): void;
}

const props = defineProps<Props>()

const emit = defineEmits<Emit>()


// 👉 Status variant resolver
const resolveUserStatusVariant = (stat: string) => {
  if (stat === 'P')
    return { color: 'warning',title:'Pending'}
  if (stat === 'A')
    return { color: 'success',title:'Active'}
  if (stat === 'I')
    return { color: 'error',title:'In Active'}

  return { color: 'secondary',title:'Unknown'}
}

// 👉 Role variant resolver
const resolveUserRoleVariant = (role: string) => {
  if (role === 'M')
    return { color: 'warning', icon: 'tabler-user' , title:'Manager'}
  if (role === 'A')
    return { color: 'success', icon: 'tabler-circle-check' , title:'Admin' }
  if (role === 'C')
    return { color: 'primary', icon: 'tabler-chart-pie-2' , title:'Customer' }
  
  return { color: 'primary', icon: 'tabler-user' }
}

</script>

<template>
    <div>
        <VRow>
            <!-- SECTION User Details -->
            <VCol cols="12">
                <VCard v-if="props.userData">
                    <VCardText class="text-center pt-15">
                        <!-- 👉 Avatar -->
                        <VAvatar rounded :size="100" :color="!props.userData.image ? 'primary' : undefined"
                            :variant="!props.userData.image ? 'tonal' : undefined">
                            <VImg v-if="props.userData.image" :src="props.userData.image_url" />
                            <span v-else class="text-5xl font-weight-medium">
                                {{ avatarText(props.userData.name) }}
                            </span>
                        </VAvatar>

                        <!-- 👉 User name -->
                        <h6 class="text-h4 mt-4">
                            {{ props.userData.name }}
                        </h6>

                        <!-- 👉 Role chip -->
                        <VChip label :color="resolveUserRoleVariant(props.userData.role).color" size="small"
                            class="text-capitalize mt-3">
                            {{ resolveUserRoleVariant(props.userData.role).title }}
                        </VChip>
                    </VCardText>

                    <VCardText class="d-flex justify-center flex-wrap mt-3">
                        <!-- 👉 Done task -->
                        <div class="d-flex align-center me-8">
                            <VAvatar :size="38" rounded color="primary" variant="tonal" class="me-3">
                                <VIcon icon="tabler-checkbox" />
                            </VAvatar>

                            <div>
                                <h6 class="text-h6">
                                    {{ kFormatter(props.userData.taskDone) }}
                                </h6>
                                <span class="text-sm">Task Done</span>
                            </div>
                        </div>

                        <!-- 👉 Done Project -->
                        <div class="d-flex align-center me-4">
                            <VAvatar :size="38" rounded color="primary" variant="tonal" class="me-3">
                                <VIcon icon="tabler-briefcase" />
                            </VAvatar>

                            <div>
                                <h6 class="text-h6">
                                    {{ kFormatter(props.userData.projectDone) }}
                                </h6>
                                <span class="text-sm">Project Done</span>
                            </div>
                        </div>
                    </VCardText>

                    <VDivider />

                    <!-- 👉 Details -->
                    <VCardText>
                        <p class="text-sm text-uppercase text-disabled">
                            Details
                        </p>

                        <!-- 👉 User Details list -->
                        <VList class="card-list mt-2">
                            <VListItem>
                                <VListItemTitle>
                                    <h6 class="text-h6">
                                        Username:
                                        <span class="text-body-1">
                                            {{ props.userData.name }}
                                        </span>
                                    </h6>
                                </VListItemTitle>
                            </VListItem>

                            <VListItem>
                                <VListItemTitle>
                                    <h6 class="text-h6">
                                        Email:
                                        <span class="text-body-1">{{ props.userData.email }}</span>
                                    </h6>
                                </VListItemTitle>
                            </VListItem>

                            <VListItem>
                                <VListItemTitle>
                                    <h6 class="text-h6">
                                        Status:

                                        <VChip label size="small"
                                            :color="resolveUserStatusVariant(props.userData.status).color"
                                            class="text-capitalize">
                                            {{ resolveUserStatusVariant(props.userData.status).title }}
                                        </VChip>
                                    </h6>
                                </VListItemTitle>
                            </VListItem>

                            <VListItem>
                                <VListItemTitle>
                                    <h6 class="text-h6">
                                        Role:
                                        <span class="text-capitalize text-body-1"> {{
                                            resolveUserRoleVariant(props.userData.role).title }}</span>
                                    </h6>
                                </VListItemTitle>
                            </VListItem>
                            <VListItem>
                                <VListItemTitle>
                                    <h6 class="text-h6">
                                        Contact:
                                        <span class="text-body-1">{{ props.userData.phone }}</span>
                                    </h6>
                                </VListItemTitle>
                            </VListItem>
                            <VListItem>
                                <VListItemTitle>
                                    <h6 class="text-h6">
                                        City:
                                        <span class="text-body-1">{{ props.userData.city }}</span>
                                    </h6>
                                </VListItemTitle>
                            </VListItem>
                        </VList>
                    </VCardText>

                    <!-- 👉 Edit and Suspend button -->
                    <VCardText class="d-flex justify-center">
                        <VBtn variant="elevated" class="me-4" @click="emit('isDrawerOpen',true)">
                            Edit
                        </VBtn>

                        <VBtn variant="tonal" color="error">
                            Suspend
                        </VBtn>
                    </VCardText>
                </VCard>
            </VCol>
            <!-- !SECTION -->
        </VRow>
    </div>
</template>

<style lang="scss" scoped>
.card-list {
    --v-card-list-gap: 0.75rem;
}

.text-capitalize {
    text-transform: capitalize !important;
}
</style>
