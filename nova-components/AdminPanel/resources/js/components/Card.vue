<template>
    <card>
        <div class="px-3 py-3 grid-container grid grid-cols-8">
            <card class="col-span-1 flex flex-col items-center justify-center px-3 py-3 bg-grey mr-2 mb-2">
                <heading :level="3" class="mb-3">Redis clear</heading>
                <div class="flex items-center justify-center">
                    <button type="button"
                            class="inline-flex items-center px-4 py-2 btn btn-default btn-danger"
                            :class="this.loading? 'cursor-not-allowed':''"
                            :disabled="this.loading"
                            @click.prevent="redisClear"
                    >
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
                             :class="this.loading? 'block':'hidden'"
                             xmlns="http://www.w3.org/2000/svg"
                             fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Clear
                    </button>
                </div>
            </card>

            <card class="col-span-1 flex flex-col items-center justify-center px-3 py-3 bg-grey mr-2 mb-2">
                <heading :level="3" class="mb-3">Cache clear</heading>
                <button @click.prevent="clearCache"
                        class="btn btn-default btn-danger"
                >
                    Clear
                </button>
            </card>

            <card class="col-span-2 flex flex-col items-center justify-center px-3 py-3 bg-grey mr-2 mb-2">
                <heading :level="3" class="mb-3">Restart horizon</heading>
                <button
                    class="btn btn-default btn-primary"
                    @click.prevent="horizonRestart"
                >
                    Restart
                </button>
            </card>

            <card class="col-span-4 flex flex-col items-center justify-center px-3 py-3 bg-grey mb-2">
                <heading :level="3" class="mb-3">Search</heading>
                <div class="row grid grid-cols-6">
                    <div class="col-span-4">
                        <div class="btn-research_locale">
                            <select name="lang"
                                    id="lang"
                                    v-model="locale"
                                    class=" form-control form-input form-input-bordered">
                                <option value="">All locales</option>
                                <option :value="locale"
                                        :key="index"
                                        v-for="(name,locale, index) in locales">{{ name }}
                                </option>
                            </select>
                            <button
                                class="btn btn-default btn-primary"
                                @click.prevent="reindexSite"
                            >
                                Reindex {{ locale }}
                            </button>
                        </div>
                    </div>

                    <div class="col-span-2">
                        <button
                            class="btn btn-default btn-primary"
                            @click.prevent="reindexBlog"
                        >
                            Reindex blog
                        </button>
                    </div>
                </div>
            </card>
        </div>
    </card>
</template>

<script>
import api from "../api";

export default {
    props: [
        'card',
    ],

    data: () => ({
        locale : '',
        locales: {},
        loading: false
    }),

    mounted() {
        this.getLocales();
    },

    methods: {
        async horizonRestart() {
            const response = (await api.restartHorizon());

            if (response.status === 200) {
                this.$toasted.show('Horizon restart successfully', {type: 'success'});
            }
        },

        async redisClear() {
            this.loading = true;
            const response = (await api.redisClear());

            if (response.status === 200) {
                this.loading = false;
                this.$toasted.show('Cache cleared successfully', {type: 'success'});
            }
        },

        async clearCache() {
            const response = (await api.clearCache());

            if (response.status === 200) {
                this.$toasted.show('Cache cleared successfully', {type: 'success'});
            }
        },

        async reindexSite() {
            const response = (await api.reindexSite(this.locale));

            if (response.status === 200) {
                this.$toasted.show('Site reindex started successfully', {type: 'success'});
            }
        },

        async reindexBlog() {
            const response = (await api.reindexBlog());

            if (response.status === 200) {
                this.$toasted.show('Blog reindex started successfully', {type: 'success'});
            }
        },

        async getLocales() {
            this.locales = (await api.getLocales()).data;
        }
    },
}
</script>
