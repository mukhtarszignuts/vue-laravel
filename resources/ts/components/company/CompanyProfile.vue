<script lang="ts" setup>
import CardStatisticsVertical from '@/components/CardStatisticsVertical.vue';
import axios from '@/plugins/axios';

interface DashboardItem {
    title: string;
    color: string;
    stats: string;
    icon: string;
}

const dashboardData = ref < DashboardItem[] > ([]);
// 
const getDashboardDetails = async () => {
    try {
        const { data } = await axios.get('admin/dashboard');

        if (data) { 
                             
            dashboardData.value = [{
                    title: 'Employee',
                    color: 'info',
                    stats: data.data.employee,
                    icon: 'tabler-users',
                },
                {
                    title: 'Projects',
                    color: 'success',
                    stats: data.data.project,
                    icon: 'tabler-target-arrow',
                },
                {
                    title: 'Client',
                    color: 'warning',
                    stats: data.data.client,
                    icon: 'tabler-users',
                },
                
            ];
           
        }
    } catch (e) {
        console.error(e);
    }
}

onMounted(()=>{
    getDashboardDetails()
})

</script>

<template>
    <VRow>
        <VCol v-for="statistics in dashboardData" :key="statistics.title" md="2" cols="12">
            <CardStatisticsVertical v-bind="statistics" />
        </VCol>
    </VRow>
</template>
