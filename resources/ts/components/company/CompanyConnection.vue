<script setup lang="ts">
import axios from '@axios'
import { useRoute } from 'vue-router'
import avatar1 from '@images/avatars/avatar-1.png'
import avatar2 from '@images/avatars/avatar-2.png'
import avatar3 from '@images/avatars/avatar-3.png'
import avatar4 from '@images/avatars/avatar-4.png'
import avatar5 from '@images/avatars/avatar-5.png'
import avatar6 from '@images/avatars/avatar-6.png'


interface ConnectionsTab{
  name: string
  tasks: string
  avatar: string
  projects: string
  connections: string
  designation: string
  isConnected: boolean
  chips: ProfileChip[]
}

interface  ProfileChip{
  title: string
  color: string
}

const StaticConnection=[
    {
      tasks: '834',
      projects: '18',
      isConnected: true,
      connections: '129',
      name: 'Mark Gilbert',
      designation: 'UI Designer',
      avatar: avatar1,
      chips: [
        {
          title: 'Figma',
          color: 'secondary',
        },
        {
          title: 'Sketch',
          color: 'warning',
        },
      ],
    },
    {
      tasks: '2.31k',
      projects: '112',
      isConnected: false,
      connections: '1.28k',
      name: 'Eugenia Parsons',
      designation: 'Developer',
      avatar: avatar2,
      chips: [
        {
          color: 'error',
          title: 'Angular',
        },
        {
          color: 'info',
          title: 'React',
        },
      ],
    },
    {
      tasks: '1.25k',
      projects: '32',
      isConnected: false,
      connections: '890',
      name: 'Francis Byrd',
      designation: 'Developer',
      avatar: avatar3,
      chips: [
        {
          title: 'HTML',
          color: 'primary',
        },
        {
          color: 'info',
          title: 'React',
        },
      ],
    },
    {
      tasks: '12.4k',
      projects: '86',
      isConnected: false,
      connections: '890',
      name: 'Leon Lucas',
      designation: 'UI/UX Designer',
      avatar: avatar4,
      chips: [
        {
          title: 'Figma',
          color: 'secondary',
        },
        {
          title: 'Sketch',
          color: 'warning',
        },
        {
          color: 'primary',
          title: 'Photoshop',
        },
      ],
    },
    {
      tasks: '23.8k',
      projects: '244',
      isConnected: true,
      connections: '2.14k',
      name: 'Jayden Rogers',
      designation: 'Full Stack Developer',
      avatar: avatar5,
      chips: [
        {
          color: 'info',
          title: 'React',
        },
        {
          title: 'HTML',
          color: 'warning',
        },
        {
          color: 'success',
          title: 'Node.js',
        },
      ],
    },
    {
      tasks: '1.28k',
      projects: '32',
      isConnected: false,
      designation: 'SEO',
      connections: '1.27k',
      name: 'Jeanette Powell',
      avatar: avatar6,
      chips: [
        {
          title: 'Analysis',
          color: 'secondary',
        },
        {
          color: 'success',
          title: 'Writing',
        },
      ],
    },
  ]

const router = useRoute()
const connectionData = ref<ConnectionsTab[]>([])

// const fetchProjectData = () => {
//   if (router.params.tab === 'connections') {
//     axios.get('/pages/profile', {
//       params: {
//         tab: router.params.tab,
//       },
//     }).then(response => {
//       connectionData.value = response.data
//     })
// }
// connectionData.value = []
// }

// watch(router, fetchProjectData, { immediate: true })

onMounted(()=>{
    // console.log('connections');
    // fetchProjectData()
})
</script>

<template>
    <VRow>
        <VCol v-for="data in StaticConnection" :key="data.name" sm="6" lg="4" cols="12">
            <VCard>
                <div class="vertical-more">
                    <MoreBtn :menu-list="[
              { title: 'Share connection', value: 'Share connection' },
              { title: 'Block connection', value: 'Block connection' },
              { type: 'divider', class: 'my-2' },
              { title: 'Delete', value: 'Delete', class: 'text-error' },
            ]" item-props />
                </div>

                <VCardItem>
                    <VCardTitle class="d-flex flex-column align-center justify-center">
                        <VAvatar size="100" :image="data.avatar" />

                        <p class="mt-4 mb-0">
                            {{ data.name }}
                        </p>
                        <span class="text-body-1">{{ data.designation }}</span>

                        <div class="d-flex align-center flex-wrap gap-2 mt-2">
                            <VChip v-for="chip in data.chips" :key="chip.title" label :color="chip.color" size="small">
                                {{ chip.title }}
                            </VChip>
                        </div>
                    </VCardTitle>
                </VCardItem>

                <VCardText>
                    <div class="d-flex justify-space-around">
                        <div class="text-center">
                            <h6 class="text-h6">
                                {{ data.projects }}
                            </h6>
                            <span class="text-body-1">Projects</span>
                        </div>
                        <div class="text-center">
                            <h6 class="text-h6">
                                {{ data.tasks }}
                            </h6>
                            <span class="text-body-1">Tasks</span>
                        </div>
                        <div class="text-center">
                            <h6 class="text-h6">
                                {{ data.connections }}
                            </h6>
                            <span class="text-body-1">Connections</span>
                        </div>
                    </div>

                    <div class="d-flex justify-center gap-4 mt-5">
                        <VBtn :prepend-icon="data.isConnected ? 'tabler-user-check' : 'tabler-user-plus'"
                            :variant="data.isConnected ? 'elevated' : 'tonal'">
                            {{ data.isConnected ? 'connected' : 'connect' }}
                        </VBtn>

                        <IconBtn variant="tonal" class="rounded">
                            <VIcon icon="tabler-mail" />
                        </IconBtn>
                    </div>
                </VCardText>
            </VCard>
        </VCol>
    </VRow>
</template>

<style lang="scss">
.vertical-more {
    position: absolute;
    inset-block-start: 1rem;
    inset-inline-end: 0.5rem;
}
</style>
