<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{$title ?? config('app.name')}}</title>

    @stack('header')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        /* Mobil menü animasyonları için özel stiller */
        #sidebar {
            transition: transform 0.3s ease;
            transform: translateX(-100%);
            will-change: transform;
        }

        /* Menü açıkken */
        #sidebar:not(.-translate-x-full) {
            transform: translateX(0);
        }

        /* Desktop görünüm */
        @media (min-width: 1024px) {
            #sidebar {
                transform: translateX(0);
            }
        }

        /* Overlay stili */
        #sidebar-overlay {
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        #sidebar-overlay:not(.opacity-0) {
            opacity: 1;
        }
    </style>
</head>
<body class="bg-slate-100 dark:bg-slate-900 text-slate-900 dark:text-white">
<!-- Sol Sidebar -->
@if(false)
<aside id="sidebar" class="fixed left-0 top-0 h-full w-[280px] bg-white dark:bg-slate-800 shadow-lg z-50 -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out">
    <div class="flex flex-col h-full overflow-hidden">
        <!-- Logo Bölümü -->
        <div class="p-4 flex items-center space-x-4 border-b border-slate-100 dark:border-slate-700">
            <button id="menu-toggle" class="lg:hidden p-2 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-400 dark:focus:ring-slate-500">
                <i class="fas fa-bars text-xl"></i>
            </button>
            <a href="{{ route('home') }}" class="flex items-center space-x-2">
                <i class="fas fa-tv text-slate-800 dark:text-white text-2xl"></i>
                <span class="font-bold text-xl text-slate-800 dark:text-white">Stream</span>
            </a>
        </div>

        <!-- Ana Menü -->
        <nav class="flex-1 px-2 py-4 overflow-y-auto thin-scrollbar">
            <div class="space-y-1">
                <a href="{{ route('home') }}" class="flex items-center px-4 py-2.5 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg">
                    <i class="fas fa-compass w-5"></i>
                    <span class="ml-3 text-sm font-medium">Keşfet</span>
                </a>
                <a href="#" class="flex items-center px-4 py-2.5 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg">
                    <i class="fas fa-fire w-5"></i>
                    <span class="ml-3 text-sm font-medium">Popüler</span>
                </a>
                <a href="#" class="flex items-center px-4 py-2.5 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg">
                    <i class="fas fa-clock w-5"></i>
                    <span class="ml-3 text-sm font-medium">Son Yüklenenler</span>
                </a>
                @auth
                    <a href="#" class="flex items-center px-4 py-2.5 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg">
                        <i class="fas fa-heart w-5"></i>
                        <span class="ml-3 text-sm font-medium">Takip Edilenler</span>
                    </a>
                @endauth
            </div>

            @auth
                <div class="mt-8">
                    <h3 class="px-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                        Kitaplık
                    </h3>
                    <div class="mt-2 space-y-1">
                        <a href="#" class="flex items-center px-4 py-2.5 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg">
                            <i class="fas fa-history w-5"></i>
                            <span class="ml-3 text-sm font-medium">Geçmiş</span>
                        </a>
                        <a href="#" class="flex items-center px-4 py-2.5 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg">
                            <i class="fas fa-bookmark w-5"></i>
                            <span class="ml-3 text-sm font-medium">Kaydedilenler</span>
                        </a>
                        <a href="#" class="flex items-center px-4 py-2.5 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg">
                            <i class="fas fa-thumbs-up w-5"></i>
                            <span class="ml-3 text-sm font-medium">Beğenilenler</span>
                        </a>
                    </div>
                </div>
        @endauth
    </div>

    @auth
        <hr class="my-4 border-gray-700">

        <div class="space-y-1">
            <div class="px-4 py-2 text-sm text-gray-400">Kitaplık</div>
            <a href="#" class="flex items-center px-4 py-2 text-white hover:bg-gray-800 rounded-xl">
                <i class="fas fa-clock-rotate-left w-6 text-xl"></i>
                <span class="ml-3 text-sm">Geçmiş</span>
            </a>
            <a href="#" class="flex items-center px-4 py-2 text-white hover:bg-gray-800 rounded-xl">
                <i class="fas fa-clock w-6 text-xl"></i>
                <span class="ml-3 text-sm">Daha Sonra İzle</span>
            </a>
            <a href="#" class="flex items-center px-4 py-2 text-white hover:bg-gray-800 rounded-xl">
                <i class="fas fa-thumbs-up w-6 text-xl"></i>
                <span class="ml-3 text-sm">Beğendiğim Videolar</span>
            </a>
            <a href="#" class="flex items-center px-4 py-2 text-white hover:bg-gray-800 rounded-xl">
                <i class="fas fa-download w-6 text-xl"></i>
                <span class="ml-3 text-sm">İndirilenler</span>
            </a>
        </div>
    @endauth

    @auth
        <hr class="my-4 border-gray-700">

        <div class="space-y-1">
            <div class="px-4 py-2 text-sm text-gray-400">Abonelikler</div>
            <!-- Abonelikleri döngü ile listele -->
            <a href="#" class="flex items-center px-4 py-2 text-white hover:bg-gray-800 rounded-xl">
                <img src="https://via.placeholder.com/24" alt="Channel" class="w-6 h-6 rounded-full">
                <span class="ml-3 text-sm truncate">Kanal Adı 1</span>
                <span class="ml-auto">
                            <span class="w-2 h-2 bg-red-500 rounded-full"></span>
                        </span>
            </a>
            <a href="#" class="flex items-center px-4 py-2 text-white hover:bg-gray-800 rounded-xl">
                <img src="https://via.placeholder.com/24" alt="Channel" class="w-6 h-6 rounded-full">
                <span class="ml-3 text-sm truncate">Kanal Adı 2</span>
            </a>
        </div>

        <hr class="my-4 border-gray-700">

        <div class="space-y-1">
            <div class="px-4 py-2 text-sm text-gray-400">Keşfet</div>
            <a href="#" class="flex items-center px-4 py-2 text-white hover:bg-gray-800 rounded-xl">
                <i class="fas fa-fire w-6 text-xl"></i>
                <span class="ml-3 text-sm">Trendler</span>
            </a>
            <a href="#" class="flex items-center px-4 py-2 text-white hover:bg-gray-800 rounded-xl">
                <i class="fas fa-music w-6 text-xl"></i>
                <span class="ml-3 text-sm">Müzik</span>
            </a>
            <a href="#" class="flex items-center px-4 py-2 text-white hover:bg-gray-800 rounded-xl">
                <i class="fas fa-gamepad w-6 text-xl"></i>
                <span class="ml-3 text-sm">Oyun</span>
            </a>
            <a href="#" class="flex items-center px-4 py-2 text-white hover:bg-gray-800 rounded-xl">
                <i class="fas fa-newspaper w-6 text-xl"></i>
                <span class="ml-3 text-sm">Haberler</span>
            </a>
            <a href="#" class="flex items-center px-4 py-2 text-white hover:bg-gray-800 rounded-xl">
                <i class="fas fa-trophy w-6 text-xl"></i>
                <span class="ml-3 text-sm">Spor</span>
            </a>
        </div>
    @endauth

    <hr class="my-4 border-gray-700">

    <div class="space-y-1 pb-4">
        <div class="px-4 py-2 text-sm text-gray-400">YouTube'dan Daha Fazlası</div>
        <a href="#" class="flex items-center px-4 py-2 text-white hover:bg-gray-800 rounded-xl">
            <i class="fab fa-youtube text-red-600 w-6 text-xl"></i>
            <span class="ml-3 text-sm">YouTube Premium</span>
        </a>
        <a href="#" class="flex items-center px-4 py-2 text-white hover:bg-gray-800 rounded-xl">
            <i class="fas fa-film w-6 text-xl"></i>
            <span class="ml-3 text-sm">YouTube Films</span>
        </a>
        <a href="#" class="flex items-center px-4 py-2 text-white hover:bg-gray-800 rounded-xl">
            <i class="fas fa-cog w-6 text-xl"></i>
            <span class="ml-3 text-sm">Ayarlar</span>
        </a>
        <a href="#" class="flex items-center px-4 py-2 text-white hover:bg-gray-800 rounded-xl">
            <i class="fas fa-flag w-6 text-xl"></i>
            <span class="ml-3 text-sm">İçerik Bildirme</span>
        </a>
        <a href="#" class="flex items-center px-4 py-2 text-white hover:bg-gray-800 rounded-xl">
            <i class="fas fa-question-circle w-6 text-xl"></i>
            <span class="ml-3 text-sm">Yardım</span>
        </a>
        <a href="#" class="flex items-center px-4 py-2 text-white hover:bg-gray-800 rounded-xl">
            <i class="fas fa-exclamation-circle w-6 text-xl"></i>
            <span class="ml-3 text-sm">Geri Bildirim</span>
        </a>
    </div>
    </nav>

</aside>
@endif
<!-- Üst Menü -->
<nav class="fixed top-0 right-0 left-0 bg-white dark:bg-slate-800 shadow-sm z-20

@if(false) lg:pl-[320px] @endif">
    <div class="flex items-center justify-between h-16 px-4">
        <!-- Sol Bölüm - Mobil Menü -->
        <div class="lg:hidden flex items-center">
            <button id="mobile-menu-button" class="p-2 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg" aria-label="Toggle mobile menu">
                <i class="fas fa-bars text-xl"></i>
            </button>
            <a href="{{ route('home') }}" class="flex items-center space-x-2 ml-2">
                <i class="fas fa-tv text-slate-800 dark:text-white text-2xl"></i>
                <span class="font-bold text-lg text-slate-800 dark:text-white">Stream</span>
            </a>
        </div>

        <!-- Orta Bölüm - Arama -->
        <div class="flex-1 max-w-2xl mx-auto flex items-center px-4">
            <div class="relative flex-1">
                <div class="relative">
                    <form action="{{ route('search.video') }}" method="POST">
                        @csrf

                        <input type="text" name="title" placeholder="Video veya içerik ara..."
                               class="w-full bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-full px-4 py-2 pl-10 focus:outline-none focus:ring-2 focus:ring-slate-500">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-slate-400"></i>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sağ Bölüm - Kullanıcı Menüsü -->
        <div class="flex items-center space-x-4">
            {{--@auth
                <button class="p-2 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg">
                    <i class="fas fa-video text-slate-600 dark:text-slate-400"></i>
                </button>
            @endauth--}}
            <!--                <button class="p-2 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg">
                    <i class="fas fa-bell text-slate-600 dark:text-slate-400"></i>
                </button>-->
            @guest
                <a href="{{ route('login.page') }}" class="flex items-center space-x-2 bg-slate-800 text-white px-4 py-2 rounded-lg hover:bg-slate-700 transition-colors">
                    <i class="far fa-user-circle"></i>
                    <span class="text-sm font-medium">Giriş Yap</span>
                </a>
            @else
                <div class="flex items-center space-x-2">
                    <button id="create-button" class="relative p-2 hover:bg-slate-100 dark:hover:bg-gray-800 rounded-full group">
                        <i class="fas fa-video text-slate-700 dark:text-white"></i>
                        <div class="hidden group-hover:block absolute right-0 mt-2 w-48 bg-white dark:bg-[#282828] rounded-xl shadow-lg py-2 z-50">
                            <a href="{{ route('video.page', Auth::user()->channel->uid) }}" class="flex items-center px-4 py-2 hover:bg-slate-100 dark:hover:bg-gray-700 text-slate-700 dark:text-white">
                                <i class="fas fa-video w-5"></i>
                                <span class="ml-3 text-sm">Video Yükle</span>
                            </a>
<!--                            <a href="#" class="flex items-center px-4 py-2 hover:bg-slate-100 dark:hover:bg-gray-700 text-slate-700 dark:text-white">
                                <i class="fas fa-broadcast-tower w-5"></i>
                                <span class="ml-3 text-sm">Canlı Yayın</span>
                            </a>-->
                        </div>
                    </button>
                    <button id="notifications-button" class="relative p-2 hover:bg-slate-100 dark:hover:bg-gray-800 rounded-full group">
                        <i class="fas fa-bell text-slate-700 dark:text-white"></i>
                        <span class="absolute top-0 right-0 bg-red-600 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center">3</span>
                        <div class="hidden group-hover:block absolute right-0 mt-2 w-80 bg-white dark:bg-[#282828] rounded-xl shadow-lg py-2 z-50 text-slate-800 dark:text-white">
                            <div class="flex items-center justify-between px-4 py-2 border-b border-slate-200 dark:border-gray-700">
                                <span class="font-medium">Bildirimler</span>
                                <i class="fas fa-cog hover:text-gray-500 dark:hover:text-gray-300 cursor-pointer"></i>
                            </div>
                            <div class="max-h-96 overflow-y-auto thin-scrollbar">
                                <div class="p-4 hover:bg-slate-100 dark:hover:bg-gray-700 cursor-pointer">
                                    <div class="flex items-start space-x-3">
                                        <img src="https://via.placeholder.com/40" alt="Channel" class="w-10 h-10 rounded-full">
                                        <div class="flex-1">
                                            <p class="text-sm">Yeni video: "Laravel ile YouTube Klonu Yapımı"</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">2 saat önce</p>
                                        </div>
                                        <div class="text-gray-400">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </button>
                    <div class="relative">
                        <a>
                            <button id="user-menu-button" class="flex items-center space-x-2 focus:outline-none">
                                <img src="{{ auth()->user()->profile_image ?? asset('images/default-avatar.png') }}"
                                     onerror="this.src='{{ asset('images/default-avatar.png') }}'"
                                     alt="Avatar"
                                     class="w-8 h-8 rounded-full object-cover">
                            </button>
                        </a>
                        <div id="user-menu" class="hidden absolute right-0 mt-2 w-72 bg-white dark:bg-[#282828] rounded-xl shadow-lg py-2 z-50 text-slate-800 dark:text-white">
                            <div class="px-4 py-2 border-b border-slate-200 dark:border-gray-700">
                                <div class="flex items-center space-x-3">
                                    <img src="{{ auth()->user()->profile_image }}" alt="Avatar" class="w-10 h-10 rounded-full">
                                    <div class="flex-1">
                                        <div class="font-medium">{{ auth()->user()->name }}</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400 truncate">{{ auth()->user()->email }}</div>
<!--
                                        <a href="#" class="text-blue-600 dark:text-[#3ea6ff] text-sm hover:underline">Google Hesabınızı yönetin</a>
-->
                                    </div>
                                </div>
                            </div>
                            <div class="py-2">
                                <a href="{{ route('channel', Auth::user()->channel->slug) }}" class="flex items-center px-4 py-2 hover:bg-slate-100 dark:hover:bg-gray-700">
                                    <i class="far fa-user-circle w-5"></i>
                                    <span class="ml-3">Kanalım</span>
                                </a>
<!--                                <a href="#" class="flex items-center px-4 py-2 hover:bg-slate-100 dark:hover:bg-gray-700">
                                    <i class="fas fa-user-shield w-5"></i>
                                    <span class="ml-3">YouTube Studio</span>
                                </a>
                                <a href="#" class="flex items-center px-4 py-2 hover:bg-slate-100 dark:hover:bg-gray-700">
                                    <i class="fas fa-user-friends w-5"></i>
                                    <span class="ml-3">Hesap değiştir</span>
                                </a>-->
                                <hr class="my-2 border-slate-200 dark:border-gray-700">
<!--                                <a href="#" class="flex items-center px-4 py-2 hover:bg-slate-100 dark:hover:bg-gray-700">
                                    <i class="fas fa-moon w-5"></i>
                                    <span class="ml-3">Görünüm: Koyu</span>
                                </a>
                                <a href="#" class="flex items-center px-4 py-2 hover:bg-slate-100 dark:hover:bg-gray-700">
                                    <i class="fas fa-language w-5"></i>
                                    <span class="ml-3">Dil: Türkçe</span>
                                </a>
                                <a href="#" class="flex items-center px-4 py-2 hover:bg-slate-100 dark:hover:bg-gray-700">
                                    <i class="fas fa-shield-alt w-5"></i>
                                    <span class="ml-3">Kısıtlı Mod: Kapalı</span>
                                </a>
                                <hr class="my-2 border-slate-200 dark:border-gray-700">-->
                                <a href="{{ route('profile.page', Auth::id()) }}" class="flex items-center px-4 py-2 hover:bg-slate-100 dark:hover:bg-gray-700">
                                    <i class="fas fa-cog w-5"></i>
                                    <span class="ml-3">Ayarlar</span>
                                </a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center px-4 py-2 hover:bg-slate-100 dark:hover:bg-gray-700">
                                        <i class="fas fa-sign-out-alt w-5"></i>
                                        <span class="ml-3">Çıkış Yap</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endguest
        </div>
    </div>
</nav>
<div id="app" class="flex flex-col min-h-8">
    <main class="flex-grow pt-14 @if(false) lg:pl-[320px] @endif">
        @yield('content')
    </main>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Minimal, güvenli ve hata üretmeyen mobil sidebar kontrolü
        const sidebar = document.getElementById('sidebar');
        const mobileButton = document.getElementById('mobile-menu-button');
        const menuToggleInside = document.getElementById('menu-toggle');
        let overlay = null;

        if (!sidebar) return;

        const createOverlay = () => {
            if (overlay) return overlay;
            overlay = document.createElement('div');
            overlay.id = 'sidebar-overlay';
            overlay.className = 'fixed inset-0 bg-black/50 z-40 lg:hidden opacity-0 transition-opacity duration-300';
            document.body.appendChild(overlay);
            requestAnimationFrame(() => overlay.classList.remove('opacity-0'));
            overlay.addEventListener('click', closeMenu);
            return overlay;
        };

        const removeOverlay = () => {
            if (!overlay) return;
            overlay.classList.add('opacity-0');
            setTimeout(() => { overlay?.remove(); overlay = null; }, 250);
        };

        const openMenu = () => {
            sidebar.classList.remove('-translate-x-full');
            sidebar.classList.add('translate-x-0');
            createOverlay();
            document.documentElement.style.overflow = 'hidden';
            document.body.style.overflow = 'hidden';
        };

        const closeMenu = () => {
            sidebar.classList.add('-translate-x-full');
            sidebar.classList.remove('translate-x-0');
            removeOverlay();
            document.documentElement.style.overflow = '';
            document.body.style.overflow = '';
        };

        const toggleMenu = (e) => {
            if (e) e.stopPropagation();
            if (sidebar.classList.contains('-translate-x-full')) openMenu();
            else closeMenu();
        };

        if (mobileButton) mobileButton.addEventListener('click', toggleMenu);
        if (menuToggleInside) menuToggleInside.addEventListener('click', toggleMenu);

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                if (!sidebar.classList.contains('-translate-x-full')) closeMenu();
            }
        });

        const handleResize = () => {
            if (window.innerWidth >= 1024) {
                sidebar.classList.remove('-translate-x-full');
                sidebar.classList.add('translate-x-0');
                removeOverlay();
                document.documentElement.style.overflow = '';
                document.body.style.overflow = '';
            } else {
                if (!sidebar.classList.contains('-translate-x-full') && !overlay) {
                    sidebar.classList.add('-translate-x-full');
                    sidebar.classList.remove('translate-x-0');
                }
            }
        };

        window.addEventListener('resize', handleResize);
        handleResize();

        const userMenuButton = document.getElementById('user-menu-button');
        const userMenu = document.getElementById('user-menu');

        if (userMenuButton && userMenu) {
            userMenuButton.addEventListener('click', (e) => {
                e.stopPropagation();
                userMenu.classList.toggle('hidden');
            });

            document.addEventListener('click', (e) => {
                if (!userMenu.contains(e.target) && !userMenuButton.contains(e.target)) {
                    userMenu.classList.add('hidden');
                }
            });

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') userMenu.classList.add('hidden');
            });
        }
    });

    // Düzeltilmiş vueMixinFunctions
    const vueMixinFunctions = [
        () => ({
            methods: {
                setAppStore(env) {
                    if (this.appStore) {
                        this.appStore.setAuth(@json(auth()->user()));
                        this.appStore.setEnv(env);
                    }
                }
            }
        })
    ];
</script>


@stack('footer')
</body>
</html>
