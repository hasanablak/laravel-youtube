@extends('layouts.app', [
		'title' => $currentVideo->title,
		'hideTopBar' => true,
	])

@push('header')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700;800&display=swap" rel="stylesheet">
@endpush

@php
    // Kanalın resmi: kanalın en son videosunun kapağı
    $channelAvatar = $currentVideo->channel->videos()->latest()->first()?->thumbnail_url;
@endphp

@section('content')
    <div id="app" class="min-h-screen bg-candy-blush font-nunito text-candy-ink">
        <div class="mx-auto flex max-w-[1600px] flex-col gap-5 px-3 pb-16 pt-3 sm:px-4 lg:flex-row" data-channel-id="{{ $currentVideo->channel->id }}">

            {{-- SOL BÖLÜM --}}
            <div class="min-w-0 flex-1 space-y-4">

                {{-- Video Player --}}
                <div class="overflow-hidden rounded-3xl border-2 border-white bg-black shadow-soft-lg">
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

                {{-- Kanal + başlık + etkileşim --}}
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <a href="{{ route('channel', $currentVideo->channel->slug) }}"
                       data-speech="true" data-speech-text="{{ $currentVideo->channel->name }}"
                       class="group flex min-w-0 items-center gap-3">
                        <span class="relative h-14 w-14 flex-none overflow-hidden rounded-full border-2 border-white bg-pastel-pink shadow-soft transition group-hover:shadow-soft-lg">
                            @if($channelAvatar)
                                <img src="{{ $channelAvatar }}" alt="" onerror="this.remove()" class="h-full w-full object-cover">
                            @endif
                        </span>
                        <div class="min-w-0">
                            <h1 class="line-clamp-2 text-base font-bold leading-snug sm:text-lg">{{ $currentVideo->title }}</h1>
                            <span class="block text-sm font-normal text-candy-muted">{{ $currentVideo->channel->name }}</span>
                        </div>
                    </a>

                    <div class="flex items-center gap-2">
                        {{-- Abone Ol --}}
                        <button
                            :class="[
                                'relative flex h-11 items-center gap-2 rounded-full px-4 text-sm font-bold shadow-soft transition-all duration-300',
                                isSubscribed ? 'bg-white text-candy-muted' : 'bg-candy-rose text-white hover:shadow-soft-lg',
                                animating ? 'scale-105' : ''
                            ]"
                            @click="toggleSubscribe({{ $currentVideo->channel->id }})"
                        >
                            <i :class="isSubscribed ? 'fas fa-check' : 'fas fa-bell'"></i>
                            <span v-if="!isSubscribed">Abone Ol</span>
                            <span v-else>Abone Olundu</span>
                            <span v-if="animating" class="absolute inset-0 rounded-full bg-white/40 animate-ping"></span>
                        </button>

                        {{-- Beğen (kalp) --}}
                        <button
                            @click="toggleLike({{ $currentVideo->id }})"
                            aria-label="Beğen"
                            :class="[
                                'flex h-11 items-center gap-2 rounded-full bg-white px-4 text-sm font-bold shadow-soft transition-all duration-200 hover:shadow-soft-lg',
                                liked ? 'text-candy-rose scale-110' : 'text-candy-muted',
                                likeAnimating ? 'animate-bounce' : ''
                            ]"
                        >
                            <i :class="liked ? 'fa-solid fa-heart' : 'fa-regular fa-heart'"></i>
                            <span>@{{ likeCount }}</span>
                        </button>

                        {{-- Beğenme --}}
                        <button
                            @click="toggleDislike({{ $currentVideo->id }})"
                            aria-label="Beğenme"
                            :class="[
                                'flex h-11 items-center gap-2 rounded-full bg-white px-4 text-sm font-bold shadow-soft transition-all duration-200 hover:shadow-soft-lg',
                                disliked ? 'text-candy-ink scale-110' : 'text-candy-muted',
                                dislikeAnimating ? 'animate-bounce' : ''
                            ]"
                        >
                            <i class="fa-solid fa-thumbs-down"></i>
                            <span>@{{ dislikeCount }}</span>
                        </button>
                    </div>
                </div>

                {{-- Video Bilgisi (ebeveyn için) --}}
                <div class="rounded-3xl bg-white p-4 text-sm shadow-soft">
                    <div class="mb-1 flex items-center gap-2 text-candy-muted">
                        <span>{{ number_format($currentVideo->views) }} görüntüleme</span>
                        <span>•</span>
                        <span>{{ $currentVideo->created_at->diffForHumans() }}</span>
                    </div>
                    @if($currentVideo->description)
                        <p class="whitespace-pre-line leading-relaxed">{{ $currentVideo->description }}</p>
                    @endif
                </div>

                {{-- 💬 YORUMLAR --}}
                <div class="rounded-3xl bg-white p-4 shadow-soft">
                    <h3 class="mb-4 text-base font-bold">
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
                            class="flex-1 bg-transparent border-b border-pastel-pink focus:border-candy-rose text-gray-800 pb-2 outline-none placeholder-gray-500"
                        >
                        <button
                            @click="postComment({{ $currentVideo->id }})"
                            class="flex items-center gap-2 rounded-full bg-candy-rose px-5 py-2 text-sm font-bold text-white shadow-soft transition duration-200 hover:shadow-soft-lg active:scale-95"
                        >
                            <i class="fas fa-paper-plane"></i>
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
                                <div v-if="comment.replies && comment.replies.length" class="ml-12 mt-3 space-y-4 border-l border-pastel-pink pl-4">
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
                                            class="w-full bg-transparent border-b border-pastel-pink focus:border-candy-rose text-sm pb-1 outline-none transition"
                                        >
                                        <button
                                            @click="replyToComment({{ $currentVideo->id }}, comment.id, comment.replyText)"
                                            class="absolute right-0 top-1/2 -translate-y-1/2 text-candy-rose text-xs font-bold hover:underline transition"
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
            <aside aria-label="Önerilen videolar" class="grid w-full content-start grid-cols-2 gap-3 sm:grid-cols-3 lg:w-[340px] lg:grid-cols-1 xl:w-[400px]">
                @foreach($recommendationVideos as $video)
					<x-kid-video-card :video="$video" />
                @endforeach
            </aside>
        </div>
    </div>
	<Modal size="lg" v-if="showGameModal" :hide-header="true" :allow-back-drop-click="false" :hide-footer="true">
		<template v-slot:header> .. </template>
		<template v-slot:body> 
			{{-- Şimdilik eşleştirme oyunu (want-to). İngilizce söyleme oyununa dönmek için:
			     src="/games/hayvan-ingilizce/index.html?animals=2&colors=2&objects=1&embed=1" allow="microphone; autoplay" --}}
			<iframe
				src="/games/want-to/index.html"
				allow="autoplay"
				style="width: min(1200px, 92vw); height: min(640px, 80vh); border: 0; border-radius: 24px; display: block;"
			></iframe>
		</template>
	</Modal>

	<Modal
		size="medium"
		v-if="showLastWatchModal"
		:hide-header="true"
		:allow-back-drop-click="false"
		:hide-footer="false"
		:hide-cancel-button="true"
		modal-content-class="md:w-[92%] max-w-lg"
		@close="closeLastWatchModal"
	>
		<template v-slot:body>
			<div class="last-watch-warning text-center py-2 px-1">
				<div class="last-watch-video-wrap mb-4 overflow-hidden rounded-2xl border-4 border-amber-300 shadow-lg">
					
                     <video 
                     ref="lastWatchVideo"
                     autoplay muted loop playsinline class="w-full aspect-video object-cover bg-amber-50">
                    <source src="{{ asset('assets/videos/son-1-hak-0/video-1.mp4') }}" type="video/mp4">
                </video>
				</div>
                <audio ref="lastWatchAudio" autoplay preload="auto">
					<source src="{{ asset('assets/videos/son-1-hak-0/warning-3.mp3') }}" type="audio/mpeg">
				</audio>
				<h2 class="text-2xl font-extrabold text-amber-700 mb-2">Bu son izleme hakkın!</h2>
				<p class="text-base text-amber-900/80 leading-relaxed">
					Bugün sadece <strong>1 video</strong> daha izleyebilirsin.<br>
					Seçimini iyi yap ve keyfini çıkar!
				</p>
			</div>
		</template>
		<template v-slot:footer="{ closeModalHandle }">
			<button
				type="button"
                style="background: linear-gradient(to right, yellow, deeppink);"
				class="w-full rounded-full bg-gradient-to-r px-6 py-3 text-lg font-bold text-white shadow-lg hover:scale-[1.02] transition-transform"
				@click="closeModalHandle"
			>
				Tamam, anladım!
			</button>
		</template>
	</Modal>
    @push('footer')
        <style>
            .last-watch-warning {
                font-family: 'Comic Sans MS', 'Chalkboard SE', cursive, sans-serif;
            }
        </style>
        <script>
            vueMixinFunctions.push(() => ({
			components: {
				Modal
			},
                data() {
                    return {
						showGameModal: false,
						showLastWatchModal: false,
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
				watch: {
					showLastWatchModal(value) {
						if (value) {
							this.$nextTick(() => {
								this.playLastWatchWarningMedia();
							});
						}
					},
				},
                mounted() {
					

                    const channelId = {{ $currentVideo->channel->id }};
                    const videoId = {{ $currentVideo->id }};

                    // axios.get(`/api/check-subscription/${channelId}`).then(res => {
                    //     this.isSubscribed = res.data.subscribed;
                    //     this.subscriberCount = res.data.count;
                    // }).catch(()=>{});

                    axios.get(`/api/count/${videoId}`).then(res => {
                        this.likeCount = res.data.likes;
                        this.dislikeCount = res.data.dislikes;
                    }).catch(()=>{});

                    // axios.get(`/api/check-like/${videoId}`).then(res => {
                    //     this.liked = !!res.data.like;
                    //     this.disliked = !!res.data.dislike;
                    // }).catch(()=>{});

                    this.fetchComments(videoId);
					this.checkLastWatchWarning();
					
					// Video bitme eventi
					const videoElement = document.getElementById('video');
					if (videoElement) {
						// videoElement.addEventListener('ended', async () => {
						// 	await this.storeVideoWatchingActivity();
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
								this.openGameModal();
								this.storeVideoWatchingActivity();
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
							// Sadece kendi oyunlarımızdan gelen bitiş mesajları: { status: 'success' | 'skipped' }
							if (event.origin !== window.location.origin) return;
							const status = event.data && event.data.status;
							if (status !== 'success' && status !== 'skipped') return;

							// Oyun kutlama sesini bitirip mesaj gönderiyor; atlandıysa hiç bekletme.
							setTimeout(() => {
								self.showGameModal = false;
								// Modal kapandıktan sonra videoyu kaldığı yerden başlat
								const videoElement = document.getElementById('video');
								if (videoElement && videoElement.paused) {
									// Kullanıcı hareketi olmadan tam ekran reddedilebilir; video yine oynasın
									videoElement.requestFullscreen?.().catch(() => {});
									videoElement.play().catch(() => {});
								}
							}, status === 'success' ? 1200 : 300);
						});
					}, 1000);

				},
                methods: {
					
					openGameModal() {
						// Videoyu duraklat
						const videoElement = document.getElementById('video');
						if (videoElement && !videoElement.paused) {
							videoElement.pause();
							if (document.fullscreenElement) document.exitFullscreen().catch(() => {});
						}
						
						// Modal'ı aç
						this.showGameModal = true;
					},

					async checkLastWatchWarning() {
						try {
							const data = await window.refreshWatchLimit?.();
							if (data && data.remaining === 1 && !data.limit_exceeded) {
								this.showLastWatchModal = true;
							}
						} catch (error) {
							console.error('Son izleme hakkı uyarısı alınamadı:', error);
						}
					},

					playLastWatchWarningMedia() {
						const video = this.$refs.lastWatchVideo;
						const audio = this.$refs.lastWatchAudio;

						if (video) {
							video.currentTime = 0;
							video.play().catch(() => {});
						}

						if (audio) {
							audio.currentTime = 0;
							audio.play().catch(() => {});
						}
					},

					closeLastWatchModal() {
						this.stopLastWatchWarningMedia();
						this.showLastWatchModal = false;
					},

					stopLastWatchWarningMedia() {
						const video = this.$refs.lastWatchVideo;
						const audio = this.$refs.lastWatchAudio;

						if (video) {
							video.pause();
							video.currentTime = 0;
						}

						if (audio) {
							audio.pause();
							audio.currentTime = 0;
						}
					},
					
					async storeVideoWatchingActivity() {
						const videoId = {{ $currentVideo->id }};
						const result = await axios.post(`/api/videos/${videoId}/store-video-watching-activity`);
                        window.dispatchWatchLimitRefresh?.();
						return result.data;
					},

					async checkDailyWatchLimit() {
						try {
                            const { data } = await axios.get('/api/check-daily-watch-limit', {
                                params: { video_id: {{ $currentVideo->id }} },
                            });
                            window.refreshWatchLimit?.();
							// Kota dolsa bile bu video bugün zaten sayıldıysa izlemeye devam edilir.
							return !(data?.can_watch ?? !data?.limit_exceeded);
						} catch (error) {
							console.error('Günlük izleme limiti kontrol hatası:', error);
							return true;
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
