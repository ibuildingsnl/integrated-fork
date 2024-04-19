<script setup>
import {computed, reactive, ref} from "vue";
import debounce from "lodash.debounce";
import Checkbox from "../form/Checkbox.vue";
import TextInput from "../form/TextInput.vue";
import SuggestionTextInput from "../form/SuggestionTextInput.vue";
import Button from "../form/Button.vue";

const endpoint = `${window.location.protocol}//${window.location.host}/admin`;

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
let selections = ref([]);

for (let i = 0; i < 10; i++) {
    results.value.push({
        key: `sugg-${i}`,
        title: 'Lorem Ipsum',
        subtitle: 'Article | 23-03-2023',
        text: 'Lorem ipsum dolor sit amet consectetuera adipicising elit',
    })
}

let activeChannels = computed(() => {
    return channels.value.filter((channel) => channel.active).map((channel) => channel.key).join(',');
});

const doSearch = debounce(async () => {
    try {
        let url = `${endpoint}/article-search/search-channel/${activeChannels.value}`;
        url = url.endsWith('/') ? url.substring(0, url.length - 1) : url;
        const response = await fetch(`${url}?term=${encodeURIComponent(searchTerm.value)}`);

        if (!response.ok) {
            throw response.errored;
        }

        const data = await response.json();

        results.value = data.map((entry) => {
            const pubTime = new Date(entry.pub_time);
            const content = entry.content.toString();
            const text = content.length > 0 ? content : '\u00A0';
            const typeName = [entry.type_name.substring(0, 1).toLocaleUpperCase(), entry.type_name.substring(1)].join('');

            return {
                key: entry.id,
                title: entry.title,
                text: text,
                subtitle: `${typeName} | ${pubTime.toDateString()}`,
            };
        });
    } catch (e) {
        results.value = [];
    }
}, 300);

const linkText = ref(JSON.parse(new URLSearchParams(window.location.search).get('data')).selectionText);
const openInNewTab = ref(false);
</script>

<template>
    <div class="flex flex-row px-4 py-2">
        <aside class="basis-1/4">
            <h1 class="text-xl">Kanalen</h1>
            <div>
                <Checkbox
                    v-for="channel in channels"
                    :key="channel.key"
                    :id="channel.key"
                    v-model="channel.active"
                    :label="channel.label"
                />
            </div>
        </aside>
        <main class="basis-3/4 space-y-2">
            <TextInput v-model="linkText" placeholder="Link text"/>
            <SuggestionTextInput
                :permanent="true"
                :suggestions="results"
                placeholder="URL or search term"
                :multiple="false"
                v-model="searchTerm"
                v-model:selections="selections"
                @searchConfirm="doSearch"
            />
            <Checkbox id="new-tab" v-model="openInNewTab" label="Open in new tab"/>
            <div class="flex flex-row space-x-2">
                <Button type="normal">Cancel</Button>
                <Button type="primary">Apply</Button>
            </div>
            <div>
                <p>{{ selections.toString() }}</p>
                <p>{{ searchTerm }}</p>
            </div>
        </main>
    </div>
</template>
