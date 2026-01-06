<?php

namespace App\Console\Commands;

use App\Models\Video;
use App\Services\VideoService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ManuelGifRegenerate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'manuel:gif-regenerate {folder? : İşlenecek klasör adı}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manuel videolar için GIF\'leri yeniden oluşturur';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('GIF yeniden oluşturma işlemi başlatılıyor...');
        $folderParam = $this->argument('folder');
        $disk = Storage::disk('manuel-videos');

        if ($folderParam) {
            // SADECE BELİRTİLEN KLASÖR
            if (!$disk->exists($folderParam)) {
                $this->error("❌ '{$folderParam}' klasörü bulunamadı.");
                return Command::FAILURE;
            }

            $files = $disk->allFiles($folderParam);
            $this->info("Sadece '{$folderParam}' klasörü işleniyor.");
        } else {
            // TÜM KLASÖRLER
            $files = $disk->allFiles();
            $this->info('Tüm klasörler işleniyor.');
        }

        $totalFileCount = count($files);
        $this->info('Toplam ' . $totalFileCount . ' dosya bulundu.');

        // Dosyaları klasörlere göre grupla
        $groupedFiles = collect($files)->groupBy(function ($file) {
            return dirname($file); // Klasör ismini al
        });

        $processedCount = 0;
        $skippedCount = 0;

        foreach ($groupedFiles as $folderName => $filesInFolder) {
            $this->line('Klasör işleniyor: ' . $folderName);

            // Bu klasördeki her dosya için işlem yap
            foreach ($filesInFolder as $index => $file) {
                // Dosya uzantısını kontrol et
                $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                $allowedExtensions = ['mp4', 'avi', 'mov', 'wmv', 'flv', 'mkv', 'webm', 'm4v'];

                if (!in_array($extension, $allowedExtensions)) {
                    $this->warn('  ⊘ Video dosyası değil, es geçiliyor: ' . basename($file));
                    $skippedCount++;
                    continue;
                }

                $this->line(($processedCount + 1) . '/' . $totalFileCount . '  - Video işleniyor: ' . basename($file));

                // Dosya hash'ini hesapla
                $fullPath = Storage::disk('manuel-videos')->path($file);
                $fileHash = hash_file('sha256', $fullPath);

                // Hash'e göre video'yu bul
                $video = Video::where('file_hash', $fileHash)->first();

                if (!$video) {
                    $this->warn('  ⚠ Video veritabanında bulunamadı, es geçiliyor...');
                    $skippedCount++;
                    continue;
                }

                try {
                    // GIF'i yeniden oluştur
                    app(VideoService::class)->generateGif($video, 'manuel-videos', $file);
                    $this->info('  ✅ GIF başarıyla oluşturuldu');
                    $processedCount++;
                } catch (\Exception $e) {
                    $this->error('  ❌ Hata: ' . $e->getMessage());
                    $skippedCount++;
                }
            }
        }

        $this->info('İşlem tamamlandı!');
        $this->info("✅ İşlenen: {$processedCount}");
        $this->info("⊘ Atlanan: {$skippedCount}");

        return Command::SUCCESS;
    }
}
