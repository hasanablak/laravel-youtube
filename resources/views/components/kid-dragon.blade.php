{{--
    Minik ejder rehber: sol alt köşede duran, aşağı kaydırmayı özendiren 3D karakter.
    Davranışlar ve çizim: resources/js/kid-dragon.js (ayrı Vite girişi, Three.js sadece bu sayfada yüklenir).

    ÖNEMLİ: Bu bileşen #app'in DIŞINDA render edilmeli (sayfada @push('footer') ile).
    #app içindeki DOM'u Vue yeniden oluşturur; canvas'a verilen referanslar kaybolur.
    Sol altta durur, çünkü sağ altta DailyWatchLimitBubble var.
--}}
<div id="kid-dragon-wrap" aria-hidden="false">
    <div class="kid-dragon-hint" id="kid-dragon-hint" aria-hidden="true">
        <svg viewBox="0 0 34 22"><path d="M5 5l12 12L29 5" stroke="#FF8FB1" stroke-width="5" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>
        <svg viewBox="0 0 34 22"><path d="M5 5l12 12L29 5" stroke="#FF8FB1" stroke-width="5" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>
    </div>
    <canvas id="kid-dragon" tabindex="0" role="img" aria-label="Minik ejder: aşağıda daha çok kanal var"></canvas>
</div>

<style>
    #kid-dragon-wrap {
        position: fixed; z-index: 30; pointer-events: none;
        left: calc(6px + env(safe-area-inset-left, 0px)); bottom: calc(4px + env(safe-area-inset-bottom, 0px));
        width: clamp(150px, 22vw, 230px); aspect-ratio: 1 / 1.1; will-change: transform, opacity;
    }
    #kid-dragon { width: 100%; height: 100%; display: block; pointer-events: auto; cursor: pointer; border-radius: 30px; -webkit-tap-highlight-color: transparent; }
    #kid-dragon:focus-visible { outline: 3px solid #B9A4FF; outline-offset: 3px; }

    .kid-dragon-hint {
        position: absolute; left: 74%; bottom: 46%; display: flex; flex-direction: column; align-items: center;
        background: #FFFFFF; border-radius: 26px; padding: 10px 12px 6px; box-shadow: 0 6px 18px rgba(232, 120, 160, .22);
        opacity: 0; transform: scale(.6) translateY(10px); transform-origin: 0 100%;
        transition: opacity .25s, transform .35s cubic-bezier(.3, 1.6, .5, 1);
    }
    .kid-dragon-hint::after { content: ""; position: absolute; left: -7px; bottom: 16px; width: 16px; height: 16px; background: #FFFFFF; transform: rotate(45deg); border-radius: 3px; }
    .kid-dragon-hint.is-on { opacity: 1; transform: none; }
    .kid-dragon-hint svg { width: 34px; height: 22px; animation: kid-dragon-chevron 1s ease-in-out infinite; }
    .kid-dragon-hint svg + svg { margin-top: -10px; animation-delay: .15s; opacity: .55; }

    .kid-dragon-confetti {
        position: fixed; z-index: 31; width: 10px; height: 14px; border-radius: 3px; pointer-events: none;
        animation: kid-dragon-confetti 1.3s cubic-bezier(.2, .7, .4, 1) forwards;
    }

    @keyframes kid-dragon-chevron { 0%, 100% { transform: translateY(-3px) } 50% { transform: translateY(4px) } }
    @keyframes kid-dragon-confetti {
        from { transform: translate(0, 0) rotate(0); opacity: 1 }
        to { transform: translate(var(--dx), var(--dy)) rotate(var(--r)); opacity: 0 }
    }
    @media (prefers-reduced-motion: reduce) { .kid-dragon-hint svg { animation: none; } }
</style>

@vite('resources/js/kid-dragon.js')
