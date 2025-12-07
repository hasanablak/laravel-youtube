<?php

namespace App\Jobs;

use App\Models\Video;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class CreateThumbnailFromVideo implements ShouldQueue
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
     *
     * @return void
     */
    public function handle()
    {
        $destination = '/' . $this->video->uid . '/' . $this->video->uid . '.png';
       
        FFMpeg::fromDisk($this->disk)
            ->open($this->video_path)
            ->getFrameFromSeconds(rand(60, 500))
            ->export()
            ->toDisk('videos')
            ->save($destination);

        $this->video->update([
            'thumbnail_image' => $this->video->uid . '.png'
        ]);
    }
}
