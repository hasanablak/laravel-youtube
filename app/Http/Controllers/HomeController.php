<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Video;
use App\Models\WatchHistory;
use App\Services\DailyWatchLimitService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
	/** "Yeni video!" etiketi için bir videonun kaç gün yeni sayılacağı */
	private const NEW_VIDEO_DAYS = 7;

	public function __construct(
		private readonly DailyWatchLimitService $dailyWatchLimitService
	) {
	}

	/**
	 * Kanal ağırlıklı ana sayfa: her kanal büyük bir kapak resmiyle gösterilir.
	 *
	 * Kanal listesi (isim, video sayısı, son videonun kapağı) herkes için
	 * aynı olduğundan 10 dk cache'lenir. Kullanıcıya özel bilgiler (kanal başına
	 * izlediği video sayısı, izlemediği yeni videolar) her istekte birkaç toplu
	 * sorguyla hesaplanır.
	 */
	public function index()
	{
		$channels = $this->getChannelShelf();
		$userId = auth()->id();

		$watchedPerChannel = collect();
		$newPerChannel = $this->countNewVideosPerChannel($userId);
		$limitStatus = null;

		if ($userId) {
			$watchedPerChannel = WatchHistory::query()
				->join('videos', 'watch_histories.video_id', '=', 'videos.id')
				->where('watch_histories.user_id', $userId)
				->groupBy('videos.channel_id')
				->selectRaw('videos.channel_id, COUNT(DISTINCT watch_histories.video_id) as watched')
				->pluck('watched', 'videos.channel_id');

			$limitStatus = $this->dailyWatchLimitService->getStatus($userId);
		}

		$channels = $channels
			->map(function (array $channel) use ($watchedPerChannel, $newPerChannel) {
				$watched = min((int) ($watchedPerChannel[$channel['id']] ?? 0), $channel['video_count']);
				$channel['watched'] = $watched;
				$channel['new_count'] = (int) ($newPerChannel[$channel['id']] ?? 0);
				return $channel;
			})
			// Önce yeni videosu olan kanallar, sonra çocuğun en çok izlediği kanallar gelsin
			->sortBy([
				fn ($a, $b) => ($b['new_count'] > 0) <=> ($a['new_count'] > 0),
				fn ($a, $b) => $b['watched'] <=> $a['watched'],
			])
			->values();

		return view('pages.index', [
			'channels' => $channels,
			'limitStatus' => $limitStatus,
		]);
	}

	/**
	 * Herkese açık, kapak resmi olan videoları bulunan kanallar.
	 *
	 * @return Collection<int, array{id:int,name:string,slug:string,video_count:int,thumbs:array}>
	 */
	private function getChannelShelf(): Collection
	{
		return Cache::remember('home_channel_shelf_v2', 600, function () {
			$publicVideos = fn ($query) => $query
				->where('visibility', 'public')
				->whereNotNull('thumbnail_image');

			return Channel::query()
				->whereHas('videos', $publicVideos)
				->whereNotIn('id', config('app.black_list_channel_ids'))
				->withCount(['videos as video_count' => $publicVideos])
				->inRandomOrder()
				->get()
				->map(function (Channel $channel) use ($publicVideos) {
					$thumbs = $channel->videos()
						->tap($publicVideos)
						->latest()
						->limit(1)
						->get()
						->map(fn (Video $video) => [
							'title' => $video->title,
							'url' => $video->thumbnail_url,
						])
						->all();

					return [
						'id' => $channel->id,
						'name' => $channel->name,
						'slug' => $channel->slug,
						'video_count' => $channel->video_count,
						'thumbs' => $thumbs,
					];
				})
				->values();
		});
	}

	/**
	 * Son NEW_VIDEO_DAYS gün içinde eklenmiş ve kullanıcının henüz izlemediği
	 * videoların kanal başına sayısı.
	 */
	private function countNewVideosPerChannel(?int $userId): Collection
	{
		return Video::query()
			->where('visibility', 'public')
			->whereNotNull('thumbnail_image')
			->where('created_at', '>=', now()->subDays(self::NEW_VIDEO_DAYS))
			->when($userId, fn ($query) => $query->whereDoesntHave(
				'watchHistories',
				fn ($q) => $q->where('user_id', $userId)
			))
			->groupBy('channel_id')
			->selectRaw('channel_id, COUNT(*) as total')
			->pluck('total', 'channel_id');
	}

	public function registerPage()
	{
		if (auth()->check()) {
			return redirect()->route('home');
		}
		return view('pages.register');
	}
}
