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
    <div class="bg-white flex flex-row px-2 py-1 rounded-lg border border-zinc-200"
         :class="{'pr-2!': slots.default}"
    >
        <input @focus="(e) => emit('focus', e)"
               @focusout.stop="(e) => emit('blur', e)"
               @keyup="(e) => emit('keyup', e)"
               :id="id"
               class="outline-0 w-full"
               v-model="value"
               :placeholder="props.placeholder" type="text"/>
        <slot/>
    </div>
</template>

<style scoped>

</style>
