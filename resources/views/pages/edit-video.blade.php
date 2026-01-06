@extends('layouts.app', [
    'title' => 'Video Düzenle | LaravelTube'
])

@section('content')
    <div class="max-w-7xl mx-auto px-4 py-6">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-xl font-medium text-white">Video Detayları</h1>
            <button type="button" onclick="history.back()" class="text-[#3ea6ff] hover:text-[#65b8ff] transition-colors duration-200">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        @if(session('success'))
            <div class="mb-4 p-4 bg-[#303030] border border-[#4CAF50] text-[#4CAF50] rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-[#282828] rounded-lg p-6">
            <form action="{{ route('video.edit', ['channel' => $channel, 'video' => $video]) }}"
                  method="POST"
                  enctype="multipart/form-data"
                  class="space-y-6"
                  x-data="{ visibility: '{{ old('visibility', $video->visibility) }}' }">
            @csrf

            <div class="grid grid-cols-12 gap-6">
                <!-- Sol Taraf - Video Önizleme -->
                <div class="col-span-7">
                    <div class="aspect-video bg-[#1f1f1f] rounded-lg overflow-hidden mb-4">
                        <video src="{{ $video->url }}" class="w-full h-full" controls></video>
                    </div>
                    <div class="bg-[#1f1f1f] p-4 rounded-lg">
                        <h3 class="text-white text-sm font-medium mb-2">Video bağlantısı</h3>
                        <div class="flex">
                            <input type="text"
                                   value="{{ route('videos.show', $video->uid) }}"
                                   class="flex-1 bg-[#282828] text-white px-3 py-2 rounded-l-lg border border-[#3f3f3f] focus:outline-none"
                                   readonly>
                            <button type="button"
                                    onclick="navigator.clipboard.writeText('{{ route('videos.show', $video->uid) }}')"
                                    class="px-4 py-2 bg-[#3f3f3f] text-white rounded-r-lg hover:bg-[#4f4f4f] transition-colors">
                                Kopyala
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Sağ Taraf - Form Alanları -->
                <div class="col-span-5 space-y-4">
                    <!-- Video Başlığı -->
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-300 mb-1">Başlık</label>
                        <input type="text"
                               name="title"
                               id="title"
                               value="{{ old('title', $video->title) }}"
                               class="w-full px-4 py-2 bg-[#1f1f1f] border @error('title') border-red-500 @else border-[#3f3f3f] @enderror text-white rounded-lg focus:outline-none focus:border-[#3ea6ff] focus:ring-1 focus:ring-[#3ea6ff]"
                               placeholder="Videonuz için çekici bir başlık ekleyin">
                        @error('title')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Video Açıklaması -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-300 mb-1">Açıklama</label>
                        <textarea name="description"
                                  id="description"
                                  rows="4"
                                  class="w-full px-4 py-2 bg-[#1f1f1f] border @error('description') border-red-500 @else border-[#3f3f3f] @enderror text-white rounded-lg focus:outline-none focus:border-[#3ea6ff] focus:ring-1 focus:ring-[#3ea6ff]"
                                  placeholder="İzleyicileriniz için videoyu açıklayın">{{ old('description', $video->description) }}</textarea>
                        @error('description')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Görünürlük -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-3">Görünürlük</label>
                        <div class="space-y-3">
                            <!-- Herkese Açık -->
                            <div class="flex items-center p-3 rounded-lg cursor-pointer"
                                 :class="{ 'bg-[#3f3f3f]': visibility === 'public', 'hover:bg-[#1f1f1f]': visibility !== 'public' }"
                                 @click="visibility = 'public'">
                                <input type="radio" name="visibility" value="public" x-model="visibility" class="hidden">
                                <div class="flex items-center">
                                    <svg class="w-6 h-6 text-gray-300 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <div>
                                        <p class="text-white font-medium">Herkese Açık</p>
                                        <p class="text-sm text-gray-400">Herkes bu videoyu izleyebilir</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Liste Dışı -->
                            <div class="flex items-center p-3 rounded-lg cursor-pointer"
                                 :class="{ 'bg-[#3f3f3f]': visibility === 'unlisted', 'hover:bg-[#1f1f1f]': visibility !== 'unlisted' }"
                                 @click="visibility = 'unlisted'">
                                <input type="radio" name="visibility" value="unlisted" x-model="visibility" class="hidden">
                                <div class="flex items-center">
                                    <svg class="w-6 h-6 text-gray-300 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                                    </svg>
                                    <div>
                                        <p class="text-white font-medium">Liste Dışı</p>
                                        <p class="text-sm text-gray-400">Yalnızca bağlantıya sahip olanlar izleyebilir</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Özel -->
                            <div class="flex items-center p-3 rounded-lg cursor-pointer"
                                 :class="{ 'bg-[#3f3f3f]': visibility === 'private', 'hover:bg-[#1f1f1f]': visibility !== 'private' }"
                                 @click="visibility = 'private'">
                                <input type="radio" name="visibility" value="private" x-model="visibility" class="hidden">
                                <div class="flex items-center">
                                    <svg class="w-6 h-6 text-gray-300 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                    <div>
                                        <p class="text-white font-medium">Özel</p>
                                        <p class="text-sm text-gray-400">Sadece siz görüntüleyebilirsiniz</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @error('visibility')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Küçük Resim -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Küçük Resim</label>
                        <div class="grid grid-cols-3 gap-4">
                            <div class="relative aspect-video bg-[#1f1f1f] rounded-lg overflow-hidden group cursor-pointer">
                                <img src="{{ $video->thumbnail }}" alt="Mevcut küçük resim" class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity">
                                    <span class="text-white text-sm">Mevcut</span>
                                </div>
                            </div>
                            <label class="relative aspect-video bg-[#1f1f1f] rounded-lg overflow-hidden cursor-pointer hover:bg-[#3f3f3f] transition-colors">
                                <input type="file" name="thumbnail" accept="image/*" class="hidden">
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Kaydet Butonu -->
                    <div class="flex justify-end pt-6">
                        <button type="submit" class="bg-[#3ea6ff] text-black px-6 py-2.5 rounded-lg font-medium hover:bg-[#65b8ff] transition-colors duration-200">
                            Değişiklikleri Kaydet
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
