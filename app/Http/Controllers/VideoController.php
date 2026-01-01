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
                    $q->withExists([
                        'watchHistories as watched_by_auth_user' => function ($q) {
                            $q->where('user_id', auth()->id());
                        }
                    ]);
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

    public function video($video)
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

        

        $videos = Video::orderBy('views', 'asc')
        ->inRandomOrder()
        ->limit(10)
        ->whereNot('id', $video->id)
        ->whereNotIn("channel_id", config('app.black_list_channel_ids'))
        ->get();


		

        return view('pages.video-page',["video"=>$video,"videos"=>$videos]);
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

	public function onVideoEnd($videoId)
	{

		if (Auth::check()) {
			// Aynı saat içinde aynı video için kayıt var mı kontrol et
			$existingWatch = WatchHistory::where('user_id', Auth::id())
				->where('video_id', $videoId)
				->whereRaw('DATE_FORMAT(watched_at, "%Y-%m-%d %H") = ?', [now()->format('Y-m-d H')])
				->first();

			// Eğer kayıt yoksa oluştur
			if (!$existingWatch) {
				WatchHistory::create([
					'user_id' => Auth::id(),
					'video_id' => $videoId,
					'watched_at' => now(),
				]);
			}
		}

		//check if user has exceeded daily limit



		return response()->json([
			'success' => true,
			'message' => 'Video bitiş işlemi kaydedildi.'
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
}
