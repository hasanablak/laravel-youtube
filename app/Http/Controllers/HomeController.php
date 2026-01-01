<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function index()
    {
		$channels = Cache::remember('channels_with_latest_video', 600, function () {
            return Channel::whereHas('videos', function($query) {
                $query->where('visibility', 'public')
                      ->whereNotNull('thumbnail_image');
            })
            ->whereNotIn("id", config('app.black_list_channel_ids'))
            ->with(['videos' => function($query) {
                $query->where('visibility', 'public')
                      ->whereNotNull('thumbnail_image')
                      ->inRandomOrder()
                      ->limit(1);
            }])
            ->inRandomOrder()
            ->get()
            ->map(function($channel) {
                $channel->latest_thumbnail = $channel->videos->first()?->thumbnail_image;
                return $channel;
            });
        });

        // Her kanaldan en fazla 2 video al, tümünü izlenmeye göre sırala ve 50 ile sınırla — 5 dk cache
        $videos = Cache::remember('home_videos_per_channel_2', 300, function () use ($channels) {
            $videosCollection = collect();

            foreach ($channels->pluck('id') as $channelId) {
                $perChannel = Video::query()
                    ->where('channel_id', $channelId)
                    ->where('visibility', 'public')
                    ->whereNotNull('thumbnail_image')
                    ->orderBy('views', 'asc')
                    ->limit(2)
                    ->get();

				$videosCollection = $videosCollection->concat($perChannel);
			}

			return $videosCollection
				->sortBy('views')   // azdan çoğa
				->values()
				->take(50);
		});

		return view('pages.index',[
			"videos" => $videos
				->shuffle(),
			"channels" => $channels
		]);
	}

	public function registerPage()
    {
        if (auth()->check()) {
            return redirect()->route('home');
        }
        return view('pages.register');
    }
}

