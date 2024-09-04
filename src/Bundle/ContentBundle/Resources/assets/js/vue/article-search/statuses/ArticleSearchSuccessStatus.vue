<script setup>

import IconoirCheck from "../icons/IconoirCheck.vue";

const props = defineProps({
    text: String,
});
</script>

<template>
    <div class="py-4 text-lg text-zinc-400 select-none flex flex-col items-center space-y-2">
        <IconoirCheck class="w-10 h-10"/>
        <p>{{ props.text }}</p>
    </div>
</template>

<style scoped>

</style>