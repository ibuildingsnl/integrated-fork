<script setup>
import {computed} from "vue";

const props = defineProps({
    id: undefined,
    label: undefined,
    modelValue: false,
});

const emit = defineEmits([
    'update:modelValue'
]);

const value = computed({
    get() {
        return props.modelValue;
    },
    set(val) {
        emit('update:modelValue', val);
    }
});
</script>

<template>
    <div class="flex flex-row justify-start items-center space-x-2">
        <input :id="props.id" v-model="value" type="checkbox"/>
        <label :for="props.id">{{ label }}</label>
    </div>
</template>

<style scoped lang="scss">
    input[type=checkbox] {
        appearance: none;
        background-color: #fff;
        margin: 0;
        font: inherit;
        color: currentColor;
        width: 1.15em;
        height: 1.15em;
        border: theme('borderWidth.2') solid currentColor;
        border-radius: theme('borderRadius.md');
        transform: translateY(-0.075em);
        &:checked {
            background-color: theme('colors.blue.400');
        }
    }
</style>
