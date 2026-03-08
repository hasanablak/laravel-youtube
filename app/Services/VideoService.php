<?php

	namespace App\Services;

use App\Jobs\ConvertVideoForStreaming;
use App\Jobs\CreateThumbnailFromVideo;
use App\Jobs\CreateGifFromVideo;
use App\Models\Channel;
use App\Models\Video;
use App\Models\WatchHistory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

	class VideoService
	{
		public function uploadVideoToStorage($video){;
			$path = $video->store('public/videos-temp');
			return $path;
		}

		public function slugflyFileName(string $file): string{
			
			$fileNameWithoutExtension = pathinfo($file, PATHINFO_FILENAME);
			$extension = pathinfo($file, PATHINFO_EXTENSION);
			$fileNameSlugWithoutExtension = Str::slug($fileNameWithoutExtension);

			$newSlugFileNameWithExtension = $fileNameSlugWithoutExtension . '.' . $extension;
			$dir = dirname($file);
			$dir = $dir === '.' ? '' : $dir;

			$newRelativePath = $dir === '' 
				? $newSlugFileNameWithExtension 
				: $dir . '/' . $newSlugFileNameWithExtension;
			
			Storage::disk('manuel-videos')->move($file, $newRelativePath);

			return $newRelativePath;
		}

	    public function saveVideoToDatabase($channel, $path, $settings = [], $video_orginal_url = null): Video
		{
			$channel = Channel::where('uid', $channel)->first();


			$filename = basename($path);

			
			$video = $channel->videos()
				->create([
					'title' => $settings['title'],
					'description' => $settings['description'] ?? null,
					'uid' => uniqid(true),
					'visibility' => $settings['visibility'] ?? "public",
					'video_orginal_path' => $path,
					'image' => $settings['image'] ?? null,
					'video_orginal_url' => $video_orginal_url ?? null,
					'video_orginal_name' =>  $settings['video_orginal_name'],
					'video_slug_url' => $settings['video_slug_url'] ?? null,
					'video_slug_path' => $settings['video_slug_path'] ?? null,
					'file_hash' => $settings['file_hash'] ?? null,
				]);



			if (config('app.when_upload_video_add_queue_for_streaming')) {
				$video->update([
					'is_converting_for_streaming' => true,
				]);
				ConvertVideoForStreaming::dispatch($video);
			} else {
				$video->update([
					'is_converting_for_streaming' => false,
				]);
			}

			return $video;
		}

		public function generateThumbnail(Video $video, $disk = 'videos-temp', $video_path = null){
			CreateThumbnailFromVideo::dispatch($video, $disk, $video_path);
		}

		public function generateGif(Video $video, $disk = 'videos-temp', $video_path = null){
			CreateGifFromVideo::dispatch($video, $disk, $video_path);
		}

		/**
		 * Kullanıcıya özel video önerileri döndürür
		 * 
		 * @param Video $currentVideo Şu anda izlenen video
		 * @param int|null $userId Kullanıcı ID (giriş yapmış ise)
		 * @param int $limit Toplam öneri sayısı (varsayılan: 10)
		 * @return \Illuminate\Support\Collection
		 */
		public function getRecommendedVideos(Video $currentVideo, ?int $userId = null, int $limit = 10)
		{
			$recommendationVideos = collect();
			$blacklistChannelIds = config('app.black_list_channel_ids', []);

			// 1. Aynı kanaldaki, hiç izlemedikleri videolar (1 adet)
			$sameChannelLimit = min(1, $limit);
			$sameChannelVideos = $this->getSameChannelRecommendations(
				$currentVideo,
				$userId,
				$sameChannelLimit,
				$blacklistChannelIds
			);
			$recommendationVideos = $recommendationVideos->merge($sameChannelVideos);

			// 2. Kullanıcının daha önce izlediği kanalları bul
			$watchedChannelIds = [];
			if ($userId) {
				$watchedChannelIds = WatchHistory::where('user_id', $userId)
					->join('videos', 'watch_histories.video_id', '=', 'videos.id')
					->distinct()
					->pluck('videos.channel_id')
					->toArray();
			}

			// 3. Hiç izlemediği ve daha önce izlediği kanalların dışındaki videolar (5 adet)
			$newChannelLimit = min(5, $limit - $recommendationVideos->count());
			if ($newChannelLimit > 0) {
				$newChannelVideos = $this->getNewChannelRecommendations(
					$currentVideo,
					$userId,
					$watchedChannelIds,
					$blacklistChannelIds,
					$recommendationVideos->pluck('id')->toArray(),
					$newChannelLimit
				);
				$recommendationVideos = $recommendationVideos->merge($newChannelVideos);
			}

			// 4. Daha önce izledikleri ama az izledikleri videolar (3 adet)
			$lessWatchedLimit = min(3, $limit - $recommendationVideos->count());
			if ($userId && $lessWatchedLimit > 0) {
				$lessWatchedVideos = $this->getLessWatchedRecommendations(
					$currentVideo,
					$userId,
					$blacklistChannelIds,
					$recommendationVideos->pluck('id')->toArray(),
					$lessWatchedLimit
				);
				$recommendationVideos = $recommendationVideos->merge($lessWatchedVideos);
			}

			// 5. Eğer toplam hedeften azsa, kalan yerleri popüler videolarla doldur
			if ($recommendationVideos->count() < $limit) {
				$remaining = $limit - $recommendationVideos->count();
				$fillerVideos = $this->getPopularRecommendations(
					$currentVideo,
					$blacklistChannelIds,
					$recommendationVideos->pluck('id')->toArray(),
					$remaining
				);
				$recommendationVideos = $recommendationVideos->merge($fillerVideos);
			}

			return $recommendationVideos;
		}

		/**
		 * Aynı kanaldaki videoları getirir
		 */
		private function getSameChannelRecommendations(
			Video $currentVideo,
			?int $userId,
			int $limit,
			array $blacklistChannelIds
		) {
			$query = Video::query()
				->where('channel_id', $currentVideo->channel_id)
				->whereNot('id', $currentVideo->id)
				->whereNotIn('channel_id', $blacklistChannelIds);

			if ($userId) {
				$query->withCount(['watchHistories as user_watch_count' => function ($q) use ($userId) {
					$q->where('user_id', $userId);
				}])
				->orderByRaw('user_watch_count ASC')
				->orderBy('views', 'desc');
			} else {
				$query->orderBy('views', 'desc');
			}

			return $query->limit($limit)->get();
		}

		/**
		 * Yeni kanallardan videoları getirir
		 */
		private function getNewChannelRecommendations(
			Video $currentVideo,
			?int $userId,
			array $watchedChannelIds,
			array $blacklistChannelIds,
			array $excludeVideoIds,
			int $limit
		) {
			$query = Video::query()
				->whereNot('id', $currentVideo->id)
				->whereNotIn('channel_id', array_merge($watchedChannelIds, $blacklistChannelIds))
				->whereNotIn('id', $excludeVideoIds);

			if ($userId) {
				$query->whereDoesntHave('watchHistories', function ($q) use ($userId) {
					$q->where('user_id', $userId);
				});
			}

			$query->orderBy('views', 'desc');

			return $query->limit($limit)->get();
		}

		/**
		 * Az izlenmiş videoları getirir
		 */
		private function getLessWatchedRecommendations(
			Video $currentVideo,
			int $userId,
			array $blacklistChannelIds,
			array $excludeVideoIds,
			int $limit
		) {
			return Video::query()
				->whereNot('id', $currentVideo->id)
				->whereNotIn('channel_id', $blacklistChannelIds)
				->whereNotIn('id', $excludeVideoIds)
				->whereHas('watchHistories', function ($q) use ($userId) {
					$q->where('user_id', $userId);
				})
				->withCount(['watchHistories as user_watch_count' => function ($q) use ($userId) {
					$q->where('user_id', $userId);
				}])
				->orderBy('user_watch_count', 'asc')
				->orderBy('views', 'desc')
				->limit($limit)
				->get();
		}

		/**
		 * Popüler videoları getirir (fallback)
		 */
		private function getPopularRecommendations(
			Video $currentVideo,
			array $blacklistChannelIds,
			array $excludeVideoIds,
			int $limit
		) {
			return Video::query()
				->whereNot('id', $currentVideo->id)
				->whereNotIn('channel_id', $blacklistChannelIds)
				->whereNotIn('id', $excludeVideoIds)
				->orderBy('views', 'desc')
				->limit($limit)
				->get();
		}
	}