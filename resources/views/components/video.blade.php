<div @mouseenter='speechText(@json($video->title))' class="group bg-pink-50 border border-pink-200 rounded-2xl overflow-hidden shadow-sm hover:shadow-lg transition-shadow transform hover:-translate-y-1">
	{{-- Thumbnail --}}
	<div class="relative aspect-video overflow-hidden">
		<a href="{{ route('video.watch', $video->uid) }}" class="block w-full h-full">
			<img src="{{ Storage::url('videos/' . $video->uid . '/' . $video->thumbnail_image) }}"
					alt="{{ $video->title }}"
					class="w-full h-full object-cover transform group-hover:scale-105 transition duration-300">
			<div class="absolute bottom-2 right-2 bg-pink-600 text-white text-xs px-2 py-1 rounded-lg">
				{{ $video->duration ?? '0:00' }}
			</div>
		</a>
	</div>

	{{-- Video Bilgileri --}}
	<div class="p-4 flex gap-3">
		{{-- Kanal Avatarı --}}
		{{--<a href="{{ route('channel', $video->channel->slug) }}" class="flex-shrink-0">
			<img src="{{ $video->channel->image ? Storage::url($video->channel->image) : asset('images/default-avatar.png') }}"
					alt="{{ $video->channel->name }}"
					class="w-10 h-10 rounded-full">
		</a>--}}

		{{-- Başlık ve Detaylar --}}
		<div class="flex-1 min-w-0">
			<a href="{{ route('video.watch', $video->uid) }}"
				class="block font-semibold text-pink-700 text-sm line-clamp-2 mb-1 hover:text-pink-600">
				{{ $video->title }}
			</a>
			<a href="{{ route('channel', $video->channel->slug) }}"
				class="block text-pink-600 text-sm hover:text-pink-500">
				{{ $video->channel->name }}
			</a>
			@if(false)
			<div class="text-pink-500/80 text-sm flex items-center mt-1">
				<span>{{ number_format($video->views) }} izlenme</span>
				<span class="mx-1">•</span>
				<span>{{ $video->created_at->diffForHumans() }}</span>
			</div>
			@endif
		</div>

		{{-- Menü --}}
		<div class="relative">
			<button class="p-2 hover:bg-pink-100 rounded-full opacity-0 group-hover:opacity-100 transition-opacity">
				<i class="fas fa-ellipsis-vertical text-pink-500"></i>
			</button>
		</div>
	</div>
</div>

