<script setup>
const props = defineProps({
    id: String,
    label: String,
    modelValue: String,
    name: String,
    checked: Boolean,
});

const emit = defineEmits([
    'update:modelValue'
]);

</script>

<template>
    <div>
        <label class="radiobutton-container" :for="props.id">
            <input type="radio" :id="props.id" :name="props.name" :value="props.modelValue" @change="() => emit('update:modelValue', props.modelValue)" :checked="props.checked"/>
            <span class="radiobutton"></span>
            <div class="background"></div>
            {{ props.label }}
        </label>
    </div>
</template>

<style scoped>

</style>