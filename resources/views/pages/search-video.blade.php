@extends('layouts.app', ['title' => $title . ' | Stream'])

@section('content')
    <div class="max-w-5xl mx-auto px-4 py-8 bg-white min-h-screen">
        {{-- Başlık --}}
        <h1 class="text-2xl font-semibold text-gray-900 mb-4">Video Arama Sonuçları</h1>

        {{-- Sonuç sayısı --}}
        <div class="text-sm text-gray-600 mb-6">
            @if(isset($videos) && count($videos))
                <span>{{ $videos->total() ?? count($videos) }} sonuç bulundu</span>
            @else
                <span>Sonuç yok</span>
            @endif
        </div>

        {{-- Video listesi alt alta --}}
        <div class="space-y-6">
            @forelse($videos as $video)
                <a href="{{ route('videos.show', $video->uid) }}" class="block group">
                    <div class="flex flex-col sm:flex-row gap-4 border border-gray-200 rounded-lg overflow-hidden shadow-sm hover:shadow-md transition bg-white">

                        {{-- Thumbnail --}}
                        <div class="relative w-full sm:w-60 flex-shrink-0">
                            @php
                                $thumb = $video->image ? asset($video->image) : asset('storage/videos/'.$video->uid.'/'.$video->thumbnail_image) ?? null;
                            @endphp
                            <img
                                src="{{ $thumb ? $thumb : asset('images/video-placeholder.png') }}"
                                alt="{{ $video->title }}"
                                class="w-full h-40 sm:h-full object-cover"
                            >
                            @if(!empty($video->duration))
                                <span class="absolute bottom-2 right-2 bg-black/75 text-white text-xs px-2 py-0.5 rounded">{{ $video->duration }}</span>
                            @endif
                        </div>

                        {{-- Bilgiler --}}
                        <div class="flex-1 py-3 pr-4 sm:py-4 sm:pr-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-1 group-hover:text-[#3ea6ff]">{{ $video->title }}</h3>

                            <div class="text-sm text-gray-500 mb-1">
                                @if($video->channel)
                                    {{ $video->channel->name }}
                                @else
                                    Kanal #{{ $video->channel_id }}
                                @endif
                            </div>

                            <div class="text-xs text-gray-400 mb-2">
                                {{ number_format($video->views ?? 0) }} görüntüleme • {{ $video->created_at ? $video->created_at->diffForHumans() : '' }}
                            </div>

                            @if(!empty($video->description))
                                <p class="text-sm text-gray-600 line-clamp-2">{{ Str::limit($video->description, 150) }}</p>
                            @endif
                        </div>
                    </div>
                </a>
            @empty
                <div class="bg-gray-50 border border-dashed border-gray-200 rounded-lg p-8 text-center">
                    <h4 class="text-lg font-medium text-gray-800">Aradığınız videoyu bulamadık</h4>
                    <p class="mt-2 text-sm text-gray-500">Farklı anahtar kelimeler deneyin.</p>
                </div>
            @endforelse
        </div>

        {{-- Sayfalama --}}
        <div class="mt-8 flex items-center justify-center">
            @if(method_exists($videos, 'withQueryString'))
                {{ $videos->withQueryString()->links() }}
            @endif
        </div>
    </div>
@endsection

@push('footer')
    <style>
        .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    </style>
@endpush
