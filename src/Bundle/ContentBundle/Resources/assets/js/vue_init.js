import {createApp} from 'vue';
import ArticleSearchComponent from "./components/ArticleSearchComponent.vue";

const app = createApp({
    components: {
        ArticleSearchComponent
    },
    delimiters: ['${', '}$'],
});

app.mount('#app');
