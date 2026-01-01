@extends('layouts.app', [
    'title' => $channel->name . ' | YouTube'
])

@push('footer')
    <script>
        function showTab(tabId) {
            document.querySelectorAll('.tab-content').forEach(content => {
                content.style.display = 'none';<
            });

            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('border-black', 'text-black');
                btn.classList.add('border-transparent');
            });

            document.getElementById(tabId).style.display = 'block';

            const activeBtn = document.querySelector(`[data-tab="${tabId}"]`);
            activeBtn.classList.remove('border-transparent');
            activeBtn.classList.add('border-black', 'text-black');

            const url = new URL(window.location);
            url.searchParams.set('tab', tabId);
            window.history.pushState({}, '', url);
        }

        document.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            const activeTab = urlParams.get('tab') || 'videos';
            showTab(activeTab);
        });
    </script>
@endpush

@section('content')
    <div class="min-h-screen text-black bg-white">
        {{-- Kanal Banner --}}
        <div class="relative w-full h-[180px] md:h-[220px] lg:h-[260px] bg-gray-200">
            @if($channel->banner)
                <img src="{{ asset($channel->banner) }}" alt="Channel Banner" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-b from-white/60 to-transparent"></div>
            @else
                <div class="flex items-center justify-center h-full text-gray-500 text-sm">
                    <i class="fas fa-image mr-2"></i>
                    Kanal kapağı yüklenmedi
                </div>
            @endif
        </div>

        {{-- Kanal Bilgileri --}}
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 -mt-12 relative z-10 mb-6">
                <div class="flex flex-col md:flex-row items-start md:items-end gap-6">
                    <img src="{{ $channel->image ? asset($channel->image) : asset('images/default-avatar.png') }}"
                         alt="Kanal Profil"
                         class="w-24 h-24 md:w-32 md:h-32 rounded-full border-4 border-white shadow-lg object-cover">

                    <div class="md:mb-2">
                        <h1 class="text-2xl font-semibold mb-1">{{ $channel->name }}</h1>
                        <div class="flex items-center gap-4 text-gray-600 text-sm">
                            <span>{{ '@' . $channel->slug }}</span>
                            <span>{{ number_format(rand(10000, 1000000)) }} abone</span>
                            <span>{{ $totalVideoCount }} video</span>
                            
                            <span >Toplam görüntülenme</span>
                            <span >{{ $totalWatchCount }} kez</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                   @auth
                        <a href="#" class="bg-white text-black px-4 py-2 rounded-full font-medium border border-black hover:bg-black hover:text-white transition">
                            Kanal Ayarlar
                        </a>
                   @endauth

                </div>
            </div>

            {{-- Sekmeler --}}
            <div class="border-b border-gray-300 mt-4">
                <div class="max-w-7xl mx-auto">
                    <div class="px-4 flex gap-8 text-gray-600 font-medium overflow-x-auto thin-scrollbar">
                        <button onclick="showTab('videos')" class="tab-btn py-4 border-b-2 border-black text-black whitespace-nowrap active" data-tab="videos">Videolar</button>
                        <button onclick="showTab('about')" class="tab-btn py-4 border-b-2 border-transparent hover:text-black whitespace-nowrap" data-tab="about">Hakkında</button>
                    </div>
                </div>
            </div>

            {{-- Tab Contents --}}
			 <x-videos-grid :videos="$channel->videos" :channel="$channel" />
           

            {{-- About Tab --}}
            <div id="about" class="tab-content max-w-7xl mx-auto px-4 pb-16" style="display: none;">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Sol Sütun - Kanal Açıklaması -->
                    <div class="lg:col-span-2">
                        <div class="bg-white shadow-md rounded-xl p-6">
                            <h3 class="text-lg font-semibold mb-4 text-black">Açıklama</h3>
                            <p class="text-gray-700 whitespace-pre-line">{{ $channel->description ?? 'Henüz bir kanal açıklaması eklenmemiş.' }}</p>
                        </div>
                    </div>

                    <!-- Sağ Sütun - İstatistikler -->
                    <div>
                        <div class="bg-white shadow-md rounded-xl p-6">
                            <h3 class="text-lg font-semibold mb-4 text-black">İstatistikler</h3>
                            <div class="space-y-4">
                                <div>
                                    <p class="text-gray-600">Katılma tarihi</p>
                                    <p class="text-black font-medium">{{ $channel->created_at->format('d F Y') }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-600">Toplam görüntülenme</p>
                                    <p class="text-black font-medium">{{ $totalWatchCount }}</p>
                                </div>
<!--                                <div>
                                    <p class="text-gray-600">Konum</p>
                                    <p class="text-black font-medium"></p>
                                </div>-->
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <style>
                .thin-scrollbar::-webkit-scrollbar {
                    height: 3px;
                }
                .thin-scrollbar::-webkit-scrollbar-track {
                    background: transparent;
                }
                .thin-scrollbar::-webkit-scrollbar-thumb {
                    background-color: #c0c0c0;
                    border-radius: 3px;
                }
                .thin-scrollbar::-webkit-scrollbar-thumb:hover {
                    background-color: #a0a0a0;
                }
            </style>
        </div>
    </div>
@endsection
