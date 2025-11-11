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
					  ->orderBy('created_at', 'desc')
					  ->limit(1);
			}])
			->inRandomOrder()
			->get()
			->map(function($channel) {
				$channel->latest_thumbnail = $channel->videos->first()?->thumbnail_image;
				return $channel;
			});
		});

        $videos = Video::query()
		->whereNotIn("id", [config('app.black_list_channel_ids')])
		->orderBy('views','asc')
		->inRandomOrder()
		->limit(50)
		->get();

        return view('pages.index',[
			"videos" => $videos,
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

