<script setup>
import {computed} from "vue";

const props = defineProps({
    type: String,
    disabled: Boolean,
});

const classes = computed(() => {
    return {
        'btn btn-green': props.type === 'primary',
        'no-icon btn btn-white': props.type === 'normal',
        'bg-[#007b67] disabled:hover:bg-[#007b67] text-zinc-300 cursor-not-allowed': props.disabled === true,
    };
});
</script>

<template>
    <button
        :class="classes"
        :disabled="props.disabled"
    >
        <slot/>
    </button>
</template>

<style scoped>

</style>
