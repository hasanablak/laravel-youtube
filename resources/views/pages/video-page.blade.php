@extends('layouts.app', ['title' => $video->title . ' | Stream'])

@section('content')
    <div id="app" class="max-w-7xl mx-auto px-4 py-6 bg-gray-50 min-h-screen">
        <div class="flex flex-col lg:flex-row gap-8" data-channel-id="{{ $video->channel->id }}">

            {{-- SOL BÖLÜM --}}
            <div class="flex-1 space-y-6">

                {{-- Video Player --}}
                <div class="bg-black rounded-xl overflow-hidden shadow-md">
                    <video
						id="video"
                        controls
                        {{-- autoplay --}}
                        class="w-full aspect-video"
                        poster="{{ $video->thumbnail }}"
                    >
                        <source src="{{ $video->video_url }}"
                                type="video/mp4">
                        Tarayıcınız video etiketini desteklemiyor.
                    </video>
                </div>

                {{-- Başlık --}}
                <h1 class="text-2xl font-semibold text-gray-900">{{ $video->title }}</h1>

                {{-- Kanal Bilgileri + Etkileşim --}}
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 pb-4 border-b border-gray-200">

                    {{-- Kanal Bilgileri --}}
                    <div class="flex items-center gap-4">
                        <a href="{{ route('channel', $video->channel->slug) }}" class="flex items-center gap-3 group">
                            <img
                                src="{{ $video->channel->image ? asset($video->channel->image) : asset('images/default-avatar.png') }}"
                                class="w-12 h-12 rounded-full object-cover ring-2 ring-transparent group-hover:ring-red-500 transition"
                            >
                            <div>
                                <div class="flex items-center gap-1 font-semibold text-gray-900">
                                    {{ $video->channel->name }}
                                    @if($video->channel->verified)
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
                                @click="toggleSubscribe({{ $video->channel->id }})"
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
                            @click="toggleLike({{ $video->id }})"
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
                            @click="toggleDislike({{ $video->id }})"
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
                        <span>{{ number_format($video->views) }} görüntüleme</span>
                        <span>•</span>
                        <span>{{ $video->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="text-gray-800 whitespace-pre-line leading-relaxed">{{ $video->description }}</p>
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
                            @keyup.enter="postComment({{ $video->id }})"
                            type="text"
                            placeholder="Yorum ekle..."
                            class="flex-1 bg-transparent border-b border-gray-300 focus:border-gray-600 text-gray-800 pb-2 outline-none placeholder-gray-500"
                        >
                        <button
                            @click="postComment({{ $video->id }})"
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
                                        @click="deleteComment({{ $video->id }}, comment.id)"
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
                                                    @click="deleteComment({{ $video->id }}, reply.id)"
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
                                            @click="replyToComment({{ $video->id }}, comment.id, comment.replyText)"
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
					{{-- <a href="{{ route('video.watch', $video->uid) }}">
						<div class="flex gap-3 p-2 rounded-lg hover:bg-gray-100 cursor-pointer transition">
							<div class="relative w-40 flex-shrink-0 rounded-md overflow-hidden">
								<img src="{{ $video->thumbnail_url }}" class="w-full h-full object-cover">
								<div class="absolute bottom-1 right-1 bg-black/80 text-white text-xs px-2 py-0.5 rounded">
								{{ str_pad($video->duration, 2, '0', STR_PAD_LEFT) }}
								</div>
							</div>
							<div class="flex-1 min-w-0">
								<h3 class="text-sm font-medium text-gray-900 line-clamp-2">
									{{ $video->title }}
								</h3>
								<p class="text-gray-600 text-xs mt-1">{{ $video->channel->name }}</p>
								<p class="text-gray-500 text-xs mt-1">
									{{ number_format($video->views) }} görüntüleme • {{ $video->created_at->diffForHumans() }}
								</p>
							</div>
						</div>
					</a> --}}
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
						showModal: true,
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
                    }
                },
                mounted() {
					

                    const channelId = {{ $video->channel->id }};
                    const videoId = {{ $video->id }};

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
						videoElement.addEventListener('ended', async () => {
							await this.onVideoEnded();
							const limitExceeded = await this.checkDailyWatchLimit();
							if (limitExceeded) {
								window.location.href = '/errors/video-watch-limit-reached';
							}
						});

						videoElement.addEventListener('play', async () => {
							const limitExceeded = await this.checkDailyWatchLimit();
							if (limitExceeded) {
								window.location.href = '/errors/video-watch-limit-reached';
							}
						});
					}
                },
				created(){
					const self = this;
					setTimeout(() => {
						window.addEventListener('message', function(event) {
							setTimeout(() => {
								self.showModal = false;
							}, 1800);
						});
					}, 1000);
				},
                methods: {
					
					async onVideoEnded() {
						const videoId = {{ $video->id }};
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
