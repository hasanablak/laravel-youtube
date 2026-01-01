<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class Video extends Model
{
    use HasFactory;

    protected $guarded = [];

	protected $appends = ['video_url','thumbnail_url'];


	public function getVideoUrlAttribute()
	{
		if ($this->is_converting_for_streaming) {
			return '/videos/' . $this->uid . '/' . $this->processed_file;
		} else {
			return $this->video_orginal_url;
		}
	}

	public function getThumbnailUrlAttribute(){
		return Storage::disk('videos')->url($this->uid . '/' . $this->thumbnail_image);
	}
    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }


    public function getThumbnailAttribute()
    {

        if ($this->thumbnail_image) {
            return '/videos/' . $this->uid . '/' . $this->thumbnail_image;
        } else {
            return '/videos/' . 'default.png';
        }
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function dislikes()
    {
        return $this->hasMany(Dislike::class);
    }

    public function comments()
    {
        return $this->hasMany(Video::class);
    }

    public function getRouteKeyName()
    {
        return 'uid';
    }

    public function getUploadedDateAttribute()
    {
        $d = new Carbon($this->created_at);

        return $d->toFormattedDateString();
    }

    public function watchHistories()
    {
        return $this->hasMany(WatchHistory::class);
    }
}
