<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VideoController;
use Illuminate\Support\Facades\Route;
use Nwidart\Modules\Facades\Module;

Route::get('/',[HomeController::class,'index'])->name('home');

Route::get('/register',[HomeController::class,'registerPage'])->name('register.page');

Route::post('/register', [AuthController::class, 'register'])->name('register.submit');

Route::get('/login', [AuthController::class, 'loginPage'])->name('login.page');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

Route::get('/channels/{channel}', [VideoController::class,'channel'])->name('channel');
Route::get('/watch/{video}', [VideoController::class,'video'])->name('video.watch');

Route::get('/api/count/{video_id}',[VideoController::class, 'countLikeDislike'])
    ->name('count.like-dislike');

Route::get('api/comments/{video}', [CommentController::class, 'index']);

Route::post('videos/search', [SearchController::class, 'search'])->name('search.video');

Route::group(['middleware' => ['web','auth']], function (){

    Route::get('/channel/{channel}/create',[VideoController::class,'videoUploadPage'])->name('video.page');
    Route::post('/channel/{channel}/create',[VideoController::class,'videoUpload'])->name('videos.store');

    Route::get('/videos/{channel}/{video}/edit', [VideoController::class,'videoEditPage'])->name('video.edit.page');
    Route::post('/videos/{channel}/{video}/edit', [VideoController::class,'videoEdit'])->name('video.edit');

    Route::get('/profile/{id}',[UserController::class, 'updatePage'])->name('profile.page');
    Route::post('/profile/{id}',[UserController::class, 'update'])->name('profile.update');

    Route::post('api/channels/{channel_id}', [VideoController::class, 'subscribe'])
        ->middleware('auth')
        ->name('subscribe');

    Route::get('/api/check-subscription/{channel_id}', [VideoController::class, 'checkSubscription'])
        ->middleware('auth')
        ->name('check.subscription');

    Route::get('/api/check-like/{video_id}',[VideoController::class, 'checkLikeDislike'])
        ->middleware('auth')
        ->name('check.like');

    Route::post('/api/videos/{video_id}/like',[VideoController::class, 'like'])
        ->middleware('auth')
        ->name('like');

    Route::post('/api/videos/{video_id}/dislike',[VideoController::class, 'dislike'])
        ->middleware('auth')
        ->name('dislike');

    Route::post('api/comment/{video}', [CommentController::class, 'store']);
    Route::post('api/comment/{video}/reply', [CommentController::class, 'reply']);
    Route::delete('api/comment/{video}', [CommentController::class, 'destroy']);

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

Route::view('/errors/video-watch-limit-reached', 'pages.errors.video-watch-limit-reached')->name('errors.video-watch-limit-reached');

Route::get('/admin', function () {
	$modules = Module::all();

	return view('admin.dashboard', [
		"modules" => $modules
	]);
})->name('admin.dashboard');

Route::get('/redirect', fn () => (redirect()->route('admin.dashboard')))->name('');

Route::post('api/videos/{video}/end', [VideoController::class, 'onVideoEnd'])->name('api.videos.end');
Route::get('api/check-daily-watch-limit', [VideoController::class, 'checkDailyWatchLimit'])->name('api.videos.check-daily-watch-limit');
