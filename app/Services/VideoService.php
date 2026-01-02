<?php

	namespace App\Services;

use App\Jobs\ConvertVideoForStreaming;
use App\Jobs\CreateThumbnailFromVideo;
use App\Jobs\CreateGifFromVideo;
use App\Models\Channel;
use App\Models\Video;
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
	}