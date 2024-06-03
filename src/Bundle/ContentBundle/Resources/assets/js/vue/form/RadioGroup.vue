<script setup>
import RadioButton from "./RadioButton.vue";

const props = defineProps({
    label: String|null,
    name: String,
    radioButtons: Array, // { value: String, key: String, label: String }
    modelValue: String,
});

const emit = defineEmits(['update:modelValue']);
</script>

<template>
    <div class="w-full">
        <p v-if="props.label?.length ?? 0 > 0">{{ props.label }}</p>
        <RadioButton v-for="radioButton in radioButtons" :key="radioButton.key" :name="props.name" :model-value="radioButton.value" :label="radioButton.label" :id="radioButton.key" @update:modelValue="(val) => emit('update:modelValue', val)" :checked="props.modelValue === radioButton.value"/>
    </div>
</template>

<style scoped>

</style>