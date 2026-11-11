@extends('layouts.app', [
    'title' => $channel->name . ' | Minik Ekran'
])

@push('header')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700;800&display=swap" rel="stylesheet">
@endpush

@php
    // Kanalın resmi: en son eklenen videonun kapağı
    $avatar = $channel->videos->sortByDesc('created_at')->first()?->thumbnail_url;
@endphp

@section('content')
<div class="min-h-screen bg-candy-blush font-nunito text-candy-ink">
    <div class="mx-auto flex max-w-[1600px] flex-col gap-4 px-3 pb-16 pt-3 sm:px-4">

        {{-- Kanal başlığı: ana sayfaya dön + kanal resmi + adı --}}
        <div class="flex items-center gap-3">
            <a href="{{ route('home') }}" aria-label="Ana sayfa"
               class="flex h-12 w-12 flex-none items-center justify-center rounded-full bg-white shadow-soft transition hover:shadow-soft-lg">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="#FF8FB1" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
            </a>

            <span class="relative h-14 w-14 flex-none overflow-hidden rounded-full border-2 border-white bg-pastel-pink shadow-soft sm:h-16 sm:w-16">
                @if($avatar)
                    <img src="{{ $avatar }}" alt="" onerror="this.remove()" class="h-full w-full object-cover">
                @endif
            </span>

            <div class="min-w-0">
                <h1 class="line-clamp-1 text-lg font-bold leading-tight sm:text-xl"
                    data-speech="true" data-speech-text="{{ $channel->name }}">{{ $channel->name }}</h1>
                <p class="text-sm font-normal text-candy-muted">{{ $totalVideoCount }} video</p>
            </div>
        </div>

        {{-- Videolar: önce en az izlenenler (VideoController@channel sıralaması) --}}
        @if($channel->videos->isEmpty())
            <p class="rounded-3xl bg-white p-5 font-bold shadow-soft">Bu kanalda henüz video yok.</p>
        @else
            <section aria-label="Videolar" class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 2xl:grid-cols-6">
                @foreach($channel->videos as $video)
                    <x-kid-video-card :video="$video" />
                @endforeach
            </section>
        @endif
    </div>
</div>
@endsection
