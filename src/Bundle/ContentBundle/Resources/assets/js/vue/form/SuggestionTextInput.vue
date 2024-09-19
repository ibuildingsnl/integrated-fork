<script setup>
import TextInput from "./TextInput.vue";
import {computed, ref, useSlots} from "vue";
import SuggestionTextInputSuggestion from "./SuggestionTextInputSuggestion.vue";
import debounce from "lodash.debounce";

const props = defineProps({
    suggestions: Array,
    modelValue: String,
    selections: Array,
    placeholder: String,
    id: String,
    permanent: Boolean,
    multiple: Boolean,
    errorText: String,
});

const emit = defineEmits(['update:modelValue', 'update:selections', 'searchConfirm']);
const slots = useSlots();

const value = computed({
    get() {
        return props.modelValue;
    },
    set(newValue) {
        emit('update:modelValue', newValue);
        sendSearch();
    }
});

const selections = computed({
    get() {
        return props.selections ?? [];
    },
    set(newValue) {
        emit('update:selections', newValue);
    }
});

const input = ref(null);
const isShowing = ref(false);

const onBlur = (e) => {
    if (!input.value.parentElement.contains(e.relatedTarget)) {
        isShowing.value = false;
    }
};

const selectSuggestion = (key) => {
    if (props.suggestions.findIndex((v) => v.id === key) !== -1) {
        if (props.multiple) {
            if (selections.value.includes(key)) {
                selections.value = selections.value.filter((v) => v !== key);
                return;
            }

            selections.value.push(key);
            return;
        }

        if (selections.value.includes(key)) {
            selections.value = [];
            return;
        }

        selections.value = [key];
    } else {
        if (props.multiple) {
            selections.value = selections.value.filter((v) => v.id !== key);
            return;
        }

        selections.value = [];
    }
};

const sendSearch = debounce(() => {
    emit('searchConfirm');
}, 300);
</script>

<template>
    <div class="relative" v-click-outside="() => isShowing = false">
        <TextInput
            v-model="value"
            @blur="onBlur"
            @focus="() => isShowing = true"
            @keyup.enter="sendSearch"
            :placeholder="props.placeholder"
            :id="props.id"
            :error-text="props.errorText"
        />
        <div
            ref="input"
            v-show="isShowing || props.permanent"
            class="flex flex-col justify-stretch w-full bg-white rounded-lg results overflow-y-auto"
            :class="{'absolute bottom-0 left-0 translate-y-full': !props.permanent, 'permanent': props.permanent}"
        >
            <SuggestionTextInputSuggestion
                @select="() => selectSuggestion(id)"
                @focusout="onBlur"
                v-for="{id, title, subtitle, text, url} in props.suggestions"
                :key="id"
                :title="title"
                :subtitle="subtitle"
                :text="text"
                :tooltip="url"
                :selected="selections.includes(id)"
            />
            <div v-if="props.suggestions.length === 0" class="grow flex flex-row justify-center items-center">
                <slot/>
                <span v-if="!slots.default" class="py-4 text-lg text-zinc-400 select-none">No results</span>
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>
