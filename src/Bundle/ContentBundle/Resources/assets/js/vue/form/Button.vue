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
        'disabled': props.disabled === true,
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
