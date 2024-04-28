<script setup>
const props = defineProps({
    maxHeight: String,
});
</script>

<template>
    <div class="form-group w-full flex flex-wrap overflow-y-auto" :style="`max-height: ${props.maxHeight};`">
        <slot/>
    </div>
</template>

<style scoped>

</style>
