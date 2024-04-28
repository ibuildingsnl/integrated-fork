<script setup>
import {computed, useSlots} from "vue";

const props = defineProps({
    placeholder: undefined,
    modelValue: undefined,
    id: undefined,
});

const emit = defineEmits([
    'update:modelValue',
    'focus',
    'blur',
    'keyup',
]);

const value = computed({
    get() {
        return props.modelValue;
    },
    set(val) {
        emit('update:modelValue', val);
    }
});

const slots = useSlots();
</script>

<template>
    <div class="form-field">
        <input @focus="(e) => emit('focus', e)"
               @focusout.stop="(e) => emit('blur', e)"
               @keyup="(e) => emit('keyup', e)"
               :id="id"
               v-model="value"
               :placeholder="props.placeholder" type="text"/>
        <slot/>
    </div>
</template>

<style scoped>

</style>
