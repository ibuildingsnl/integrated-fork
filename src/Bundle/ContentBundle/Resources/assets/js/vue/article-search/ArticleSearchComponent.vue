<script setup>
import {computed, onMounted, ref} from "vue";
import debounce from "lodash.debounce";
import Checkbox from "../form/Checkbox.vue";
import TextInput from "../form/TextInput.vue";
import SuggestionTextInput from "../form/SuggestionTextInput.vue";
import Button from "../form/Button.vue";
import MaxHeightScroller from "../layout/MaxHeightScroller.vue";

const props = defineProps({
    channels: String,
    contentTypes: String,
});

const endpoint = `${window.location.protocol}//${window.location.host}/admin`;
const linkText = ref(JSON.parse(new URLSearchParams(window.location.search).get('data')).selectionText);
const openInNewTab = ref(false);
const results = ref([]);
const searchTerm = ref('');
const selections = ref([]);

const channels = ref(JSON.parse(props.channels).map((channel) => {
    return {active: false, ...channel}
}));

const contentTypes = ref(JSON.parse(props.contentTypes).map((contentType) => {
    return {active: false, ...contentType}
}));

const activeChannels = computed(() => {
    return channels.value.filter((channel) => channel.active).map((channel) => channel.key).join(',');
});

const activeContentTypes = computed(() => {
    return contentTypes.value.filter((contentType) => contentType.active).map((contentType) => contentType.key).join(',');
});

const hasValidUrl = computed(() => {
    try {
        return new URL(searchTerm.value);
    } catch {
        return false;
    }
});

const isStateValid = computed(() => {
    // Either valid url and link text contains something or valid selection and link text contains something
    return (hasValidUrl.value || (selections.value.length > 0 && results.value.length > 0)) && linkText.value.length > 0;
});

const attemptSearch = () => {
    if (activeChannels.value.length === 0 || activeContentTypes.value.length === 0 || searchTerm.value.length === 0) {
        return;
    }

    if(hasValidUrl.value) {
        return;
    }

    return doSearch();
};

const doSearch = debounce(async () => {
    try {
        let url = `${endpoint}/article-search/search-channel/${activeChannels.value}`;
        url = url.endsWith('/') ? url.substring(0, url.length - 1) : url;
        const response = await fetch(`${url}?term=${encodeURIComponent(searchTerm.value)}&contentTypeIds=${activeContentTypes.value}`);

        if (!response.ok) {
            throw response.errored;
        }

        results.value = await response.json();
        selections.value = [];
    } catch (e) {
        results.value = [];
    }
}, 300);

const finishSelection = () => {
    if(hasValidUrl.value) {
        window.parent.postMessage({
            mceAction: 'insertContent',
            content: `<a href="${searchTerm.value}"${openInNewTab.value ? ' target="_blank"' : ''}>${linkText.value}</a>`
        }, '*');
        window.parent.postMessage({
            mceAction: 'close',
        }, '*');

        return;
    }

    if(selections.value.length === 0 || results.value.length === 0 || linkText.value.length === 0) {
        return;
    }

    const id = selections.value[0];
    const item = results.value.filter((result) => result.id === id)[0];

    window.parent.postMessage({
        mceAction: 'insertContent',
        content: `<a href="${item.url}"${openInNewTab.value ? ' target="_blank"' : ''}>${linkText.value}</a>`
    }, '*');
    window.parent.postMessage({
        mceAction: 'close',
    }, '*');
};

const cancel = () => {
    window.parent.postMessage({
        mceAction: 'close',
    }, '*');
};

onMounted(() => {
    if (channels.value.length > 1) {
        return;
    }

    channels.value = [{...channels.value[0], active: true}];
});
</script>

<template>
    <div class="flex flex-row px-4 py-2 h-screen space-x-4">
        <aside class="basis-1/4 space-y-2">
            <div>
                <h1 class="text-xl">Kanalen</h1>
                <MaxHeightScroller max-height="160px">
                    <Checkbox
                        v-for="channel in channels"
                        :key="channel.key"
                        :id="channel.key"
                        v-model="channel.active"
                        :label="channel.label"
                    />
                </MaxHeightScroller>
            </div>

            <div>
                <h1 class="text-xl">Content Type</h1>
                <MaxHeightScroller max-height="260px">
                    <Checkbox
                        v-for="contentType in contentTypes"
                        :key="contentType.key"
                        :id="contentType.key"
                        v-model="contentType.active"
                        :label="contentType.label"
                    />
                </MaxHeightScroller>
            </div>
        </aside>

        <main class="basis-3/4 space-y-2 flex flex-col justify-between">
            <div class="space-y-2">
                <TextInput v-model="linkText" placeholder="Link text"/>
                <SuggestionTextInput
                    :permanent="true"
                    :suggestions="results"
                    placeholder="URL or search term"
                    :multiple="false"
                    v-model="searchTerm"
                    v-model:selections="selections"
                    @searchConfirm="attemptSearch"
                />
                <Checkbox id="new-tab" v-model="openInNewTab" label="Open in new tab"/>
            </div>

            <div class="flex flex-row space-x-2 self-end">
                <Button @click.prevent.stop="cancel" type="normal">Cancel</Button>
                <Button @click.prevent.stop="finishSelection" type="primary" :disabled="!isStateValid">Apply</Button>
            </div>
        </main>
    </div>
</template>
