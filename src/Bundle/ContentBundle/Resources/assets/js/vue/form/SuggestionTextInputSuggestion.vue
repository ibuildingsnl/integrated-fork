<script setup>
const props = defineProps({
    text: undefined,
    title: undefined,
    subtitle: undefined,
});

const emit = defineEmits([
    'select',
]);
</script>

<template>
    <div @keyup.stop.enter="(e) => emit('select', e)"
         @click.stop.left="(e) => emit('select', e)"
         tabindex="0"
         class="p-2 space-x-2 flex flex-row justify-start items-start outline-0 hover:bg-blue-50 focus:bg-blue-100 cursor-pointer">
        <slot/>
        <div>
            <div class="flex flex-row justify-between">
                <h4 class="font-semibold">Result title</h4>
                <h6 class="text-zinc-400">23-03-2023</h6>
            </div>
            <p class="line-clamp-2">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Ab aliquid
                delectus deserunt dolores et
                eum illo ipsam minima necessitatibus officiis, repudiandae sint ullam veritatis! Consequuntur
                odit possimus temporibus tenetur velit.</p>
        </div>
    </div>
</template>

<style scoped>

</style>
