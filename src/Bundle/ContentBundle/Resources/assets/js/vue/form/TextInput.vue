<script setup>
import {computed, useSlots} from "vue";

const props = defineProps({
    placeholder: String,
    modelValue: undefined,
    id: String,
    errorText: String,
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
        <input
            @focus="(e) => emit('focus', e)"
            @focusout.stop="(e) => emit('blur', e)"
            @keyup="(e) => emit('keyup', e)"
            :id="id"
            v-model="value"
            :placeholder="props.placeholder" type="text"
            :class="{'!border-red-400': props.errorText?.length ?? 0 > 0}"
            :title="props.errorText ?? ''"
        />
        <slot/>
    </div>
</template>

<style scoped>

</style>
