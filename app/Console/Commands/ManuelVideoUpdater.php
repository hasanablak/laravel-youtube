<?php

namespace App\Console\Commands;

use App\Models\Channel;
use App\Models\User;
use App\Models\Video;
use App\Services\VideoService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ManuelVideoUpdater extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'manuel:video-updater';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Video güncelleme işlemi başlatılıyor...');

        $disk = Storage::disk('manuel-videos');
        $files = $disk->allFiles();
		$totalFileCount = count($files);
        $this->info('Toplam ' . $totalFileCount . ' dosya bulundu.');

        // Dosyaları klasörlere göre grupla
        $groupedFiles = collect($files)->groupBy(function ($file) {
            return dirname($file); // Klasör ismini al
        });

        foreach ($groupedFiles as $folderName => $filesInFolder) {
            $this->line('Klasör işleniyor: ' . $folderName);

            // Channel'ı bul veya oluştur
			$channel = Channel::where('name', $folderName)->first();
			if (!$channel) {
				$user = User::create([
					'name' => $folderName,
					'email' => Str::slug($folderName) . '@example.com',
					'password' => bcrypt('password'), // Güvenlik için gerçek bir parola kullanın
				]);
                $channel = Channel::create([
                        'user_id' => $user->id,
						'uid' => Str::uuid(),
                        'name' => $folderName,
                        'slug' => Str::slug($folderName),
                        'description' => 'Otomatik oluşturuldu: ' . $folderName,
                        // Diğer gerekli alanları buraya ekleyin
                    ]);
				$this->info('  ✓ Yeni channel oluşturuldu: ' . $folderName);

			} else {
				$this->info('  ✓ Mevcut channel bulundu: ' . $folderName);
			}

            // Bu klasördeki her dosya için işlem yap
            foreach ($filesInFolder as $index => $file) {
			
				// Dosya uzantısını kontrol et
				$extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
				$allowedExtensions = ['mp4', 'avi', 'mov', 'wmv', 'flv', 'mkv', 'webm', 'm4v'];
				
				if (!in_array($extension, $allowedExtensions)) {
					$this->warn('  ⊘ Video dosyası değil, es geçiliyor: ' . basename($file));
					continue;
				}

                $this->line($index + 1 . '/' . $totalFileCount . '  - Video işleniyor: ' . basename($file));

				
				// Dosya hash'ini hesapla
				$fullPath = Storage::disk('manuel-videos')->path($file);
				$fileHash = hash_file('sha256', $fullPath);
				// Hash'e göre kontrol et
				$existingVideo = Video::where('file_hash', $fileHash)->first();
				if ($existingVideo) {
					$this->info('👍 Video zaten var (hash eşleşti), es geçiliyor...');
					continue;
				}else{
					$this->info('✨ Yeni video, veritabanına ekleniyor...');
				}

				// Dosyayı al veya path'i kullan
				$diskPath_ = 'storage/app/public/manuel-videos/';
				$diskPath =  $diskPath_ . $file;
				$fileName = basename($file); // '1-bolu.mov'
				
				// $videoPath = app(VideoService::class)->uploadVideoToStorage(
				// 	$fullPath
				// );
				$title = Str::beforeLast($fileName, '[');
				$title = Str::beforeLast($title, '.');

				//Change file name to slug version


				$slugflyFileName = app(VideoService::class)->slugflyFileName($file);

				$video = app(VideoService::class)
					->saveVideoToDatabase($channel->uid, $diskPath, [
						'title' =>  $title,
						'description' => null,
						'visibility' => "public",
						'file_hash' => $fileHash,
						'video_orginal_name' => $fileName,
						'video_slug_url' => config('app.url') . '/storage/manuel-videos/' . $slugflyFileName,
						'video_slug_path' => $diskPath_ . $slugflyFileName,
					],
					config('app.url') . '/storage/manuel-videos/' . $slugflyFileName
				);

				app(VideoService::class)->generateThumbnail($video, 'manuel-videos');
				$this->info('✅ Video başarıyla eklendi');

			}
        }

        $this->info('İşlem tamamlandı!');

        return Command::SUCCESS;
    }
}
