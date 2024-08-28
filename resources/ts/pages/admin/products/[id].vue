<script lang="ts" setup>
import axios from '@/plugins/axios';
import moment from 'moment';
import { ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';

interface ProductData {
    id: number,
    name: string,
    description: string,
    image: string,
    price: string,
    stock: number,
    display_order: number,
    sub_category_id: number,
    category_id: number,
    sub_category: object,
    category: object,
    images: any,
    created_at: string,
}

const route = useRoute();
const router = useRouter();
const productData = ref<ProductData>()

const getProductDetails = async () => {
    try {
        const { data } = await axios.get(`/admin/product/${route.params.id}`)
        productData.value = data.data.product
    } catch (e) {
        console.error(e);
    }
}

onMounted(() => {
    getProductDetails();
})
</script>
<template>
    <div>
        <VRow>

            <VCol cols="12" sm="6" md="4" v-if="productData">
                <VCard>
                    <VImg :src="productData.images[0].image_url" cover />

                    <VCardItem>
                        <VCardTitle>{{ productData.name }}</VCardTitle>
                    </VCardItem>

                    <VCardText>

                        <div class=" d-flex gap-2 flex-wrap">
                            <v-img v-for="(image, index) in productData.images" :key="index" :src="image.image_url"
                                class="my-image" width="150px" height="150px" />
                            <VBtn color="secondary" variant="tonal" @click="router.push({
                            path: `/admin/products/list`,
                            });"> Back </VBtn>
                        </div>
                    </VCardText>
                </VCard>
            </VCol>
            <VCol cols="12" sm="8" md="8">
                <VCard>
                    <VCardText>
                        <p>Description : {{ productData?.description }}</p>
                        <p>Price : {{ productData?.price }}</p>
                        <p>Stock : {{ productData?.stock }}</p>
                        <p>Category : {{ productData?.category.name }}</p>
                        <p>Sub Category : {{ productData?.sub_category.name }}</p>
                        <p>Created : {{ productData?.created_at ?
                            moment(productData?.created_at).format('YYYY-MM-DD HH:MM:s')
                            : '--' }}</p>
                    </VCardText>
                </VCard>
            </VCol>
        </VRow>
    </div>
</template>
