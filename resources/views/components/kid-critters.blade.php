{{--
    Başlıkta dolaşan, dokunulabilen özgün çizgi film karakterleri (kid-header içinde).
      - Kedi:   barın altında yürür, ara sıra oturur. Fare üstüne gelince oturur;
                tıklayınca zıplar ve "Miyav!" der.
      - Tavşan: arada bir dalganın arkasından kafasını uzatır; fare yaklaşınca saklanır;
                göründüğünde tıklanırsa zıplar ve "Ce-e!" der.
      - Kuş:    ara sıra gökyüzünden uçarak geçer; tıklanınca takla atar ve "Cik cik!" der.
    Hareketler JS (requestAnimationFrame) + CSS; "hareketi azalt" açıksa karakterler sabit durur.
--}}
<div class="kid-critters pointer-events-none absolute inset-0 z-[1]">

    {{-- KEDİ --}}
    <button type="button" class="kid-cat pointer-events-auto absolute bottom-1 left-0" aria-label="Kedi" data-state="walk" data-dir="1">
        <svg class="kid-cat__body" viewBox="0 0 120 80" aria-hidden="true">
            <path class="kid-cat__tail" d="M26 48C14 44 6 32 10 18" stroke="#F4A261" stroke-width="7" stroke-linecap="round" fill="none"/>
            <rect class="kid-cat__leg kid-cat__leg--a" x="31" y="52" width="9" height="20" rx="4.5" fill="#E8914E"/>
            <rect class="kid-cat__leg kid-cat__leg--b" x="43" y="52" width="9" height="20" rx="4.5" fill="#F4A261"/>
            <rect class="kid-cat__leg kid-cat__leg--b" x="69" y="52" width="9" height="20" rx="4.5" fill="#E8914E"/>
            <rect class="kid-cat__leg kid-cat__leg--a" x="81" y="52" width="9" height="20" rx="4.5" fill="#F4A261"/>
            <ellipse cx="58" cy="48" rx="34" ry="17" fill="#F4A261"/>
            <ellipse cx="62" cy="56" rx="22" ry="7" fill="#FFE1C7"/>
            <path d="M44 33q3 7 0 12M56 31q3 8 0 13M68 33q3 7 0 12" stroke="#E07B39" stroke-width="3.5" stroke-linecap="round" fill="none"/>
            {{-- kafa --}}
            <path d="M80 24L83 4l11 14z" fill="#F4A261"/>
            <path d="M98 18l9-14 3 20z" fill="#F4A261"/>
            <path d="M83 20l1.5-10 5.5 7z" fill="#FFB3C8"/>
            <path d="M100 17l5-8 1.5 11z" fill="#FFB3C8"/>
            <circle cx="94" cy="34" r="19" fill="#F4A261"/>
            <ellipse cx="97" cy="42" rx="11" ry="8" fill="#FFE1C7"/>
            <g class="kid-cat__eyes">
                <ellipse cx="88" cy="31" rx="3.4" ry="4.4" fill="#3B2F4A"/>
                <ellipse cx="102" cy="31" rx="3.4" ry="4.4" fill="#3B2F4A"/>
                <circle cx="89.2" cy="29.4" r="1.3" fill="#FFFFFF"/>
                <circle cx="103.2" cy="29.4" r="1.3" fill="#FFFFFF"/>
            </g>
            <path d="M95 38.5l3 0-1.5 2.2z" fill="#FF7FA6"/>
            <path d="M92 42q2.2 2.4 4.5 0q2.3 2.4 4.5 0" stroke="#3B2F4A" stroke-width="1.6" stroke-linecap="round" fill="none"/>
            <path d="M84 40l-9-2M84 43l-9 1M108 40l9-2M108 43l9 1" stroke="#C9733A" stroke-width="1.3" stroke-linecap="round"/>
            <ellipse cx="84" cy="39" rx="3.5" ry="2.2" fill="#FF9DB8" opacity=".6"/>
            <ellipse cx="106" cy="39" rx="3.5" ry="2.2" fill="#FF9DB8" opacity=".6"/>
        </svg>
    </button>

    {{-- TAVŞAN --}}
    <div class="kid-bunny-slot pointer-events-none absolute bottom-0 h-16 w-16 overflow-hidden" style="left: 55%">
        <button type="button" class="kid-bunny pointer-events-auto absolute inset-x-0 bottom-0" aria-label="Tavşan" data-up="0">
            <svg viewBox="0 0 64 64" aria-hidden="true">
                <ellipse class="kid-bunny__ear" cx="23" cy="16" rx="6" ry="15" fill="#FFFFFF" transform="rotate(-10 23 16)"/>
                <ellipse cx="23" cy="17" rx="3" ry="10" fill="#FFC2D4" transform="rotate(-10 23 17)"/>
                <ellipse class="kid-bunny__ear kid-bunny__ear--r" cx="41" cy="16" rx="6" ry="15" fill="#FFFFFF" transform="rotate(10 41 16)"/>
                <ellipse cx="41" cy="17" rx="3" ry="10" fill="#FFC2D4" transform="rotate(10 41 17)"/>
                <circle cx="32" cy="42" r="17" fill="#FFFFFF"/>
                <circle cx="25.5" cy="40" r="2.8" fill="#3B2F4A"/>
                <circle cx="38.5" cy="40" r="2.8" fill="#3B2F4A"/>
                <circle cx="26.4" cy="39" r="1" fill="#FFFFFF"/>
                <circle cx="39.4" cy="39" r="1" fill="#FFFFFF"/>
                <path d="M30.5 45h3l-1.5 2z" fill="#FF7FA6"/>
                <path d="M29 48.5q1.5 1.6 3 0q1.5 1.6 3 0" stroke="#3B2F4A" stroke-width="1.3" stroke-linecap="round" fill="none"/>
                <ellipse cx="20" cy="46" rx="3.2" ry="2" fill="#FFB3C8" opacity=".7"/>
                <ellipse cx="44" cy="46" rx="3.2" ry="2" fill="#FFB3C8" opacity=".7"/>
                <ellipse cx="22" cy="60" rx="6" ry="4.5" fill="#FFFFFF" stroke="#F3D3DD" stroke-width="1"/>
                <ellipse cx="42" cy="60" rx="6" ry="4.5" fill="#FFFFFF" stroke="#F3D3DD" stroke-width="1"/>
            </svg>
        </button>
    </div>

    {{-- KUŞ --}}
    <button type="button" class="kid-bird pointer-events-auto absolute left-0 top-2" aria-label="Kuş" data-flying="0">
        <svg viewBox="0 0 64 48" aria-hidden="true">
            <path d="M8 26l-7-4 2 9z" fill="#5AAEFF"/>
            <ellipse cx="30" cy="27" rx="20" ry="15" fill="#7CC4FF"/>
            <ellipse cx="34" cy="32" rx="12" ry="8" fill="#DDF1FF"/>
            <path class="kid-bird__wing" d="M22 24q8-14 18-2q-9 6-18 2z" fill="#5AAEFF"/>
            <circle cx="42" cy="20" r="3" fill="#3B2F4A"/>
            <circle cx="43" cy="19" r="1" fill="#FFFFFF"/>
            <path d="M49 22l8 2-8 3z" fill="#FFC94D"/>
            <ellipse cx="40" cy="27" rx="3" ry="1.8" fill="#FF9DB8" opacity=".7"/>
        </svg>
    </button>
</div>

<style>
    .kid-cat { width: 96px; height: 64px; cursor: pointer; transition: bottom .2s; -webkit-tap-highlight-color: transparent; }
    @media (min-width: 640px) { .kid-cat { width: 112px; height: 75px; } }
    .kid-cat svg { width: 100%; height: 100%; overflow: visible; filter: drop-shadow(0 3px 5px rgba(160, 90, 40, .25)); }
    .kid-cat[data-dir="-1"] .kid-cat__body { transform: scaleX(-1); }
    .kid-cat__leg { transform-box: fill-box; transform-origin: 50% 0; }
    .kid-cat[data-state="walk"] .kid-cat__leg--a { animation: kid-cat-step .42s ease-in-out infinite alternate; }
    .kid-cat[data-state="walk"] .kid-cat__leg--b { animation: kid-cat-step .42s ease-in-out infinite alternate-reverse; }
    .kid-cat[data-state="walk"] .kid-cat__body { animation: kid-cat-bounce .42s ease-in-out infinite alternate; }
    .kid-cat[data-dir="-1"][data-state="walk"] .kid-cat__body { animation-name: kid-cat-bounce-flip; }
    .kid-cat[data-state="sit"] .kid-cat__leg--a { transform: scaleY(.55); }
    .kid-cat__tail { transform-box: fill-box; transform-origin: 100% 100%; animation: kid-cat-tail 1.6s ease-in-out infinite; }
    .kid-cat[data-state="sit"] .kid-cat__tail { animation-duration: .7s; }
    .kid-cat__eyes { transform-box: fill-box; transform-origin: center; animation: kid-cat-blink 4.5s infinite; }
    .kid-cat.is-jumping { animation: kid-cat-jump .7s cubic-bezier(.3, 1.4, .5, 1); }

    .kid-bunny { width: 64px; height: 64px; cursor: pointer; transform: translateY(100%); transition: transform .45s cubic-bezier(.3, 1.5, .5, 1); -webkit-tap-highlight-color: transparent; }
    .kid-bunny svg { width: 100%; height: 100%; }
    .kid-bunny[data-up="1"] { transform: translateY(18%); }
    .kid-bunny[data-up="1"] .kid-bunny__ear--r { transform-box: fill-box; transform-origin: 50% 100%; animation: kid-bunny-ear 1.2s ease-in-out infinite; }
    .kid-bunny.is-hopping { transform: translateY(-6%) !important; }

    .kid-bird { width: 52px; height: 40px; cursor: pointer; visibility: hidden; -webkit-tap-highlight-color: transparent; }
    .kid-bird[data-flying="1"] { visibility: visible; }
    .kid-bird svg { width: 100%; height: 100%; overflow: visible; }
    .kid-bird__wing { transform-box: fill-box; transform-origin: 30% 90%; animation: kid-bird-flap .22s ease-in-out infinite alternate; }
    .kid-bird.is-flipping svg { animation: kid-bird-loop .7s ease-in-out; }

    .kid-cat:focus-visible, .kid-bunny:focus-visible, .kid-bird:focus-visible { outline: 3px solid #FF8FB1; outline-offset: 2px; border-radius: 12px; }

    @keyframes kid-cat-step   { from { transform: rotate(-18deg) } to { transform: rotate(18deg) } }
    @keyframes kid-cat-bounce { from { transform: translateY(0) } to { transform: translateY(-2px) } }
    @keyframes kid-cat-bounce-flip { from { transform: scaleX(-1) translateY(0) } to { transform: scaleX(-1) translateY(-2px) } }
    @keyframes kid-cat-tail   { 0%, 100% { transform: rotate(-8deg) } 50% { transform: rotate(10deg) } }
    @keyframes kid-cat-blink  { 0%, 46%, 50%, 100% { transform: scaleY(1) } 48% { transform: scaleY(.1) } }
    @keyframes kid-cat-jump   { 0%, 100% { translate: 0 0 } 40% { translate: 0 -26px } }
    @keyframes kid-bunny-ear  { 0%, 100% { transform: rotate(10deg) } 50% { transform: rotate(-12deg) } }
    @keyframes kid-bird-flap  { from { transform: rotate(10deg) } to { transform: rotate(-40deg) } }
    @keyframes kid-bird-loop  { to { transform: rotate(-360deg) } }

    @media (prefers-reduced-motion: reduce) {
        .kid-critters *, .kid-critters { animation: none !important; transition: none !important; }
    }
</style>

<script>
    (() => {
        if (window.__kidCrittersInit) return;
        window.__kidCrittersInit = true;

        const root = document.querySelector('.kid-critters');
        if (!root) return;
        const reduced = matchMedia('(prefers-reduced-motion: reduce)').matches;
        const say = (text) => {
            try {
                window.speechSynthesis?.cancel();
                window.TextToSpeech?.(text);
            } catch (e) {}
        };
        const rand = (a, b) => a + Math.random() * (b - a);

        /* ---------- KEDİ ---------- */
        const cat = root.querySelector('.kid-cat');
        let catX = rand(80, Math.max(120, root.clientWidth * .4));
        let dir = 1, hovered = false, sitUntil = 0, lastT = 0;
        const SPEED = 38; // px/sn

        const placeCat = () => { cat.style.transform = `translateX(${catX}px)`; };
        placeCat();

        const catLoop = (t) => {
            const dt = lastT ? Math.min((t - lastT) / 1000, .05) : 0;
            lastT = t;
            const maxX = root.clientWidth - cat.offsetWidth;
            const now = performance.now();

            if (hovered || now < sitUntil) {
                cat.dataset.state = 'sit';
            } else {
                cat.dataset.state = 'walk';
                catX += dir * SPEED * dt;
                if (catX <= 0) { catX = 0; dir = 1; }
                if (catX >= maxX) { catX = maxX; dir = -1; }
                // ara sıra oturup dinlenir ya da geri döner
                if (Math.random() < dt * .08) sitUntil = now + rand(1800, 4200);
                if (Math.random() < dt * .03) dir *= -1;
            }
            cat.dataset.dir = dir;
            placeCat();
            requestAnimationFrame(catLoop);
        };
        if (!reduced) requestAnimationFrame(catLoop); else cat.dataset.state = 'sit';

        cat.addEventListener('mouseenter', () => { hovered = true; });
        cat.addEventListener('mouseleave', () => { hovered = false; sitUntil = performance.now() + 700; });
        cat.addEventListener('click', () => {
            cat.classList.remove('is-jumping'); void cat.offsetWidth; cat.classList.add('is-jumping');
            setTimeout(() => cat.classList.remove('is-jumping'), 750);
            say('Miyav!');
        });

        /* ---------- TAVŞAN ---------- */
        const slot = root.querySelector('.kid-bunny-slot');
        const bunny = root.querySelector('.kid-bunny');
        let bunnyTimer = null;

        const hideBunny = () => { bunny.dataset.up = '0'; };
        const peek = () => {
            slot.style.left = `${rand(25, 80)}%`;
            bunny.dataset.up = '1';
            clearTimeout(bunnyTimer);
            bunnyTimer = setTimeout(() => { hideBunny(); scheduleBunny(); }, rand(2500, 4000));
        };
        const scheduleBunny = () => { bunnyTimer = setTimeout(peek, rand(6000, 12000)); };
        if (reduced) { bunny.dataset.up = '1'; } else { setTimeout(peek, 3000); }

        // fare yaklaşınca saklanır (tıklanabilmesi için çok yakına gelince değil)
        document.addEventListener('mousemove', (e) => {
            if (reduced || bunny.dataset.up !== '1') return;
            const r = bunny.getBoundingClientRect();
            const d = Math.hypot(e.clientX - (r.left + r.width / 2), e.clientY - (r.top + r.height / 2));
            if (d < 140 && d > 40 && !bunny.matches(':hover')) {
                hideBunny();
                clearTimeout(bunnyTimer);
                scheduleBunny();
            }
        }, { passive: true });

        bunny.addEventListener('click', () => {
            if (bunny.dataset.up !== '1') return;
            bunny.classList.add('is-hopping');
            setTimeout(() => bunny.classList.remove('is-hopping'), 350);
            say('Ce-e!');
        });

        /* ---------- KUŞ ---------- */
        const bird = root.querySelector('.kid-bird');
        const fly = () => {
            const w = root.clientWidth;
            const fromLeft = Math.random() < .5;
            const start = performance.now();
            const duration = rand(7000, 10000);
            const baseY = rand(2, 14);
            bird.dataset.flying = '1';
            bird.querySelector('svg').style.transform = fromLeft ? '' : 'scaleX(-1)';

            const step = (t) => {
                const p = (t - start) / duration;
                if (p >= 1) { bird.dataset.flying = '0'; setTimeout(fly, rand(9000, 16000)); return; }
                const x = fromLeft ? -60 + p * (w + 120) : w + 60 - p * (w + 120);
                const y = baseY + Math.sin(p * Math.PI * 6) * 6;
                bird.style.transform = `translate(${x}px, ${y}px)`;
                requestAnimationFrame(step);
            };
            requestAnimationFrame(step);
        };
        if (!reduced) setTimeout(fly, 4000);

        bird.addEventListener('click', () => {
            bird.classList.remove('is-flipping'); void bird.offsetWidth; bird.classList.add('is-flipping');
            say('Cik cik!');
        });
    })();
</script>
