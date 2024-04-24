<script setup>
const props = defineProps({
    maxHeight: String,
});
</script>

<template>
    <div class="flex flex-col overflow-y-auto p-2 space-y-1 border border-zinc-200 rounded-md" :style="`max-height: ${props.maxHeight};`">
        <slot/>
    </div>
</template>

<style scoped>

</style>