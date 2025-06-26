<script setup>
const props = defineProps({
    text: String,
    title: String,
    subtitle: String,
    selected: Boolean,
    tooltip: String,
});

const emit = defineEmits([
    'select',
]);
</script>

<template>
    <div
        @keyup.stop.enter="(e) => emit('select', e)"
        @click.stop.left="(e) => emit('select', e)"
        tabindex="0"
        class="suggestion p-2 space-x-2 flex flex-row justify-stretch items-start outline-0 cursor-pointer"
        :class="{'selected': props.selected}"
        :title="props.tooltip"
    >
        <slot/>
        <div class="grow">
            <div class="flex flex-row justify-between">
                <h4 class="font-semibold line-clamp-1">{{props.title}}</h4>
                <h6 class="text-zinc-400 shrink-0">{{props.subtitle}}</h6>
            </div>
            <p class="line-clamp-2">{{props.text}}</p>
        </div>
    </div>
</template>

<style scoped>

</style>
