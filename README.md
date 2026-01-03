# İndirmek için hangi programı kullanıyoruz
https://github.com/yt-dlp/yt-dlp
https://github.com/yt-dlp/yt-dlp/releases -> son realese'e tıkla ve yt-dlp.exe indir

#İndirme nasıl yapılır?
Diyelim ki Transformers indireceksin.
Transformers kanal adı oluyor, içerisine konulan videolar da bu kanala yüklenilen videolar oluyor.
yt-dlp.exe'yi al bi klasöre koy. klasör adı Transformers olsun
cmd ile yaz: yt-dlp.exe -f "best[ext=mp4][height<=720]" --download-archive archive.txt "URL"


daha sonra cmd ile php artisan manuel:video-update "Transformers" ile de indirilen klasör'ü laravel'e tanıtıyoruz.


php artisan manuel:video-update

# Tüm klasörler için

php artisan manuel:thumbnail-regenerate
php artisan manuel:gif-regenerate

# Sadece belirli bir klasör için

php artisan manuel:thumbnail-regenerate "klasor-adi"
php artisan manuel:gif-regenerate "klasor-adi"
