<?php

namespace App\Jobs;


use App\Models\Video;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class CreateGifFromVideo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $video;
	public $disk = 'videos-temp';
	public $video_path;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Video $video, $disk = 'videos-temp', $video_path = null)
    {
        $this->video = $video;
		$this->disk = $disk;
		$this->video_path = $video_path == null ? str_replace('storage/app/public/'.$disk.'/', '', $video->video_slug_path) : $video_path;
    }

    /**
     * Execute the job.
     */
    public function handle()
	{
		$gifDuration = 5;

		$media = FFMpeg::fromDisk($this->disk)->open($this->video_path);

		$duration = (int) $media->getDurationInSeconds();
		$minSecond = 0;

		// 5sn'yi taşırmamak için başlangıç üst sınırı
		$maxStart = max($minSecond, $duration - $gifDuration);
		$startSecond = rand($minSecond, $maxStart);

		$destination = '/' . $this->video->uid . '/' . $this->video->uid . '.gif';

		$media->export()
			->toDisk('videos')
			->addFilter([
				'-ss', (string) $startSecond,          // başlangıç
				'-t',  (string) $gifDuration,          // süre
				'-vf', 'fps=12,scale=480:-1:flags=lanczos', // kalite/boyut dengesi
				'-loop', '0',                          // sonsuz döngü
			])
			->save($destination);

		$this->video->update([
			'thumbnail_gif' => $this->video->uid . '.gif' // alan adını istersen ayrı yap
		]);
	}

}
