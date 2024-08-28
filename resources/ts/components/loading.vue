<script setup lang="ts">
import { layout } from '@/store/layout'

// Composable
const useLayout = layout()
const { fullScreenLoad } = storeToRefs(useLayout)

// Data
const opacity = ref('0.6')
const color = ref('indigo-darken-2')

const loading = computed(() => {
  return fullScreenLoad.value
})
</script>

<template>
  <div
    v-if="loading"
    class="overlay fill-height flex-grow-1 d-flex align-center justify-center"
    :style="`background-color: rgba(0, 0, 0, ${opacity});`"
  >
    <VProgressCircular
      :size="60"
      :color="color"
      indeterminate
    />
  </div>
</template>

<style lang="scss" scoped>
.v-progress-circular {
  margin: 1rem;
}

.overlay {
  z-index: 9999999 !important;
  width: 100%;
  position: absolute;
}
</style>
