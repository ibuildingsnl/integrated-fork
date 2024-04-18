<script setup>
import {computed} from "vue";

const props = defineProps({
    type: 'normal',
});

const classes = computed(() => {
    return {
        'bg-[#00ae93] text-white': props.type === 'primary',
        'border border-black text-black': props.type === 'normal',
    };
});
</script>

<template>
<button class="rounded-lg px-2 py-1"
        :class="classes"
><slot/></button>
</template>

<style scoped>

</style>
