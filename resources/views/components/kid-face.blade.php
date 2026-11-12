{{--
    Gözleriyle fareyi (veya dokunulan noktayı) takip eden sevimli yüz.
    Hâlleri (data-mood): happy, surprised, cute, laugh, wink.
      - Fare üstüne gelince: laugh       - Tıklayınca: surprised → cute
      - Fare yaklaşınca: cute (utanır)    - Fare hızlı hareket edince: surprised
      - Boştayken ara sıra kendiliğinden hâl değiştirir ve göz kırpar.
    Her parça sadece ilgili hâllerin sınıfını taşır (m-happy, m-cute ...); CSS görünürlüğü yönetir.
--}}
<button type="button" class="kid-face relative h-14 w-14 flex-none rounded-full focus:outline-none sm:h-16 sm:w-16"
        data-mood="happy" aria-label="Gülen yüz">
    <svg viewBox="0 0 100 100" class="h-full w-full overflow-visible" aria-hidden="true">
        {{-- Yüz --}}
        <circle cx="50" cy="52" r="44" fill="#FFE6CC"/>
        <circle cx="50" cy="52" r="44" fill="none" stroke="#FFFFFF" stroke-width="3"/>
        <ellipse cx="38" cy="22" rx="10" ry="5" fill="#FFFFFF" opacity=".55" transform="rotate(-20 38 22)"/>

        {{-- Yanaklar --}}
        <g class="part m-happy m-wink m-laugh m-surprised">
            <ellipse cx="25" cy="64" rx="7" ry="4.5" fill="#FF9DB8" opacity=".55"/>
            <ellipse cx="75" cy="64" rx="7" ry="4.5" fill="#FF9DB8" opacity=".55"/>
        </g>
        <g class="part m-cute">
            <ellipse cx="25" cy="63" rx="9" ry="6" fill="#FF7FA6" opacity=".75"/>
            <ellipse cx="75" cy="63" rx="9" ry="6" fill="#FF7FA6" opacity=".75"/>
            <path d="M20 61l3 4M25 60l3 4M72 60l3 4M77 61l3 4" stroke="#FF5C8A" stroke-width="1.6" stroke-linecap="round"/>
        </g>

        {{-- Kaşlar (şaşkın) --}}
        <g class="part m-surprised" stroke="#7A5A48" stroke-width="3" stroke-linecap="round" fill="none">
            <path d="M27 26q8-6 15-1"/>
            <path d="M58 25q7-5 15 1"/>
        </g>

        {{-- Açık gözler: sol --}}
        <g class="part eye-open m-happy m-wink m-surprised">
            <ellipse class="eye-white" cx="36" cy="44" rx="9" ry="11" fill="#FFFFFF"/>
            <g class="pupil">
                <circle cx="36" cy="45" r="5.5" fill="#3B2F4A"/>
                <circle cx="38" cy="42.5" r="2" fill="#FFFFFF"/>
            </g>
        </g>
        {{-- Açık gözler: sağ --}}
        <g class="part eye-open m-happy m-surprised">
            <ellipse class="eye-white" cx="64" cy="44" rx="9" ry="11" fill="#FFFFFF"/>
            <g class="pupil">
                <circle cx="64" cy="45" r="5.5" fill="#3B2F4A"/>
                <circle cx="66" cy="42.5" r="2" fill="#FFFFFF"/>
            </g>
        </g>

        {{-- Kapalı / ^^ gözler --}}
        <path class="part m-cute m-laugh" d="M28 47q8-10 16 0" stroke="#3B2F4A" stroke-width="3.5" stroke-linecap="round" fill="none"/>
        <path class="part m-cute m-laugh m-wink" d="M56 47q8-10 16 0" stroke="#3B2F4A" stroke-width="3.5" stroke-linecap="round" fill="none"/>

        {{-- Ağızlar --}}
        <path class="part m-happy m-wink" d="M38 63q12 13 24 0" stroke="#3B2F4A" stroke-width="3.5" stroke-linecap="round" fill="none"/>
        <ellipse class="part m-surprised" cx="50" cy="68" rx="6" ry="7.5" fill="#3B2F4A"/>
        <path class="part m-cute" d="M41 63q4.5 5 9 0q4.5 5 9 0" stroke="#3B2F4A" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
        <g class="part m-laugh">
            <path d="M35 60h30q0 17-15 17t-15-17z" fill="#E8547A"/>
            <path d="M42 72q8-6 16 0q-8 6-16 0z" fill="#FF9DB8"/>
            <path d="M37 60.5h26" stroke="#FFFFFF" stroke-width="3" stroke-linecap="round"/>
        </g>
        {{-- Göz kırparken dil --}}
        <path class="part m-wink" d="M53 68q4 7 8 0z" fill="#FF7FA6"/>
    </svg>
</button>

<style>
    .kid-face { cursor: pointer; -webkit-tap-highlight-color: transparent; }
    .kid-face svg { filter: drop-shadow(0 4px 8px rgba(232, 120, 160, .30)); }
    .kid-face .part { opacity: 0; transition: opacity .18s ease; }
    .kid-face[data-mood="happy"]     .m-happy,
    .kid-face[data-mood="surprised"] .m-surprised,
    .kid-face[data-mood="cute"]      .m-cute,
    .kid-face[data-mood="laugh"]     .m-laugh,
    .kid-face[data-mood="wink"]      .m-wink { opacity: 1; }

    .kid-face .pupil { transition: transform .08s linear; }
    .kid-face .eye-open { transform-box: fill-box; transform-origin: center; transition: transform .09s ease, opacity .18s ease; }
    .kid-face.is-blinking .eye-open { transform: scaleY(.1); }
    .kid-face[data-mood="surprised"] .eye-white { transform-box: fill-box; transform-origin: center; transform: scale(1.12); }

    .kid-face.is-popping svg { animation: kid-face-pop .45s cubic-bezier(.3, 1.6, .5, 1); }
    .kid-face:focus-visible { outline: 3px solid #FF8FB1; outline-offset: 3px; }
    @keyframes kid-face-pop { 0% { transform: scale(1) } 40% { transform: scale(1.15) rotate(-5deg) } 100% { transform: scale(1) } }
    @media (prefers-reduced-motion: reduce) { .kid-face.is-popping svg { animation: none; } }
</style>

<script>
    (() => {
        if (window.__kidFaceInit) return;
        window.__kidFaceInit = true;

        const faces = () => document.querySelectorAll('.kid-face');
        const MAX_PUPIL = 3.6;           // SVG birimi
        const NEAR_PX = 90;              // bu kadar yaklaşınca utanır
        const FAST_PX_PER_MS = 2.2;      // bu hızdan hızlı fare → şaşırır
        let lockedUntil = 0;             // tepki hâlindeyken kendiliğinden hâl değiştirmesin
        let last = { x: 0, y: 0, t: 0 };

        const setMood = (face, mood, holdMs = 0) => {
            if (face.dataset.mood !== mood) {
                face.dataset.mood = mood;
                face.classList.remove('is-popping');
                void face.offsetWidth; // animasyonu yeniden başlat
                face.classList.add('is-popping');
            }
            if (holdMs) lockedUntil = Date.now() + holdMs;
        };

        const look = (x, y) => {
            faces().forEach(face => {
                const r = face.getBoundingClientRect();
                const cx = r.left + r.width / 2, cy = r.top + r.height / 2;
                const dx = x - cx, dy = y - cy;
                const dist = Math.hypot(dx, dy) || 1;
                const k = Math.min(dist / 120, 1) * MAX_PUPIL;
                const tx = (dx / dist) * k, ty = (dy / dist) * k;
                face.querySelectorAll('.pupil').forEach(p => p.setAttribute('transform', `translate(${tx.toFixed(2)} ${ty.toFixed(2)})`));

                const over = x >= r.left && x <= r.right && y >= r.top && y <= r.bottom;
                if (Date.now() < lockedUntil) return;
                if (over) setMood(face, 'laugh');
                else if (dist < NEAR_PX + r.width / 2) setMood(face, 'cute');
                else if (['laugh', 'cute'].includes(face.dataset.mood)) setMood(face, 'happy');
            });
        };

        document.addEventListener('mousemove', e => {
            const now = performance.now();
            const speed = last.t ? Math.hypot(e.clientX - last.x, e.clientY - last.y) / Math.max(now - last.t, 1) : 0;
            last = { x: e.clientX, y: e.clientY, t: now };
            look(e.clientX, e.clientY);
            if (speed > FAST_PX_PER_MS && Date.now() >= lockedUntil) {
                faces().forEach(f => setMood(f, 'surprised', 900));
            }
        }, { passive: true });

        document.addEventListener('touchstart', e => {
            const t = e.touches[0];
            if (t) look(t.clientX, t.clientY);
        }, { passive: true });

        document.addEventListener('click', e => {
            const face = e.target.closest('.kid-face');
            if (!face) return;
            setMood(face, 'surprised', 1100);
            setTimeout(() => setMood(face, 'cute', 1400), 1100);
        });

        // Göz kırpma
        const blink = () => {
            faces().forEach(f => {
                f.classList.add('is-blinking');
                setTimeout(() => f.classList.remove('is-blinking'), 140);
            });
            setTimeout(blink, 2500 + Math.random() * 3500);
        };
        setTimeout(blink, 1800);

        // Boştayken ara sıra kendiliğinden hâl değiştirir
        const idle = () => {
            if (Date.now() >= lockedUntil) {
                const pick = ['wink', 'cute', 'surprised', 'laugh'][Math.floor(Math.random() * 4)];
                faces().forEach(f => {
                    setMood(f, pick, 1300);
                    setTimeout(() => { if (Date.now() >= lockedUntil) setMood(f, 'happy'); }, 1350);
                });
            }
            setTimeout(idle, 6000 + Math.random() * 5000);
        };
        setTimeout(idle, 5000);
    })();
</script>
