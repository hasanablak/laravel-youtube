@extends('layouts.app', [
    "title" => "YouTube"
])

@section('content')
    <div class="max-w-7xl mx-auto px-4 text-gray-900" style="margin-top: 30px;">

        {{-- Kategoriler / Channels --}}
        <div class="mb-8">
              <h2 class="text-2xl font-bold text-pink-600 mb-4">Kanallar</h2>
            <div class="flex flex-wrap gap-4 pb-4">
                @foreach($channels as $channel)
                    <a href="{{ route('channel', $channel->slug) }}" 
                       class="flex-shrink-0 group">
                        <div class="flex flex-col items-center">
                            {{-- Channel Avatar --}}
                            <div class="w-20 h-20 rounded-full overflow-hidden bg-gray-200 mb-2 ring-2 ring-transparent group-hover:ring-pink-300 transition-all">
                                    @if($channel->latest_thumbnail)
                                        <img src="{{ Storage::url('videos/' . $channel->videos->first()->uid . '/' . $channel->latest_thumbnail) }}" 
                                             alt="{{ $channel->name }}"
                                             class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-pink-400 to-pink-600 text-white text-2xl font-bold">
                                            {{ substr($channel->name, 0, 1) }}
                                        </div>
                                    @endif
                            </div>
                            {{-- Channel Name --}}
                                <span class="text-sm font-medium text-pink-600 group-hover:text-pink-700 text-center max-w-[80px] line-clamp-2">
                                {{ $channel->name }}
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
		
        {{-- Video Grid --}}
        <x-videos-grid :videos="$videos" />
    </div>

    <style>
        .thin-scrollbar::-webkit-scrollbar {
            height: 8px;
        }
        .thin-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .thin-scrollbar::-webkit-scrollbar-thumb {
            background-color: #c0c0c0;
            border-radius: 4px;
        }
        .thin-scrollbar::-webkit-scrollbar-thumb:hover {
            background-color: #a0a0a0;
        }
    </style>
@endsection
