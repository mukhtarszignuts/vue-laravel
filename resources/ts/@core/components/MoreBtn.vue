<script lang="ts" setup>
interface Props {
  menuList?: unknown[];
  itemProps?: boolean;
}

interface Emit {
  (e: "action", value: any): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emit>();
</script>

<template>
  <IconBtn density="compact" color="disabled">
    <VIcon icon="tabler-dots-vertical" />

    <VMenu v-if="props.menuList" activator="parent">
      <!-- <VList
        :items="props.menuList"
        :item-props="props.itemProps"
      /> -->
      <VList>
      <VListItem
        v-for="item in menuList"
        :key="item?.value"
        @click="emit('action',item)"
      >
        <VListItemTitle>{{ item.title }}</VListItemTitle>
      </VListItem>
    </VList>
    </VMenu>
  </IconBtn>
</template>
