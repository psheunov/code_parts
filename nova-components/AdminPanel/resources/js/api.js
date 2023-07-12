export default {
    async restartHorizon() {
        return Nova.request().get('/nova-vendor/admin-panel/restart-horizon');
    },

    async clearCache() {
        return Nova.request().get('/nova-vendor/admin-panel/cache-clear');
    },

    async redisClear() {
        return Nova.request().get('/nova-vendor/admin-panel/redis-clear');
    },

    async reindexBlog() {
        return Nova.request().get('/nova-vendor/admin-panel/reindex/blog');
    },

    async reindexSite(locale = '') {
        return Nova.request().get('/nova-vendor/admin-panel/reindex/site/' + locale);
    },

    async getLocales() {
        return Nova.request().get('/nova-vendor/admin-panel/locales');
    }
};
