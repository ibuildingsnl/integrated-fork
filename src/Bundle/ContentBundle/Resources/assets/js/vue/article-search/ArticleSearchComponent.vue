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
import RadioGroup from "../form/RadioGroup.vue";

const props = defineProps({
    channels: String,
    contentTypes: String,
    translations: String,
});

const translations = JSON.parse(props.translations);
const rawSearchParams = new URLSearchParams(window.location.search).get('data');
const searchParams = rawSearchParams ? JSON.parse(rawSearchParams) : {};
const endpoint = `${window.location.protocol}//${window.location.host}/admin`;
const linkText = ref(searchParams.selectionText ?? '');
const linkTitle = ref(searchParams.title ?? '');
const openInNewTab = ref(searchParams.openInNewTab ?? false);
const existing = ref(searchParams.existing ?? false);
const results = ref([]);
const searchTerm = ref(searchParams.url ?? '');
const selections = ref([]);
const loading = ref(false);

console.log(searchParams);

const channels = ref(JSON.parse(props.channels).map((channel) => {
    return {value: channel.key, ...channel}
}));

const contentTypes = ref(JSON.parse(props.contentTypes).map((contentType) => {
    return {active: false, ...contentType}
}));

const activeChannel = ref('');

const activeContentTypes = computed(() => {
    return contentTypes.value.filter((contentType) => contentType.active).map((contentType) => contentType.key).join(',');
});

const ensureHttps = (url) => {
    if (url.startsWith('/') || url.startsWith('#') || url.startsWith('mailto:') || url.startsWith('tel:')) {
        return url;
    }
    if (!url.startsWith('https://') && !url.startsWith('http://')) {
        return `https://${url}`;
    }
    return url;
};

const hasValidUrl = computed(() => {
    try {
        return new URL(searchTerm.value);
    } catch {
        if (searchTerm.value.startsWith('/') || searchTerm.value.startsWith('#') || searchTerm.value.startsWith('mailto:') || searchTerm.value.startsWith('tel:')) {
            return true;
        }

        if (searchTerm.value.startsWith('www')) {
            return true;
        }

        return false;
    }
});

const isStateValid = computed(() => {
    // Either valid url and link text contains something or valid selection and link text contains something
    return (hasValidUrl.value || (selections.value.length > 0 && results.value.length > 0)) && linkText.value.length > 0 && linkTitle.value.length > 0;
});

const attemptSearch = () => {
    if (activeChannel.value.length === 0 || searchTerm.value.length === 0) {
        results.value = [];
        return;
    }

    if (hasValidUrl.value) {
        results.value = [];
        return;
    }

    return doSearch();
};

const doSearch = debounce(async () => {
    try {
        loading.value = true;
        let url = `${endpoint}/article-search/search-channel/${activeChannel.value}`;
        url = url.endsWith('/') ? url.substring(0, url.length - 1) : url;
        url = `${url}?term=${encodeURIComponent(searchTerm.value)}`;

        if (activeContentTypes.value.length > 0) {
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
        const finalUrl = ensureHttps(searchTerm.value);  // Ensure https:// for searchTerm

        if (existing.value) {
            window.parent.postMessage({
                mceAction: 'linkMakerReplace',
                title: linkTitle.value,
                href: finalUrl,
                newTab: openInNewTab.value && !finalUrl.startsWith('#') && !finalUrl.startsWith('mailto:') && !finalUrl.startsWith('tel:'),
                linkText: linkText.value,
            }, '*');
        } else {
            window.parent.postMessage({
                mceAction: 'insertContent',
                content: `<a href="${finalUrl}"${openInNewTab.value && !finalUrl.startsWith('#') && !finalUrl.startsWith('mailto:') && !finalUrl.startsWith('tel:') ? ' target="_blank"' : ''} title="${linkTitle.value}">${linkText.value}</a>`
            }, '*');
        }

        window.parent.postMessage({
            mceAction: 'close',
        }, '*');

        return;
    }

    if (selections.value.length === 0 || results.value.length === 0 || linkText.value.length === 0 || linkTitle.value.length === 0) {
        return;
    }

    const id = selections.value[0];
    const item = results.value.filter((result) => result.id === id)[0];
    const finalItemUrl = ensureHttps(item.url);  // Ensure https:// for item.url

    if (existing.value) {
        window.parent.postMessage({
            mceAction: 'linkMakerReplace',
            title: linkTitle.value,
            href: finalItemUrl,
            newTab: openInNewTab.value && !finalItemUrl.startsWith('#'),
            linkText: linkText.value,
        }, '*');
    } else {
        window.parent.postMessage({
            mceAction: 'insertContent',
            content: `<a href="${finalItemUrl}"${openInNewTab.value && !finalItemUrl.startsWith('#') && !finalItemUrl.startsWith('mailto:') && !finalItemUrl.startsWith('tel:') ? ' target="_blank"' : ''} title="${linkTitle.value}">${linkText.value}</a>`
        }, '*');
    }

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

    activeChannel.value = channels.value[0].value;
});

watchEffect(() => {
    const hasActiveChannel = activeChannel.value.length > 0;
    // Dependency on activeContentTypes is necessary, even though hasContentTypes is unused
    const hasContentTypes = activeContentTypes.value.length > 0;
    const hasSearchTerm = searchTerm.value.length > 0;

    if (hasActiveChannel && hasSearchTerm) {
        attemptSearch();
    }
});
</script>

<template>
    <div class="flex flex-row">
        <aside>
            <div class="aside-item-container">
                <div class="aside-item-header">
                    <h3 class="aside-item-title">{{ translations.channels }}</h3>
                </div>
                <MaxHeightScroller max-height="calc(50vh - 35px)">
                    <RadioGroup name="channel" :radio-buttons="channels" v-model="activeChannel" />
                </MaxHeightScroller>
            </div>

            <div class="aside-item-container">
                <div class="aside-item-header">
                    <h3 class="aside-item-title">{{ translations.content_types }}</h3>
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
                <TextInput
                    v-model="linkText"
                    :error-text="linkText.length > 0 ? '' : translations.require_link_text"
                    :placeholder="translations.link_text"
                />
                <TextInput
                    v-model="linkTitle"
                    :error-text="linkTitle.length > 0 ? '' : translations.require_link_title"
                    :placeholder="translations.link_title"
                />
                <SuggestionTextInput
                    :permanent="true"
                    :suggestions="results"
                    :placeholder="translations.url_or_searchterm"
                    :multiple="false"
                    v-model="searchTerm"
                    v-model:selections="selections"
                    @searchConfirm="attemptSearch"
                    :error-text="searchTerm.length > 0 ? '' : translations.require_searchterm"
                >
                    <ArticleSearchLoadingStatus :text="translations.searching" v-if="loading"/>
                    <ArticleSearchWarningStatus :text="translations.require_searchterm" v-else-if="searchTerm.length === 0"/>
                    <ArticleSearchWarningStatus :text="translations.select_channel" v-else-if="activeChannel.length === 0"/>
                    <ArticleSearchWarningStatus :text="translations.no_results" v-else-if="results.length === 0 && !hasValidUrl"/>
                    <ArticleSearchWarningStatus :text="translations.require_link_text" v-else-if="linkText.length === 0"/>
                    <ArticleSearchWarningStatus :text="translations.require_link_title" v-else-if="linkTitle.length === 0"/>
                    <ArticleSearchSuccessStatus :text="translations.ready" v-else/>
                </SuggestionTextInput>
                <div class="form-group mt-2">
                    <Checkbox id="new-tab" v-model="openInNewTab" :label="translations.new_tab"/>
                </div>
            </div>

            <div class="flex flex-row space-x-2 self-end">
                <Button @click.prevent.stop="cancel" type="normal">{{ translations.cancel }}</Button>
                <Button @click.prevent.stop="finishSelection" type="primary" :disabled="!isStateValid">{{ translations.apply }}</Button>
            </div>
        </main>
    </div>
</template>
