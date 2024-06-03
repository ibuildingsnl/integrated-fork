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
    <div class="checkbox">
        <label :for="props.id" class="checkbox-container">
            <input :id="props.id" v-model="value" type="checkbox"/>
            <span class="checkmark"></span>
            <div class="background"></div>
            {{ label }}
        </label>
    </div>
</template>
