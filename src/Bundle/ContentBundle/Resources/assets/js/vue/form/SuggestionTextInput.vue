<script setup>
import TextInput from "./TextInput.vue";
import {computed, ref} from "vue";
import SuggestionTextInputSuggestion from "./SuggestionTextInputSuggestion.vue";

const props = defineProps({
    suggestions: [],
    modelValue: undefined,
    placeholder: undefined,
    id: undefined,
    permanent: false,
});

const emit = defineEmits(['update:modelValue']);

const value = computed({
    get() {
        return props.modelValue;
    },
    set(newValue) {
        emit('update:modelValue', newValue);
    }
});

const input = ref(null);

const onBlur = (e) => {
    if(!input.value.parentElement.contains(e.relatedTarget)) {
        isShowing.value = false;
    }
};

const isShowing = ref(false);
</script>

<template>
    <div class="relative" v-click-outside="() => isShowing = false">
        <TextInput v-model="value" @blur="onBlur" @focus="() => isShowing = true" :placeholder="props.placeholder" :id="props.id"/>
        <div ref="input"
             v-show="isShowing || props.permanent"
             class="w-full bg-white rounded-lg shadow z-50 max-h-[260px] overflow-y-auto"
             :class="{'absolute bottom-0 left-0 translate-y-full': !props.permanent}"
        >
            <SuggestionTextInputSuggestion @focusout="onBlur" v-for="{key, title, subtitle, text} in props.suggestions" :key="key" :title="title" :subtitle="subtitle" :text="text"/>
        </div>
    </div>
</template>

<style scoped>

</style>
