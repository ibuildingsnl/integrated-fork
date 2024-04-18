<script setup>
import {computed, reactive, ref} from "vue";
import debounce from "lodash.debounce";
import Checkbox from "../form/Checkbox.vue";
import TextInput from "../form/TextInput.vue";
import SuggestionTextInput from "../form/SuggestionTextInput.vue";
import Button from "../form/Button.vue";

const endpoint = 'https://integrated.localhost/admin';

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
    },
    {
        key: 'bakkers_in_bedrijf',
        label: 'Bakkers in Bedrijf',
        active: false,
    }
]);

let results = ref([]);
let searchTerm = ref('');

for (let i = 0; i < 10; i++) {
    results.value.push({
        key: `sugg-${i}`,
        title: 'Lorem Ipsum',
        subtitle: '23-03-2023',
        text: 'Lorem ipsum dolor sit amet consectetuer adipicising elit',
    })
}

let activeChannels = computed(() => {
    return channels.value.filter((channel) => channel.active).map((channel) => channel.key).join(',');
});

const doSearch = debounce(async () => {
    const response = await fetch(`${endpoint}/article-search/search-channel/${activeChannels.value}?term=${encodeURIComponent(searchTerm.value)}`);
    const data = await response.json();

    // console.log(data);
    results.value.push(data[0]);
}, 300);

const linkText = ref(JSON.parse(new URLSearchParams(window.location.search).get('data')).selectionText);
const openInNewTab = ref(false);
</script>

<template>
    <div class="flex flex-row px-4 py-2">
        <aside class="basis-1/4">
            <h1 class="text-xl">Kanalen</h1>
            <div>
                <Checkbox v-for="channel in channels"
                          :key="channel.key"
                          :id="channel.key"
                          v-model="channel.active"
                          :label="channel.label"
                />
            </div>
        </aside>
        <main class="basis-3/4 space-y-2">
            <TextInput v-model="linkText" placeholder="Link text"/>
            <SuggestionTextInput :permanent="true" :suggestions="results" placeholder="URL or title"/>
            <Checkbox id="new-tab" v-model="openInNewTab" label="Open in new tab" />
            <div class="flex flex-row space-x-2">
                <Button type="normal">Cancel</Button>
                <Button type="primary">Apply</Button>
            </div>
        </main>
    </div>
</template>
