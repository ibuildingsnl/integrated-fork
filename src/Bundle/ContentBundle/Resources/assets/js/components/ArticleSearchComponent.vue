<script setup>
import {computed, ref} from "vue";

const endpoint = 'https://integrated.localhost/admin/content';

let channels = ref([
    {
        key: 'vleesmagazine',
        label: 'Vleesmagazine',
        active: false,
    },
    {
        key: 'evmi',
        label: 'EVMI',
        active: false,
    },
    {
        key: 'vismagazine',
        label: 'Vismagazine',
        active: false,
    }
]);

let searchTerm = ref('');
let activeChannels = computed(() => {
    return channels.value.filter((channel) => channel.active).map((channel) => channel.key).join(',');
});

async function doSearch() {
    const response = await fetch(`${endpoint}/search-channel/${activeChannels.value}?keyword=${searchTerm.value}`);

    console.log(await response.json());
}
</script>

<template>
    <div class="px-4 py-2">
        <h3>Zoek in kanalen</h3>
        <div v-for="channel in channels">
            <div :key="channel.key">
                <input :id="`article-search-${channel.key}`" type="checkbox" v-model="channel.active"/>
                <label :for="`article-search-${channel.key}`">${ channel.label }$</label>
            </div>
        </div>
        <div>
            <input type="text" v-model="searchTerm" placeholder="Zoekterm">
            <button @click.prevent.stop="doSearch">Zoek</button>
        </div>
    </div>
</template>

<style scoped>

</style>
