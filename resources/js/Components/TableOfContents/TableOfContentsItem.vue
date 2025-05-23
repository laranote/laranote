<template>
    <div
        :class="{
       'is-active': item.isActive && !item.isScrolledOver,
       'is-scrolled-over': item.isScrolledOver,
     }"
        :style="{ '--level': item.level }"
    >
        <a :href="'#' + item.id" @click.prevent="onItemClick" :data-item-index="item.itemIndex">
            {{ item.textContent }}
        </a>
    </div>
</template>

<script setup>
const props = defineProps({
    item: {
        type: Object,
        required: true,
    },
    index: {
        type: Number,
        required: true,
    },
});

const emit = defineEmits(['item-click']);

const onItemClick = (event) => {
    emit('item-click', event, props.item.id)
}
</script>
