import { defineStore } from 'pinia';

export const useWatchLimitStore = defineStore('watchLimit', {
    state: () => ({
        dailyLimit: 0,
        dailyWatchCount: 0,
        remaining: 0,
        limitExceeded: false,
        loaded: false,
        loading: false,
    }),
    getters: {
        progressPercent(state) {
            if (!state.dailyLimit) {
                return 0;
            }

            return Math.round((state.dailyWatchCount / state.dailyLimit) * 100);
        },
    },
    actions: {
        applyPayload(data) {
            this.dailyLimit = data.daily_limit ?? 0;
            this.dailyWatchCount = data.daily_watch_count ?? 0;
            this.remaining = data.remaining ?? 0;
            this.limitExceeded = !!data.limit_exceeded;
            this.loaded = true;
        },
        async fetchLimit(videoId) {
            this.loading = true;

            try {
                const { data } = await window.axios.get('/api/check-daily-watch-limit', {
                    params: videoId ? { video_id: videoId } : {},
                });
                this.applyPayload(data);
                return data;
            } finally {
                this.loading = false;
            }
        },
    },
});
