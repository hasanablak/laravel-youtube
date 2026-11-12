@php
    /*
     * Çocuk sayfalarının başlığı (layout'ta 'hideTopBar' => true olan sayfalar).
     * Klasik navbar yerine küçük bir gökyüzü:
     *  - Gündüz: pastel pembe gökyüzü, gülümseyen güneş, süzülen bulutlar.
     *  - Gece (19:00–07:00): mor gökyüzü, uykulu ay, göz kırpan yıldızlar.
     * Solda çocuğun adı (config app.kid_name): her harf ayrı renkte, sırayla hafifçe zıplar;
     * dokununca/üzerine gelince "Merhaba ...!" sesli söylenir (bootstrap.js data-speech).
     * Sağda ebeveynler için küçük, silik bir kilit.
     */
    $name = config('app.kid_name', 'Hafsa');
    $hour = now(config('app.kid_timezone', 'Europe/Istanbul'))->hour;
    $isNight = $hour >= 19 || $hour < 7;

    $letterColors = $isNight
        ? ['#FFD6E3', '#FFF1C7', '#D8F3E6', '#DCEEFF', '#E8DDFF']
        : ['#FF6FA3', '#A47BFF', '#FF9A62', '#3DBE8B', '#5AAEFF'];
    $letters = mb_str_split($name);

    $parentUrl = auth()->check() ? route('profile.page', auth()->id()) : route('login.page');
@endphp

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@600;700&display=swap" rel="stylesheet">

<header class="kid-sky {{ $isNight ? 'kid-sky--night' : 'kid-sky--day' }} relative overflow-hidden">
    {{-- Süsler --}}
    <div aria-hidden="true" class="pointer-events-none absolute inset-0">
        @if($isNight)
            @foreach([[8, 22, 0], [22, 60, .6], [36, 18, 1.2], [48, 54, .3], [61, 26, .9], [72, 62, 1.5], [83, 20, .4], [92, 50, 1.1]] as [$x, $y, $d])
                <svg class="kid-twinkle absolute h-3 w-3" style="left: {{ $x }}%; top: {{ $y }}%; animation-delay: {{ $d }}s" viewBox="0 0 24 24" fill="#FFF1C7"><path d="M12 2l2.2 7.8L22 12l-7.8 2.2L12 22l-2.2-7.8L2 12l7.8-2.2z"/></svg>
            @endforeach
        @else
            <svg class="kid-cloud absolute top-3 h-10 w-24 opacity-90" style="left: 30%; animation-duration: 38s" viewBox="0 0 96 40" fill="#FFFFFF"><path d="M22 36h52a14 14 0 0 0 0-28 18 18 0 0 0-33-4A13 13 0 0 0 22 36z"/></svg>
            <svg class="kid-cloud absolute top-10 h-7 w-16 opacity-70" style="left: 58%; animation-duration: 30s; animation-delay: -12s" viewBox="0 0 96 40" fill="#FFFFFF"><path d="M22 36h52a14 14 0 0 0 0-28 18 18 0 0 0-33-4A13 13 0 0 0 22 36z"/></svg>
            <svg class="kid-cloud absolute top-2 h-8 w-20 opacity-80" style="left: 75%; animation-duration: 44s; animation-delay: -20s" viewBox="0 0 96 40" fill="#FFFFFF"><path d="M22 36h52a14 14 0 0 0 0-28 18 18 0 0 0-33-4A13 13 0 0 0 22 36z"/></svg>
            @foreach([[18, 20, 0], [46, 58, .8], [88, 64, 1.4]] as [$x, $y, $d])
                <svg class="kid-twinkle absolute h-3 w-3" style="left: {{ $x }}%; top: {{ $y }}%; animation-delay: {{ $d }}s" viewBox="0 0 24 24" fill="#FF8FB1"><path d="M12 21s-7.5-4.6-7.5-10A4.3 4.3 0 0 1 12 8.4 4.3 4.3 0 0 1 19.5 11c0 5.4-7.5 10-7.5 10z"/></svg>
            @endforeach
        @endif
    </div>

    {{-- Dolaşan karakterler: kedi, tavşan, kuş --}}
    <x-kid-critters />

    {{-- İçerik karakterlerin önünde; sadece kendi elemanları tıklanabilir, aradaki boşluk karakterlere geçer --}}
    <div class="pointer-events-none relative z-[2] mx-auto flex h-20 max-w-[1600px] items-center justify-between gap-4 px-4 sm:h-24">
        <div class="pointer-events-auto flex items-center gap-3 sm:gap-5">
        {{-- İsim: fiyonk + renkli zıplayan harfler --}}
        <a href="{{ route('home') }}"
           data-speech="true" data-speech-text="Merhaba {{ $name }}!"
           aria-label="Ana sayfa — {{ $name }}"
           class="kid-name group relative flex items-end gap-1 no-underline">
            <svg aria-hidden="true" class="kid-bow absolute -left-3 -top-3 h-7 w-9 sm:-left-4 sm:-top-4 sm:h-8 sm:w-10" viewBox="0 0 40 28">
                <path d="M20 14C14 4 4 2 3 8s2 14 17 6z" fill="#FF8FB1"/>
                <path d="M20 14c6-10 16-12 17-6s-2 14-17 6z" fill="#FF8FB1"/>
                <path d="M17 14l-4 12 5-3 2 4 2-13z" fill="#FF6FA3"/>
                <circle cx="20" cy="14" r="4" fill="#FF6FA3"/>
                <path d="M9 7c2 0 5 2 7 5" stroke="#FFFFFF" stroke-width="1.6" stroke-linecap="round" fill="none" opacity=".7"/>
            </svg>
            @foreach($letters as $i => $letter)
                <span class="kid-letter" style="--i: {{ $i }}; color: {{ $letterColors[$i % count($letterColors)] }}">{{ $letter }}</span>
            @endforeach
        </a>

        {{-- Gözleriyle fareyi takip eden yüz --}}
        <x-kid-face />
        </div>

        <div class="pointer-events-auto flex items-center gap-3">
            {{-- Güneş / Ay --}}
            @if($isNight)
                <svg aria-hidden="true" class="kid-float h-14 w-14 sm:h-16 sm:w-16" viewBox="0 0 64 64">
                    <path d="M40 8a24 24 0 1 0 16 38A20 20 0 0 1 40 8z" fill="#FFF1C7"/>
                    <path d="M24 34q3 3 6 0" stroke="#B49A5A" stroke-width="2.2" stroke-linecap="round" fill="none"/>
                    <circle cx="22" cy="40" r="3" fill="#FFB3C8" opacity=".7"/>
                    <text x="46" y="18" font-family="Fredoka, sans-serif" font-size="11" font-weight="700" fill="#FFFFFF" opacity=".85" class="kid-zzz">z</text>
                    <text x="52" y="10" font-family="Fredoka, sans-serif" font-size="8" font-weight="700" fill="#FFFFFF" opacity=".6" class="kid-zzz" style="animation-delay: .8s">z</text>
                </svg>
            @else
                <svg aria-hidden="true" class="h-14 w-14 sm:h-16 sm:w-16" viewBox="0 0 64 64">
                    <g class="kid-sun-rays" stroke="#FFC94D" stroke-width="3.5" stroke-linecap="round">
                        <path d="M32 3v7M32 54v7M3 32h7M54 32h7M11.5 11.5l5 5M47.5 47.5l5 5M11.5 52.5l5-5M47.5 16.5l5-5"/>
                    </g>
                    <circle cx="32" cy="32" r="16" fill="#FFD966"/>
                    <circle cx="26.5" cy="30" r="1.8" fill="#8A6A1F"/>
                    <circle cx="37.5" cy="30" r="1.8" fill="#8A6A1F"/>
                    <path d="M27 36q5 4.5 10 0" stroke="#8A6A1F" stroke-width="2" stroke-linecap="round" fill="none"/>
                    <circle cx="23" cy="35.5" r="2.6" fill="#FF9DB8" opacity=".7"/>
                    <circle cx="41" cy="35.5" r="2.6" fill="#FF9DB8" opacity=".7"/>
                </svg>
            @endif

            {{-- Ebeveyn: küçük ve silik --}}
            <a href="{{ $parentUrl }}" aria-label="Ebeveyn bölümü"
               class="flex h-9 w-9 items-center justify-center rounded-full opacity-40 transition hover:bg-white/60 hover:opacity-100">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="{{ $isNight ? '#FFFFFF' : '#4A4466' }}" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><rect x="5" y="10.5" width="14" height="10" rx="2.5"/><path d="M8 10.5V7.5a4 4 0 0 1 8 0v3"/></svg>
            </a>
        </div>
    </div>

    {{-- Dalgalı alt kenar: sayfa zeminine (candy-blush) karışır --}}
    <svg aria-hidden="true" class="relative block h-4 w-full sm:h-5" viewBox="0 0 1440 40" preserveAspectRatio="none">
        <path d="M0 22c120-18 240-18 360 0s240 18 360 0 240-18 360 0 240 18 360 0v18H0z" fill="#FFF5F8"/>
    </svg>
</header>

<style>
    .kid-sky--day   { background: linear-gradient(180deg, #FFD6E3 0%, #FFE9F0 60%, #FFF5F8 100%); }
    .kid-sky--night { background: linear-gradient(180deg, #2E2560 0%, #5A4A9E 70%, #8C7BC4 100%); }

    .kid-name { font-family: 'Fredoka', 'Nunito', sans-serif; font-weight: 700; line-height: 1; }
    .kid-letter {
        display: inline-block;
        font-size: 2.6rem;
        text-shadow: 0 2px 0 #FFFFFF, 0 4px 10px rgba(232, 120, 160, .25);
        animation: kid-bob 2.8s ease-in-out infinite;
        animation-delay: calc(var(--i) * .18s);
    }
    .kid-sky--night .kid-letter { text-shadow: 0 2px 0 rgba(46, 37, 96, .6), 0 0 14px rgba(255, 241, 199, .35); }
    @media (min-width: 640px) { .kid-letter { font-size: 3.2rem; } }
    .kid-name:hover .kid-letter,
    .kid-name:focus-visible .kid-letter { animation: kid-hop .55s ease-out; animation-delay: calc(var(--i) * .07s); }
    .kid-bow { transform-origin: 50% 60%; animation: kid-wiggle 3.5s ease-in-out infinite; }

    .kid-sun-rays { transform-origin: 32px 32px; animation: kid-spin 18s linear infinite; }
    .kid-float { animation: kid-bob 4s ease-in-out infinite; }
    .kid-zzz { animation: kid-twinkle 2.4s ease-in-out infinite; }
    .kid-twinkle { animation: kid-twinkle 2.2s ease-in-out infinite; }
    .kid-cloud { animation-name: kid-drift; animation-timing-function: linear; animation-iteration-count: infinite; }

    @keyframes kid-bob     { 0%, 100% { transform: translateY(0) } 50% { transform: translateY(-4px) } }
    @keyframes kid-hop     { 0% { transform: translateY(0) scale(1) } 40% { transform: translateY(-14px) scale(1.12) rotate(-6deg) } 100% { transform: translateY(0) scale(1) } }
    @keyframes kid-wiggle  { 0%, 100% { transform: rotate(-8deg) } 50% { transform: rotate(8deg) } }
    @keyframes kid-spin    { to { transform: rotate(360deg) } }
    @keyframes kid-twinkle { 0%, 100% { opacity: .25; transform: scale(.8) } 50% { opacity: 1; transform: scale(1.15) } }
    @keyframes kid-drift   { from { transform: translateX(-30vw) } to { transform: translateX(40vw) } }

    @media (prefers-reduced-motion: reduce) {
        .kid-sky *, .kid-sky *::before { animation: none !important; }
    }
</style>
