@extends('layouts.app', [
		'title' => $currentVideo->title
	])

@section('content')
    <div id="app" class="max-w-7xl mx-auto px-4 py-6 bg-gray-50 min-h-screen">
        <div class="flex flex-col lg:flex-row gap-8" data-channel-id="{{ $currentVideo->channel->id }}">

            {{-- SOL BÖLÜM --}}
            <div class="flex-1 space-y-6">

                {{-- Video Player --}}
                <div class="bg-black rounded-xl overflow-hidden shadow-md">
                    <video
						id="video"
                        controls
                        {{-- autoplay --}}
                        class="w-full aspect-video"
                        poster="{{ $currentVideo->thumbnail }}"
                    >
                        <source src="{{ $currentVideo->video_url }}"
                                type="video/mp4">
                        Tarayıcınız video etiketini desteklemiyor.
                    </video>
                </div>

                {{-- Başlık --}}
                <h1 class="text-2xl font-semibold text-gray-900">{{ $currentVideo->title }}</h1>

                {{-- Kanal Bilgileri + Etkileşim --}}
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 pb-4 border-b border-gray-200">

                    {{-- Kanal Bilgileri --}}
                    <div class="flex items-center gap-4">
                        <a href="{{ route('channel', $currentVideo->channel->slug) }}" class="flex items-center gap-3 group">
                            <img
                                src="{{ $currentVideo->channel->image ? asset($currentVideo->channel->image) : asset('images/default-avatar.png') }}"
                                class="w-12 h-12 rounded-full object-cover ring-2 ring-transparent group-hover:ring-red-500 transition"
                            >
                            <div>
                                <div class="flex items-center gap-1 font-semibold text-gray-900">
                                    {{ $currentVideo->channel->name }}
                                    @if($currentVideo->channel->verified)
                                        <i class="fas fa-check-circle text-blue-500 text-sm"></i>
                                    @endif
                                </div>
                                <p class="text-gray-500 text-sm">@{{ subscriberCount }} abone</p>
                            </div>
                        </a>

                        {{-- Abone Ol Butonu --}}
                        <transition name="pulse-fade" mode="out-in">
                            <button
                                :class="[
                                    'relative font-medium px-5 py-2 rounded-full transition-all duration-300 flex items-center justify-center gap-2 border shadow-sm',
                                    isSubscribed
                                        ? 'bg-white text-gray-900 border-gray-300 hover:bg-gray-50'
                                        : 'bg-red-600 text-white border-transparent hover:bg-red-700',
                                    animating ? 'scale-105' : ''
                                ]"
                                @click="toggleSubscribe({{ $currentVideo->channel->id }})"
                            >
                                <span v-if="!isSubscribed" class="flex items-center gap-2">
                                    <i class="fas fa-bell"></i>
                                    Abone Ol
                                </span>
                                <span v-else class="flex items-center gap-2">
                                    <i class="fas fa-check text-green-600"></i>
                                    Abone Olundu
                                </span>

                                <span v-if="animating" class="absolute inset-0 rounded-full bg-gray-200/40 animate-ping"></span>
                            </button>
                        </transition>
                    </div>

                    {{-- 🎬 Etkileşim Butonları --}}
                    <div class="flex items-center gap-4">
                        <button
                            @click="toggleLike({{ $currentVideo->id }})"
                            :class="[
        'flex items-center gap-2 px-4 py-2 rounded-full border border-gray-300 bg-white hover:bg-gray-100 transition-all duration-200',
        liked ? 'text-blue-600 scale-110' : 'text-gray-700',
        likeAnimating ? 'animate-bounce' : ''
    ]"
                        >
                            <i class="fa-solid fa-thumbs-up"></i>
                            <span>@{{ likeCount }}</span>
                        </button>

                        <button
                            @click="toggleDislike({{ $currentVideo->id }})"
                            :class="[
        'flex items-center gap-2 px-4 py-2 rounded-full border border-gray-300 bg-white hover:bg-gray-100 transition-all duration-200',
        disliked ? 'text-red-600 scale-110' : 'text-gray-700',
        dislikeAnimating ? 'animate-bounce' : ''
    ]"
                        >
                            <i class="fa-solid fa-thumbs-down"></i>
                            <span>@{{ dislikeCount }}</span>
                        </button>

                    </div>



                </div>

                {{-- Video Bilgisi --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                    <div class="text-sm text-gray-600 mb-2 flex items-center gap-2">
                        <span>{{ number_format($currentVideo->views) }} görüntüleme</span>
                        <span>•</span>
                        <span>{{ $currentVideo->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="text-gray-800 whitespace-pre-line leading-relaxed">{{ $currentVideo->description }}</p>
                </div>

                {{-- 💬 YORUMLAR --}}
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        Yorumlar (@{{ commentCount }})
                    </h3>

                    {{-- Yorum Yaz --}}
                    <div class="flex gap-3 mb-6">
                        <img
                            src="{{ auth()->check() ? (auth()->user()->profile_image ? asset(auth()->user()->profile_image) : asset('images/default-avatar.png')) : asset('images/default-avatar.png') }}"
                            class="w-10 h-10 rounded-full"
                        >
                        <input
                            v-model="newComment"
                            @keyup.enter="postComment({{ $currentVideo->id }})"
                            type="text"
                            placeholder="Yorum ekle..."
                            class="flex-1 bg-transparent border-b border-gray-300 focus:border-gray-600 text-gray-800 pb-2 outline-none placeholder-gray-500"
                        >
                        <button
                            @click="postComment({{ $currentVideo->id }})"
                            class="px-5 py-2 rounded-full bg-gray-100 text-gray-800 font-semibold text-sm border border-gray-300 hover:bg-gray-200 active:scale-95 transition duration-200 flex items-center gap-2"
                        >
                            <i class="fas fa-paper-plane text-gray-700"></i>
                            <span>Paylaş</span>
                        </button>


                    </div>

                    {{-- Yorumlar Listesi --}}
                    <div v-if="loadingComments" class="text-gray-500">Yükleniyor...</div>
                    <div v-else class="space-y-6">
                        <div v-for="comment in comments" :key="comment.id" class="flex gap-3 group">
                            <!-- Profil -->
                            <img
                                :src="comment.user?.profile_image ? `/${comment.user.profile_image}` : '/images/default-avatar.png'"
                                class="w-10 h-10 rounded-full"
                            >

                            <div class="flex-1">
                                <!-- Kullanıcı + Zaman -->
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2 text-sm">
                                        <span class="font-semibold text-gray-900">@{{ comment.user?.name }}</span>
                                        <span class="text-gray-500 text-xs">• az önce</span>
                                    </div>
                                    <button
                                        v-if="comment.user_id === appStore.auth?.id"
                                        @click="deleteComment({{ $currentVideo->id }}, comment.id)"
                                        class="opacity-0 group-hover:opacity-100 text-gray-400 hover:text-red-600 text-xs transition"
                                        title="Yorumu Sil"
                                    >
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>

                                <!-- Yorum içeriği -->
                                <p class="text-gray-800 mt-1 text-sm leading-relaxed">@{{ comment.comment }}</p>

                                <!-- Alt Yorumlar -->
                                <div v-if="comment.replies && comment.replies.length" class="ml-12 mt-3 space-y-4 border-l border-gray-200 pl-4">
                                    <div
                                        v-for="reply in comment.replies"
                                        :key="reply.id"
                                        class="flex gap-3 group"
                                    >
                                        <img
                                            :src="reply.user?.profile_image ? `/${reply.user.profile_image}` : '/images/default-avatar.png'"
                                            class="w-8 h-8 rounded-full"
                                        >
                                        <div class="flex-1">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-2 text-sm">
                                                    <span class="font-medium text-gray-900">@{{ reply.user?.name }}</span>
                                                    <span class="text-gray-500 text-xs">• az önce</span>
                                                </div>
                                                <button
                                                    v-if="reply.user_id === appStore.auth?.id"
                                                    @click="deleteComment({{ $currentVideo->id }}, reply.id)"
                                                    class="opacity-0 group-hover:opacity-100 text-gray-400 hover:text-red-600 text-xs transition"
                                                    title="Yorumu Sil"
                                                >
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                            <p class="text-gray-800 text-sm mt-1 leading-relaxed">@{{ reply.comment }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Yanıt Ekle -->
                                <div class="ml-12 mt-3 flex items-center gap-2">
                                    <img
                                        src="{{ auth()->check() ? (auth()->user()->profile_image ? asset(auth()->user()->profile_image) : asset('images/default-avatar.png')) : asset('images/default-avatar.png') }}"
                                        class="w-8 h-8 rounded-full"
                                    >
                                    <div class="flex-1 relative">
                                        <input
                                            v-model="comment.replyText"
                                            placeholder="Yanıt ekle..."
                                            class="w-full bg-transparent border-b border-gray-300 focus:border-blue-600 text-sm pb-1 outline-none transition"
                                        >
                                        <button
                                            @click="replyToComment({{ $currentVideo->id }}, comment.id, comment.replyText)"
                                            class="absolute right-0 top-1/2 -translate-y-1/2 text-blue-600 text-xs font-semibold hover:underline transition"
                                        >
                                            Paylaş
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- SAĞ BÖLÜM: Önerilen Videolar --}}
            <aside class="lg:w-96 w-full space-y-3">
                @foreach($videos as $video)
					<x-video :video="$video" />
                @endforeach
            </aside>
        </div>
    </div>
	<Modal size="lg" v-if="showModal" :hide-header="true" :allow-back-drop-click="false" :hide-footer="true">
		<template v-slot:header> [İÇERİK BURAYA] </template>
		<template v-slot:body> 
			<iframe width="1200px" height="600px" src="/games/karsiya-gecirme-big-small/index.html" frameborder="0"></iframe>	
		</template>
	</Modal>
    @push('footer')
        <script>
            vueMixinFunctions.push(() => ({
			components: {
				Modal
			},
                data() {
                    return {
						showModal: false,
                        isSubscribed: false,
                        subscriberCount: 0,
                        animating: false,
                        liked: false,
                        disliked: false,
                        likeCount: 0,
                        dislikeCount: 0,
                        likeAnimating: false,
                        dislikeAnimating: false,
                        comments: [],
                        newComment: '',
                        commentCount: 0,
                        loadingComments: false,
                        posting: false,
                        fiveMinuteTimer: null,
                    }
                },
                mounted() {
					

                    const channelId = {{ $currentVideo->channel->id }};
                    const videoId = {{ $currentVideo->id }};

                    axios.get(`/api/check-subscription/${channelId}`).then(res => {
                        this.isSubscribed = res.data.subscribed;
                        this.subscriberCount = res.data.count;
                    }).catch(()=>{});

                    axios.get(`/api/count/${videoId}`).then(res => {
                        this.likeCount = res.data.likes;
                        this.dislikeCount = res.data.dislikes;
                    }).catch(()=>{});

                    axios.get(`/api/check-like/${videoId}`).then(res => {
                        this.liked = !!res.data.like;
                        this.disliked = !!res.data.dislike;
                    }).catch(()=>{});

                    this.fetchComments(videoId);
					
					// Video bitme eventi
					const videoElement = document.getElementById('video');
					if (videoElement) {
						// videoElement.addEventListener('ended', async () => {
						// 	await this.onVideoEnded();
						// 	const limitExceeded = await this.checkDailyWatchLimit();
						// 	if (limitExceeded) {
						// 		window.location.href = '/errors/video-watch-limit-reached';
						// 	}
						// });

						videoElement.addEventListener('play', async () => {
							const limitExceeded = await this.checkDailyWatchLimit();
							if (limitExceeded) {
								window.location.href = '/errors/video-watch-limit-reached';
							}
							
							// Video başladığında 5 dakika sonra modal açmak için timer başlat
							if (this.fiveMinuteTimer) {
								clearTimeout(this.fiveMinuteTimer);
							}
							this.fiveMinuteTimer = setTimeout(() => {
								this.openModalAfterFiveMinutes();
								this.onVideoEnded();
							}, 60 * 5 * 1000); // 5 dakika = 300.000 ms
						});
						
						videoElement.addEventListener('pause', () => {
							// Video duraklatıldığında timer'ı iptal et
							if (this.fiveMinuteTimer) {
								clearTimeout(this.fiveMinuteTimer);
								this.fiveMinuteTimer = null;
							}
						});
					}
                },
				created(){
					const self = this;
					setTimeout(() => {
						window.addEventListener('message', function(event) {
							// ikinci settimeout, cevap geldikten birsüre sonra modal'ı kapat ve videoyu oynat, çünkü modal'da "HARİKA BAŞARDIN!" sesi var.
							setTimeout(() => {
								self.showModal = false;
								// Modal kapandıktan sonra videoyu kaldığı yerden başlat
								const videoElement = document.getElementById('video');
								if (videoElement && videoElement.paused) {
									if (videoElement.requestFullscreen) videoElement.requestFullscreen();
									videoElement.play();
								}
							}, 1800);
						});
					}, 1000);
				},
                methods: {
					
					openModalAfterFiveMinutes() {
						// Videoyu duraklat
						const videoElement = document.getElementById('video');
						if (videoElement && !videoElement.paused) {
							videoElement.pause();
							document.exitFullscreen();
						}
						
						// Modal'ı aç
						this.showModal = true;
					},
					
					async onVideoEnded() {
						const videoId = {{ $currentVideo->id }};
						const result = await axios.post(`/api/videos/${videoId}/end`);
						return result.data;
					},

					async checkDailyWatchLimit() {
						try {
							const response = await axios.get('/api/check-daily-watch-limit');
							return response.data.limit_exceeded;
						} catch (error) {
							console.error('Günlük izleme limiti kontrol hatası:', error);
							return true; // Hata durumunda izleme izni ver
						}
					},
					
                    async toggleSubscribe(channelId) {
                        try {
                            const response = await axios.post(`/api/channels/${channelId}`);
                            if (response.data.success) {
                                this.isSubscribed = !this.isSubscribed;
                            }
                        } catch (error) {
                            console.error('Subscribe hatası:', error);
                        }
                    },

                    async toggleLike(videoId) {
                        if (!this.appStore.auth) return (window.location.href = '/login');

                        // 🔹 Anında tepki (animasyon + sayı)
                        this.likeAnimating = true;
                        this.liked = !this.liked;
                        if (this.liked) {
                            this.likeCount++;
                            if (this.disliked) {
                                this.disliked = false;
                                this.dislikeCount--;
                            }
                        } else {
                            this.likeCount--;
                        }

                        // 🔹 Kısa animasyon (0.4s)
                        setTimeout(() => this.likeAnimating = false, 400);

                        // 🔹 Backend sync
                        try {
                            await axios.post(`/api/videos/${videoId}/like`);
                            const counts = await axios.get(`/api/count/${videoId}`);
                            this.likeCount = counts.data.likes;
                            this.dislikeCount = counts.data.dislikes;
                        } catch (err) {
                            console.error('Like hatası:', err);
                        }
                    },

                    async toggleDislike(videoId) {
                        if (!this.appStore.auth) return (window.location.href = '/login');

                        // 🔹 Anında tepki (animasyon + sayı)
                        this.dislikeAnimating = true;
                        this.disliked = !this.disliked;
                        if (this.disliked) {
                            this.dislikeCount++;
                            if (this.liked) {
                                this.liked = false;
                                this.likeCount--;
                            }
                        } else {
                            this.dislikeCount--;
                        }

                        // 🔹 Kısa animasyon (0.4s)
                        setTimeout(() => this.dislikeAnimating = false, 400);

                        // 🔹 Backend sync
                        try {
                            await axios.post(`/api/videos/${videoId}/dislike`);
                            const counts = await axios.get(`/api/count/${videoId}`);
                            this.likeCount = counts.data.likes;
                            this.dislikeCount = counts.data.dislikes;
                        } catch (err) {
                            console.error('Dislike hatası:', err);
                        }
                    },

                    async fetchComments(videoId) {
                        this.loadingComments = true;
                        try {
                            const res = await axios.get(`/api/comments/${videoId}`);
                            if (res.data.success) {
                                this.comments = res.data.comments.map(c => {
                                    c.replyText = '';
                                    const addReplyText = (node) => {
                                        if (!node.replies) return;
                                        node.replies.forEach(r => {
                                            r.replyText = '';
                                            addReplyText(r);
                                        });
                                    };
                                    addReplyText(c);
                                    return c;
                                });
                                this.commentCount = res.data.count;
                            }
                        } catch (err) {
                            console.error(err);
                        } finally {
                            this.loadingComments = false;
                        }
                    },

                    async postComment(videoId) {
                        if (!this.appStore.auth) return (window.location.href = '/login');
                        if (!this.newComment.trim()) return;

                        this.posting = true;
                        try {
                            const res = await axios.post(`/api/comment/${videoId}`, { comment: this.newComment });
                            if (res.data.success) {
                                const created = res.data.comment;
                                created.replyText = '';
                                created.replies = created.replies || [];
                                this.comments.unshift(created);
                                this.commentCount++;
                                this.newComment = '';
                            }
                        } catch (err) {
                            console.error(err);
                        } finally {
                            this.posting = false;
                        }
                    },

                    async replyToComment(videoId, parentId, replyText) {
                        if (!this.appStore.auth) return (window.location.href = '/login');
                        if (!replyText || !replyText.trim()) return;

                        try {
                            const res = await axios.post(`/api/comment/${videoId}/reply`, {
                                comment: replyText,
                                parent_id: parentId
                            });

                            if (res.data.success) {
                                const parent = this.findCommentRecursive(this.comments, parentId);
                                if (parent) {
                                    parent.replies = parent.replies || [];
                                    parent.replies.push(res.data.reply);
                                    parent.replyText = '';
                                } else {
                                    await this.fetchComments(videoId);
                                }
                            }
                        } catch (err) {
                            console.error(err);
                        }
                    },

                    async deleteComment(videoId, commentId) {
                        if (!confirm("Yorumu silmek istediğine emin misin?")) return;
                        try {
                            const res = await axios.delete(`/api/comment/${videoId}`, { data: { id: commentId } });
                            if (res.data.success) {
                                this.removeCommentRecursive(this.comments, commentId);
                                this.commentCount = Math.max(0, this.commentCount - 1);
                            }
                        } catch (err) {
                            console.error(err);
                        }
                    },

                    findCommentRecursive(list, id) {
                        for (let i = 0; i < list.length; i++) {
                            const item = list[i];
                            if (item.id === id) return item;
                            if (item.replies && item.replies.length) {
                                const found = this.findCommentRecursive(item.replies, id);
                                if (found) return found;
                            }
                        }
                        return null;
                    },

                    removeCommentRecursive(list, id) {
                        for (let i = list.length - 1; i >= 0; i--) {
                            const item = list[i];
                            if (item.id === id) {
                                list.splice(i, 1);
                                return true;
                            }
                            if (item.replies && item.replies.length) {
                                const removed = this.removeCommentRecursive(item.replies, id);
                                if (removed) {
                                    return true;
                                }
                            }
                        }
                        return false;
                    }
                }
            }));
        </script>
    @endpush

@endsection
