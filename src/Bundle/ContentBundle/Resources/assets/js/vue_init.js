import {createApp} from 'vue';
import ArticleSearchComponent from "./vue/article-search/ArticleSearchComponent.vue";
import clickOutside from "./vue/clickOutside";

const app = createApp({
    components: {
        ArticleSearchComponent
    },
    delimiters: ['${', '}$'],
});

app.directive('click-outside', clickOutside);

app.mount('#app');
