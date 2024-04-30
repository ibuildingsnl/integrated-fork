<script setup>
import {computed, onMounted, ref, watchEffect} from "vue";
import debounce from "lodash.debounce";
import Checkbox from "../form/Checkbox.vue";
import TextInput from "../form/TextInput.vue";
import SuggestionTextInput from "../form/SuggestionTextInput.vue";
import Button from "../form/Button.vue";
import MaxHeightScroller from "../layout/MaxHeightScroller.vue";
import ArticleSearchWarningStatus from "./statuses/ArticleSearchWarningStatus.vue";
import ArticleSearchSuccessStatus from "./statuses/ArticleSearchSuccessStatus.vue";
import ArticleSearchLoadingStatus from "./statuses/ArticleSearchLoadingStatus.vue";

const props = defineProps({
    channels: String,
    contentTypes: String,
});

const searchParams = JSON.parse(new URLSearchParams(window.location.search).get('data'));
const endpoint = `${window.location.protocol}//${window.location.host}/admin`;
const linkText = ref(searchParams.selectionText ?? '');
const openInNewTab = ref(searchParams.openInNewTab ?? false);
const results = ref([]);
const searchTerm = ref(searchParams.url ?? '');
const selections = ref([]);
const loading = ref(false);

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
    if (activeChannels.value.length === 0 || searchTerm.value.length === 0) {
        return;
    }

    if (hasValidUrl.value) {
        return;
    }

    return doSearch();
};

const doSearch = debounce(async () => {
    try {
        loading.value = true;
        let url = `${endpoint}/article-search/search-channel/${activeChannels.value}`;
        url = url.endsWith('/') ? url.substring(0, url.length - 1) : url;
        url = `${url}?term=${encodeURIComponent(searchTerm.value)}`;

        if(activeContentTypes.value.length > 0) {
            url = `${url}&contentTypeIds=${activeContentTypes.value}`;
        }

        const response = await fetch(url);

        if (!response.ok) {
            loading.value = false;
            throw response.errored;
        }

        results.value = await response.json();
        selections.value = [];
        loading.value = false;
    } catch (e) {
        results.value = [];
    }
}, 300);

const finishSelection = () => {
    if (hasValidUrl.value) {
        window.parent.postMessage({
            mceAction: 'insertContent',
            content: `<a href="${searchTerm.value}"${openInNewTab.value ? ' target="_blank"' : ''}>${linkText.value}</a>`
        }, '*');
        window.parent.postMessage({
            mceAction: 'close',
        }, '*');

        return;
    }

    if (selections.value.length === 0 || results.value.length === 0 || linkText.value.length === 0) {
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

watchEffect(() => {
    const hasActiveChannels = activeChannels.value.length > 0;
    // Dependency on activeContentTypes is necessary, even though hasContentTypes is unused
    const hasContentTypes = activeContentTypes.value.length > 0;
    const hasSearchTerm = searchTerm.value.length > 0;

    if(hasActiveChannels && hasSearchTerm) {
        attemptSearch();
    }
});
</script>

<template>
    <div class="flex flex-row">
        <aside>
            <div class="aside-item-container">
                <div class="aside-item-header">
                    <h3 class="aside-item-title">Kanalen</h3>
                </div>
                <MaxHeightScroller max-height="calc(50vh - 35px)">
                    <Checkbox
                        v-for="channel in channels"
                        :key="channel.key"
                        :id="channel.key"
                        v-model="channel.active"
                        :label="channel.label"
                    />
                </MaxHeightScroller>
            </div>

            <div class="aside-item-container">
                <div class="aside-item-header">
                    <h3 class="aside-item-title">Content type</h3>
                </div>
                <MaxHeightScroller max-height="calc(50vh - 35px)">
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

        <main class="flex flex-col justify-between">
            <div class="">
                <TextInput v-model="linkText" :error-text="linkText.length > 0 ? '' : 'Please fill in the link text'" placeholder="Link text"/>
                <SuggestionTextInput
                    :permanent="true"
                    :suggestions="results"
                    placeholder="URL or search term"
                    :multiple="false"
                    v-model="searchTerm"
                    v-model:selections="selections"
                    @searchConfirm="attemptSearch"
                    :error-text="searchTerm.length > 0 ? '' : 'Enter a search term or URL'"
                >
                    <ArticleSearchLoadingStatus v-if="loading"/>
                    <ArticleSearchWarningStatus text="Enter a search term to begin searching" v-else-if="searchTerm.length === 0"/>
                    <ArticleSearchWarningStatus text="Please select a channel" v-else-if="activeChannels.length === 0"/>
                    <ArticleSearchWarningStatus text="No results" v-else-if="results.length === 0 && !hasValidUrl"/>
                    <ArticleSearchWarningStatus text="Please fill in the link text" v-else-if="linkText.length === 0"/>
                    <ArticleSearchSuccessStatus v-else/>
                </SuggestionTextInput>
                <div class="form-group mt-2">
                    <Checkbox id="new-tab" v-model="openInNewTab" label="Open in new tab"/>
                </div>
            </div>

            <div class="flex flex-row space-x-2 self-end">
                <Button @click.prevent.stop="cancel" type="normal">Cancel</Button>
                <Button @click.prevent.stop="finishSelection" type="primary" :disabled="!isStateValid">Apply</Button>
            </div>
        </main>
    </div>
</template>
