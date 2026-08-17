<template>
    <div
        v-if="loaded"
        class="watch-limit-bubble"
        :class="{ expanded: isExpanded, exhausted: limitExceeded }"
    >
        <transition name="bubble-card">
            <div v-if="isExpanded" class="bubble-card">
                <div class="bubble-card-head">
                    <span class="bubble-emoji">{{ limitExceeded ? '👋' : '📺' }}</span>
                    <div>
                        <p class="bubble-title">{{ limitExceeded ? 'Mola zamanı!' : 'Bugünkü izleme hakkın' }}</p>
                        <p class="bubble-subtitle">
                            {{ limitExceeded ? 'Yarın tekrar izleyebilirsin.' : 'Her video sana yeni şeyler öğretir!' }}
                        </p>
                    </div>
                </div>

                <div class="bubble-progress">
                    <div class="bubble-progress-track">
                        <div class="bubble-progress-fill" :style="{ width: progressPercent + '%' }"></div>
                    </div>
                    <div class="bubble-stats">
                        <span>Kullanılan: {{ dailyWatchCount }}</span>
                        <span>Kalan: {{ remaining }}</span>
                    </div>
                </div>

                <div class="bubble-dots">
                    <span
                        v-for="index in dailyLimit"
                        :key="index"
                        class="bubble-dot"
                        :class="{ used: index <= dailyWatchCount }"
                    ></span>
                </div>
            </div>
        </transition>

        <button
            type="button"
            class="bubble-trigger"
            :aria-expanded="isExpanded"
            aria-label="Günlük izleme hakkını göster"
            @click="toggleExpanded"
        >
            <span class="bubble-trigger-emoji">{{ limitExceeded ? '💜' : '🎬' }}</span>
            <span class="bubble-trigger-count">{{ remaining }}</span>
            <span class="bubble-trigger-label">kaldı</span>
        </button>
    </div>
</template>

<script>
import { mapActions, mapState } from 'pinia';
import { useWatchLimitStore } from '../stores/watchLimit.js';

export default {
    name: 'DailyWatchLimitBubble',
    data() {
        return {
            isExpanded: false,
        };
    },
    computed: {
        ...mapState(useWatchLimitStore, [
            'dailyLimit',
            'dailyWatchCount',
            'remaining',
            'limitExceeded',
            'loaded',
            'progressPercent',
        ]),
    },
    mounted() {
        this.fetchLimit();
        window.addEventListener('watch-limit:refresh', this.handleRefresh);
    },
    beforeUnmount() {
        window.removeEventListener('watch-limit:refresh', this.handleRefresh);
    },
    methods: {
        ...mapActions(useWatchLimitStore, ['fetchLimit']),
        toggleExpanded() {
            this.isExpanded = !this.isExpanded;
        },
        handleRefresh() {
            this.fetchLimit();
        },
    },
};
</script>

<style scoped>
.watch-limit-bubble {
    position: fixed;
    right: 20px;
    bottom: 20px;
    z-index: 60;
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 12px;
    font-family: 'Comic Sans MS', 'Chalkboard SE', cursive, sans-serif;
}

.bubble-card {
    width: min(380px, calc(100vw - 40px));
    padding: 16px 18px;
    border-radius: 24px;
    background: linear-gradient(145deg, #fff9c4 0%, #ffe082 45%, #ffcc80 100%);
    border: 3px solid #ffb74d;
    box-shadow: 0 14px 30px rgba(255, 152, 0, 0.28);
}

.watch-limit-bubble.exhausted .bubble-card {
    background: linear-gradient(145deg, #ffe0e0 0%, #ffcdd2 45%, #ef9a9a 100%);
    border-color: #ef5350;
    box-shadow: 0 14px 30px rgba(239, 83, 80, 0.25);
}

.bubble-card-head {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 14px;
}

.bubble-emoji {
    font-size: 34px;
    line-height: 1;
}

.bubble-title {
    font-size: 18px;
    font-weight: 700;
    color: #5d4037;
    margin: 0;
}

.bubble-subtitle {
    margin: 4px 0 0;
    font-size: 13px;
    color: #795548;
}

.bubble-progress-track {
    height: 14px;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.65);
    overflow: hidden;
    box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.08);
}

.bubble-progress-fill {
    height: 100%;
    border-radius: 999px;
    background: linear-gradient(90deg, #66bb6a, #43a047);
    transition: width 0.35s ease;
}

.watch-limit-bubble.exhausted .bubble-progress-fill {
    background: linear-gradient(90deg, #ef5350, #e53935);
}

.bubble-stats {
    display: flex;
    justify-content: space-between;
    margin-top: 8px;
    font-size: 13px;
    font-weight: 700;
    color: #6d4c41;
}

.bubble-dots {
    display: flex;
    justify-content: center;
    gap: 8px;
    margin-top: 14px;
}

.bubble-dot {
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.9);
    border: 2px solid #ffb74d;
    transition: transform 0.2s ease, background 0.2s ease;
}

.bubble-dot.used {
    background: #ff9800;
    transform: scale(0.92);
}

.watch-limit-bubble.exhausted .bubble-dot.used {
    background: #ef5350;
    border-color: #e53935;
}

.bubble-trigger {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 88px;
    height: 88px;
    border: 3px solid #fff;
    border-radius: 999px;
    cursor: pointer;
    color: #fff;
    background: linear-gradient(145deg, #42a5f5 0%, #7e57c2 100%);
    box-shadow: 0 10px 24px rgba(66, 165, 245, 0.45);
    animation: bubbleFloat 2.4s ease-in-out infinite;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.bubble-trigger:hover {
    transform: scale(1.05);
    box-shadow: 0 14px 28px rgba(66, 165, 245, 0.55);
}

.watch-limit-bubble.exhausted .bubble-trigger {
    background: linear-gradient(145deg, #ef5350 0%, #ab47bc 100%);
    box-shadow: 0 10px 24px rgba(239, 83, 80, 0.4);
}

.bubble-trigger-emoji {
    font-size: 22px;
    line-height: 1;
}

.bubble-trigger-count {
    font-size: 28px;
    font-weight: 800;
    line-height: 1;
    margin-top: 2px;
}

.bubble-trigger-label {
    font-size: 12px;
    font-weight: 700;
    opacity: 0.95;
}

.bubble-card-enter-active,
.bubble-card-leave-active {
    transition: opacity 0.2s ease, transform 0.2s ease;
}

.bubble-card-enter-from,
.bubble-card-leave-to {
    opacity: 0;
    transform: translateY(10px) scale(0.96);
}

@keyframes bubbleFloat {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-6px);
    }
}

@media (max-width: 640px) {
    .watch-limit-bubble {
        right: 14px;
        bottom: 14px;
    }

    .bubble-trigger {
        width: 76px;
        height: 76px;
    }

    .bubble-trigger-count {
        font-size: 24px;
    }
}
</style>
