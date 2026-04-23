<?php

namespace App\Http\Controllers;

use App\Http\Requests\VideoEditRequest;
use App\Http\Requests\VideoUploadRequest;
use App\Http\Resources\VideoResource;
use App\Jobs\ConvertVideoForStreaming;
use App\Jobs\CreateThumbnailFromVideo;
use App\Models\Channel;
use App\Models\Comment;
use App\Models\Dislike;
use App\Models\Like;
use App\Models\Subscribe;
use App\Models\Video;
use App\Models\WatchHistory;
use App\Services\ImageService;
use App\Services\VideoService;
use App\Traits\ManageFiles;
use http\Client\Curl\User;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
class VideoController extends Controller
{
    use ManageFiles;

    public function videoUploadPage()
    {
        return view('pages.upload-video');
    }

    public function videoUpload($channel, VideoUploadRequest $request)
    {
		$imagePath = null;
		if ($request->hasFile('thumbnail_image')) {

			$imagePath = app(ImageService::class)
			->uploadFile(
				$request->file('thumbnail_image'),
				'uploads/thumbnail_images'
			);
		}

		$videoPath = app(VideoService::class)->uploadVideoToStorage($request->file('video'));

		$video = app(VideoService::class)
				->saveVideoToDatabase($channel, $videoPath, [
						'title' => $request->title ?? Str::beforeLast($videoPath->getClientOriginalName(), '.'),
						'description' => $request->description ?? null,
						'visibility' => "public",
						'image' => $imagePath,
				]);

		if(!$imagePath){
			app(VideoService::class)->generateThumbnail($video);
		}


		return response()->json([
            'success' => true,
            'redirect' => route('channel', ['channel' => $video->channel->slug]),
        ], 201);
    }


    public function videoEditPage($channel, $video)
    {
        $channel = Channel::where('uid', $channel)->first();
        $video = Video::where('uid',$video)->first();

        return view('pages.edit-video',['channel'=>$channel, 'video'=>$video]);
    }

    public function videoEdit($channel, $video,Request $request)
    {
        $video = Video::where('uid',$video)->first();

        $video->update([
            'title' => $request->title,
            'description' => $request->description,
            'visibility' => $request->visibility
        ]);

        return redirect()->route('home',[
            "success"=>"Başarıyla video yüklenmiştir!",
        ]);
    }

    public function channel($channel)
    {
        $channel = Channel::with([
                'videos' => function ($q) {
                    $q->withCount([
                        'watchHistories as user_watch_count' => function ($q) {
                            $q->where('user_id', auth()->id());
                        }
                    ])
                    ->withExists([
                        'watchHistories as watched_by_auth_user' => function ($q) {
                            $q->where('user_id', auth()->id());
                        }
                    ])
                    ->orderBy('user_watch_count', 'asc')
                    ->orderBy('views', 'desc');
                }
            ])->where('slug', $channel)
            ->first();


        return view('pages.channel',[
            'channel'=>$channel,
            'totalVideoCount'=>count($channel->videos),
            'totalWatchCount'=> $channel->videos->sum(function($video){
                return $video->watchHistories()->count();
            }),
        ]);
    }

    public function show($video)
    {
		if(auth()->check()){
			$daily_watch_count = WatchHistory::where('user_id', auth()->id())
				->whereDate('watched_at', now()->toDateString())
				->count();
			if ($daily_watch_count >= config('app.daily_video_watch_limit')) {
				return redirect()->route('errors.video-watch-limit-reached');
			}
		}
        $video = Video::where('uid',$video)->with('channel')->first();
		$video->update([
			'views' => $video->views + 1,
		]);

        // Video önerilerini VideoService üzerinden al
        $recommendationVideos = app(VideoService::class)->getRecommendedVideos(
            $video,
            auth()->id(),
            10
        );

        return view('pages.video-page',[
			"currentVideo" => $video,
			"recommendationVideos" => $recommendationVideos
		]);
    }

    public function subscribe($channel_id)
    {
        $channel = Channel::find($channel_id);

        if (!$channel) {
            return response()->json(['success' => false, 'message' => 'Kanal bulunamadı.']);
        }

        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Giriş yapmalısınız.']);
        }

        $subscribe = Subscribe::where([
            'channel_id' => $channel_id,
            'user_id' => $user->id
        ])->first();

        if ($subscribe) {
            $subscribe->delete();
            $count = Subscribe::where('channel_id', $channel_id)->count();

            return response()->json([
                'success' => true,
                'message' => 'Abonelikten çıkıldı!',
                'subscribed' => false,
                'count' => $count
            ]);
        }

        Subscribe::create([
            'channel_id' => $channel_id,
            'user_id' => $user->id
        ]);

        $count = Subscribe::where('channel_id', $channel_id)->count();

        return response()->json([
            'success' => true,
            'message' => 'Abone olundu!',
            'subscribed' => true,
            'count' => $count
        ]);
    }

    public function checkSubscription($channel_id)
    {
        $user = Auth::user();
        $subscribed = false;
        $count = Subscribe::where('channel_id', $channel_id)->count();

        if ($user) {
            $subscribed = Subscribe::where([
                'channel_id' => $channel_id,
                'user_id' => $user->id
            ])->exists();
        }

        return response()->json([
            'subscribed' => $subscribed,
            'count' => $count
        ]);
    }

    public function checkLikeDislike($video_id)
    {
        $like = Like::where([
            'video_id'=>$video_id,
            'user_id'=>Auth::user()->id
        ])->exists();

        if ($like)
        {
            return response()->json([
                'like' => true,
            ]);
        }

        $dislike = Dislike::where([
            'video_id'=>$video_id,
            'user_id'=>Auth::user()->id
        ])->exists();

        if ($dislike)
        {
            return response()->json([
                'dislike' => true,
            ]);
        }

        return response()->json([
            'like' => false,
            'dislike' => false,
        ]);
    }

    public function like($video_id)
    {
        $user = Auth::user();

        Dislike::where([
            'video_id' => $video_id,
            'user_id' => $user->id
        ])->delete();

        $existing = Like::where([
            'video_id' => $video_id,
            'user_id' => $user->id
        ])->first();

        if ($existing) {
            $existing->delete();
            $liked = false;
        } else {
            Like::create([
                'video_id' => $video_id,
                'user_id' => $user->id,
            ]);
            $liked = true;
        }

        $video = Video::with(['likes', 'dislikes'])->find($video_id);

        return response()->json([
            'liked' => $liked,
            'likes' => $video->likes->count(),
            'dislikes' => $video->dislikes->count(),
        ]);
    }

    public function dislike($video_id)
    {
        $user = Auth::user();

        Like::where([
            'video_id' => $video_id,
            'user_id' => $user->id
        ])->delete();

        $existing = Dislike::where([
            'video_id' => $video_id,
            'user_id' => $user->id
        ])->first();

        if ($existing) {
            $existing->delete();
            $disliked = false;
        } else {
            Dislike::create([
                'video_id' => $video_id,
                'user_id' => $user->id,
            ]);
            $disliked = true;
        }

        $video = Video::with(['likes', 'dislikes'])->find($video_id);

        return response()->json([
            'disliked' => $disliked,
            'likes' => $video->likes->count(),
            'dislikes' => $video->dislikes->count(),
        ]);
    }

    public function countLikeDislike($video_id)
    {
        $video = Video::with(['likes','dislikes'])->where('id',$video_id)->first();

        return response()->json([
            'likes'=>count($video->likes) ?? 0,
            'dislikes'=>count($video->dislikes) ?? 0,
        ]);
    }
	
	/**
	 * Video İzleme Aktivitesi Kaydı (Watch History Tracker)
	 *
	 * Bu method, kullanıcının bir videoyu gerçekten izleyip izlemediğini doğrular;
	 * video kısa ise 1 kez, uzunsa izlenen süreye orantılı olarak 2x, 3x şeklinde
	 * watchHistory'e kaydeden akıllı bir izleme takip sistemidir.
	 *
	 * AMAÇ:
	 * Yalnızca video sayısına (count) değil, izleme süresine de dayalı bir
	 * günlük limit sistemi oluşturmak. Böylece kısa ve uzun videolar arasında
	 * adil bir limit dengesi kurulur.
	 *
	 * ÇALIŞMA MANTIĞI:
	 * - Frontend (video-page.blade.php), kullanıcı video izlerken bu methodu her 5 dakikada bir çağırır.
	 * - Method her çağrıldığında şunu kontrol eder:
	 *   "Bu video için son watchHistory kaydından itibaren 30 dakika geçti mi?"
	 *   → Geçmediyse: kayıt eklenmez.
	 *   → Geçtiyse: watchHistory'e yeni kayıt eklenir → watchLimit sayacı +1 artar.
	 *
	 * PAUSE / SEKME KAPAMA DURUMU:
	 * - Kullanıcı videoyu durdurursa (pause): Frontend'deki 5 dakikalık interval
	 *   temizlenir (clearInterval), method artık çağrılmaz.
	 * - Kullanıcı videoyu tekrar başlatırsa (play): interval yeniden kurulur
	 *   ve method tekrar çağrılmaya başlar.
	 * - Bu sayede kullanıcı videoyu açık bırakıp başka bir şeyle ilgilenirse
	 *   ya da videoyu durdurursa, o süre izleme süresi olarak sayılmaz.
	 * - Yani bu sistem; sadece videonun oynatıldığı aktif süreyi ölçer.
	 *
	 * ÖRNEK SENARYO (40 dakikalık bir video):
	 *
	 *  Dakika  | Method Çağrısı | 30dk Geçti mi? | watchHistory Kaydı
	 *  --------|----------------|----------------|--------------------
	 *   5. dk  | ✅ İlk çağrı   | —  (ilk kayıt) | ✅ Kayıt eklenir (+1)
	 *  10. dk  | ✅             | ❌ Hayır        | ⏭ Atlanır
	 *  15. dk  | ✅             | ❌ Hayır        | ⏭ Atlanır
	 *  20. dk  | ✅             | ❌ Hayır        | ⏭ Atlanır
	 *  25. dk  | ✅             | ❌ Hayır        | ⏭ Atlanır
	 *  30. dk  | ✅             | ❌ Hayır        | ⏭ Atlanır
	 *  35. dk  | ✅             | ✅ Evet (30dk+) | ✅ Kayıt eklenir (+1)
	 *  40. dk  | ✅             | ❌ Hayır        | ⏭ Atlanır
	 *
	 *  → Toplam: 40 dakikalık video için watchHistory'e 2 kayıt, watchLimit'e +2.
	 *
	 * KISACA:
	 * - 5 dakikalık video izlenir  → watchLimit: +1
	 * - 40 dakikalık video izlenir → watchLimit: +2
	 * - 65 dakikalık video izlenir → watchLimit: +3
	 * - Bu sayede limit; video adedine değil, izlenen toplam süreye göre işler.
	 *
	 * NOT:
	 * 5 dakikalık ilk tetikleyici eşiği, çok kısa (5 dk altı) videoların
	 * gereksiz yere kaydedilmesini önlemek için seçilmiştir.
	 * Gerekirse bu eşik 2-3 dakikaya düşürülebilir.
	 */
	public function storeVideoWatchingActivity($videoId)
	{
		if (!Auth::check()) {
			return response()->json(['success' => false, 'message' => 'Unauthorized.'], 401);
		}

		$userId = Auth::id();

		// Bu video için kullanıcının en son watchHistory kaydını bul
		$lastWatch = WatchHistory::where('user_id', $userId)
			->where('video_id', $videoId)
			->latest('watched_at')
			->first();

		$shouldRecord = false;

		if (!$lastWatch) {
			// Hiç kayıt yoksa bu ilk 5dk tetiklemesi, direkt kaydet
			$shouldRecord = true;
		} else {
			// Son kayıttan itibaren 30 dakika geçti mi?
			$minutesSinceLastWatch = $lastWatch->watched_at->diffInMinutes(now());
			if ($minutesSinceLastWatch >= 30) {
				$shouldRecord = true;
			}
		}

		if ($shouldRecord) {
			WatchHistory::create([
				'user_id'    => $userId,
				'video_id'   => $videoId,
				'watched_at' => now(),
			]);
		}

		// Kullanıcının günlük watchHistory kayıt sayısını kontrol et
		$dailyWatchCount = WatchHistory::where('user_id', $userId)
			->whereDate('watched_at', today())
			->count();

		$dailyLimit = 10; // örnek limit, config'den de alınabilir

		return response()->json([
			'success'          => true,
			'recorded'         => $shouldRecord,
			'daily_watch_count' => $dailyWatchCount,
			'limit_exceeded'   => $dailyWatchCount >= $dailyLimit,
			'message'          => $shouldRecord
				? 'İzleme aktivitesi kaydedildi.'
				: 'Henüz kayıt için 30 dakika geçmedi.',
		]);
	}

	public function checkDailyWatchLimit()
	{
		if (Auth::check()) {
			$daily_watch_count = WatchHistory::where('user_id', Auth::id())
				->whereDate('watched_at', now()->toDateString())
				->count();

			return response()->json([
				'daily_watch_count' => $daily_watch_count,
				'limit_exceeded' => $daily_watch_count > config('app.daily_video_watch_limit'),
			]);
		}

		return response()->json([
			'daily_watch_count' => 0,
			'limit_exceeded' => false,
		]);
	}

    public function videoWatchLimitReached(){
        $todayWatchedVideoIds = WatchHistory::select('video_id')
                ->where('user_id', auth()->id())
				->whereDate('watched_at', now()->toDateString())
				->get()->pluck('video_id');
        $todayWatchedVideos = Video::whereIn('id', $todayWatchedVideoIds)->get();


        return view('pages.errors.video-watch-limit-reached', [
            "todayWatchedVideos" => $todayWatchedVideos
        ]);
    }
}
