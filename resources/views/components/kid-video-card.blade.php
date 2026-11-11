@props(['video'])

@php
    /*
     * Naif tema video kartı (kanal sayfası + video sayfası önerileri).
     * Çocuklar okuma bilmediği için kart neredeyse tamamen kapak resmi;
     * başlık ebeveyn için resmin altında ince yazıyla, fareli cihazda üzerine gelince görünür.
     * Üzerine gelince önizleme GIF'i oynar (bootstrap.js: data-element="video-card").
     */
    $pastels = ['bg-pastel-pink', 'bg-pastel-lilac', 'bg-pastel-peach', 'bg-pastel-mint', 'bg-pastel-sky', 'bg-pastel-butter'];
    $bg = $pastels[$video->id % count($pastels)];

    // Kaç yıldız harcar (VideoController@storeVideoWatchingActivity:
    // ilk kayıt 5. dakikada, sonra her 30 dakikada bir yeni kayıt).
    $cost = null;
    if ($video->duration && preg_match('/^(\d+):(\d+):(\d+)$/', $video->duration, $m)) {
        $minutes = $m[1] * 60 + $m[2] + $m[3] / 60;
        $cost = 1 + (int) floor(max(0, $minutes - 5) / 30);
    }

    $watched = (bool) ($video->watched_by_auth_user ?? false);
@endphp

<a href="{{ route('videos.show', $video->uid) }}"
   data-element="video-card"
   data-speech="true" data-speech-text="{{ $video->title }}"
   title="{{ $video->title }}" aria-label="{{ $video->title }}"
   {{ $attributes->merge(['class' => "$bg group relative block aspect-video overflow-hidden rounded-3xl border-2 border-white shadow-soft transition duration-200 hover:-translate-y-0.5 hover:shadow-soft-lg focus-visible:outline focus-visible:outline-4 focus-visible:outline-offset-2 focus-visible:outline-candy-rose"]) }}>

    {{-- Resim yüklenemezse görünen yedek: oynat simgesi --}}
    <span class="absolute inset-0 flex items-center justify-center" aria-hidden="true">
        <span class="flex h-12 w-12 items-center justify-center rounded-full bg-white/80 shadow-soft">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="#FF8FB1"><path d="M8 5v14l11-7z"/></svg>
        </span>
    </span>

    {{-- remove() değil: bootstrap.js hover'da bu elemanı arıyor --}}
    <img data-thumbnail-type="image" src="{{ $video->thumbnail_url }}" alt="" loading="lazy"
         onerror="this.classList.add('invisible')"
         class="absolute inset-0 h-full w-full object-cover transition-transform duration-300 group-hover:scale-105">
    <img data-thumbnail-type="gif" src="{{ $video->thumbnail_gif_url }}" alt="" loading="lazy"
         onerror="this.src = '{{ $video->thumbnail_url }}'"
         class="absolute inset-0 hidden h-full w-full object-cover">

    @if($watched)
        <span class="absolute left-1.5 top-1.5 flex h-8 w-8 items-center justify-center rounded-full border-2 border-white bg-candy-mint shadow-soft"
              aria-label="İzledin">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>
        </span>
    @endif

    @if($cost)
        <span class="absolute right-1.5 top-1.5 flex items-center rounded-full bg-white/90 px-1 py-0.5 shadow-soft"
              aria-label="{{ $cost }} yıldız">
            @for($s = 0; $s < min($cost, 3); $s++)
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="#FFC94D" stroke="#F0A92E" stroke-width="1.8" stroke-linejoin="round" aria-hidden="true"><path d="M12 2.8l2.8 5.8 6.3.9-4.6 4.4 1.1 6.3L12 17.2l-5.6 3 1.1-6.3L2.9 9.5l6.3-.9z"/></svg>
            @endfor
        </span>
    @endif

    {{-- Başlık (ebeveyn için): şeffaftan beyaza geçiş; fareli cihazda üzerine gelince görünür --}}
    <span class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-white via-white/80 to-transparent px-2.5 pb-1.5 pt-7 text-sm font-normal leading-tight text-candy-ink transition-opacity duration-200 [@media(hover:hover)]:opacity-0 [@media(hover:hover)]:group-hover:opacity-100 [@media(hover:hover)]:group-focus-visible:opacity-100">
        <span class="line-clamp-1">{{ $video->title }}</span>
    </span>
</a>
