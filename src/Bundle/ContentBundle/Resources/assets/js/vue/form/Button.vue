<script setup>
import {computed} from "vue";

const props = defineProps({
    type: String,
    disabled: Boolean,
});

const classes = computed(() => {
    return {
        'bg-[#00ae93] hover:bg-[#00483d] text-white transition-colors': props.type === 'primary',
        'border border-black text-black': props.type === 'normal',
        'bg-[#007b67] disabled:hover:bg-[#007b67] text-zinc-300 cursor-not-allowed': props.disabled === true,
    };
});
</script>

<template>
    <button
        class="rounded-lg px-2 py-1"
        :class="classes"
        :disabled="props.disabled"
    >
        <slot/>
    </button>
</template>

<style scoped>

</style>
