<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
	@forelse($videos as $video)
		<x-video :video="$video" />
	@empty
		<div class="col-span-full flex flex-col items-center justify-center py-16">
			<div class="w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center mb-4">
				<i class="fas fa-video text-2xl text-gray-500"></i>
			</div>
			<h3 class="text-lg font-medium text-black mb-2">Henüz video yok</h3>
			<p class="text-gray-600 text-center">Bu kanala video yüklendiğinde burada görünecek.</p>
			@if(auth()->check() && isset($channel))
				@if(auth()->id() === $channel->user_id)
					<a href="{{ route('video.page', $channel->uid) }}"
						class="mt-6 px-6 py-2 bg-blue-500 text-white rounded-full hover:bg-blue-600 transition">
						Video Yükle
					</a>
				@endif
			@endauth
		</div>
	@endforelse
</div>


@push("footer")
	<script>
		vueMixinFunctions.push(() => ({
            data() {
                return {
                    
                };
            },
            methods: {
                
            }
        }));
	</script>
@endpush