@extends('layouts.app', [
    "title" => "Minik Ekran",
    "hideTopBar" => true,
])

@push('header')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700;800&display=swap" rel="stylesheet">
@endpush

@php
    /*
     * Kanal ağırlıklı, resim odaklı ana sayfa.
     * Çocuklar okuma bilmediği için kanal büyük bir kapak resmiyle tanınır;
     * altındaki kanal adı ebeveynler içindir. Kanal adı dokununca sesli okunur.
     * Tailwind sınıfları tam yazılmalı (JIT bunları tarar).
     */
    $palette = [
        'bg-pastel-pink',
        'bg-pastel-lilac',
        'bg-pastel-peach',
        'bg-pastel-mint',
        'bg-pastel-sky',
        'bg-pastel-butter',
    ];
    $colorFor = fn (int $id) => $palette[$id % count($palette)];
    $initial = fn (string $name) => mb_strtoupper(mb_substr($name, 0, 1));

    $starSvg = '<path d="M12 2.8l2.8 5.8 6.3.9-4.6 4.4 1.1 6.3L12 17.2l-5.6 3 1.1-6.3L2.9 9.5l6.3-.9z"/>';
@endphp

@section('content')
<div class="min-h-screen bg-candy-blush font-nunito text-candy-ink">
    <div class="mx-auto flex max-w-[1600px] flex-col gap-3 px-3 pb-16 pt-3 sm:px-4">

        {{-- Kanallar: büyük kapak resmi, adı resmin üzerinde --}}
        <section aria-label="Kanallar" class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 2xl:grid-cols-6">
            @foreach($channels as $channel)
                @php $allWatched = $channel['video_count'] > 0 && $channel['watched'] >= $channel['video_count']; @endphp
                <a href="{{ route('channel', $channel['slug']) }}"
                   data-speech="true" data-speech-text="{{ $channel['name'] }}"
                   title="{{ $channel['name'] }}"
                   class="{{ $colorFor($channel['id']) }} group relative flex flex-col overflow-hidden rounded-3xl border-2 border-white shadow-soft transition duration-200 hover:-translate-y-0.5 hover:shadow-soft-lg focus-visible:outline focus-visible:outline-4 focus-visible:outline-offset-2 focus-visible:outline-candy-rose">

                    <span class="relative flex aspect-video items-center justify-center overflow-hidden">
                        {{-- Resim yüklenemezse kanalın baş harfi görünür --}}
                        <span class="text-5xl font-extrabold text-candy-ink/40" aria-hidden="true">{{ $initial($channel['name']) }}</span>
                        @if(!empty($channel['thumbs']))
                            <img src="{{ $channel['thumbs'][0]['url'] }}" alt="" onerror="this.remove()" loading="lazy"
                                 class="absolute inset-0 h-full w-full object-cover transition-transform duration-300 group-hover:scale-105">
                        @endif

                        @if($channel['new_count'] > 0)
                            <span class="absolute right-1.5 top-1.5 flex h-9 w-9 items-center justify-center rounded-full border-2 border-white bg-candy-rose shadow-soft"
                                  aria-label="{{ $channel['new_count'] }} yeni video">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="#FFFFFF" aria-hidden="true">{!! $starSvg !!}</svg>
                            </span>
                        @endif

                        @if($allWatched)
                            <span class="absolute left-1.5 top-1.5 flex h-9 w-9 items-center justify-center rounded-full border-2 border-white bg-candy-mint shadow-soft"
                                  aria-label="Hepsini izledin">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>
                            </span>
                        @endif

                        {{-- Kanal adı (ebeveyn için): resmin altında şeffaftan beyaza geçiş.
                             Fareli cihazlarda sadece üzerine gelince görünür; dokunmatikte hep görünür. --}}
                        <span class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-white via-white/80 to-transparent px-2.5 pb-1.5 pt-7 text-sm font-normal leading-tight transition-opacity duration-200 sm:text-base [@media(hover:hover)]:opacity-0 [@media(hover:hover)]:group-hover:opacity-100 [@media(hover:hover)]:group-focus-visible:opacity-100">
                            <span class="line-clamp-1">{{ $channel['name'] }}</span>
                        </span>
                    </span>
                </a>
            @endforeach
        </section>

        @if($channels->isEmpty())
            <p class="rounded-3xl bg-white p-5 font-bold shadow-soft">
                Henüz kanal yok. <code>php artisan manuel:video-updater</code> ile video ekleyebilirsin.
            </p>
        @endif
    </div>
</div>
@endsection

{{-- Aşağı kaydırmayı özendiren 3D ejder; #app dışında olmalı (bkz. bileşen açıklaması) --}}
@push('footer')
    <x-kid-dragon />
@endpush
