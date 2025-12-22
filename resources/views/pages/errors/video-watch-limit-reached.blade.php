<!-- filepath: /Users/hasanablak/Develop/Laravel/laravel-youtube/resources/views/pages/errors/video-watch-limit-reached.blade.php -->
@extends('layouts.app', [
    'title' => 'Mola Zamanı! 🎈'
])

@section('content')

<div class="limit-container">
    <div class="stars">
        <div class="star">⭐</div>
        <div class="star">✨</div>
        <div class="star">🌟</div>
        <div class="star">💫</div>
        <div class="star">⭐</div>
        <div class="star">✨</div>
    </div>

    <div class="limit-card">
        <div class="video-section">
            <div class="video-wrapper">
				@php
					$rand = rand(0, 2);
				@endphp
                <video autoplay muted loop playsinline class="limit-video">
                    <source src="{{ asset('assets/videos/bye-bye-' . $rand . '/video-1.mp4') }}" type="video/mp4">
                </video>
				
                <div class="video-overlay">
                    <div class="overlay-text">👋 Ekranı Kapat</div>
                    <div class="overlay-subtext">Oyuncaklarla Oynama Zamanı!</div>
                </div>
				<audio autoplay>
					<source src="{{ asset('assets/speech/hafsacim-bugunki-cizgifilm-saatimiz-bitti.mp3') }}" type="audio/mpeg">
				</audio>
            </div>
        </div>

        <div class="emoji-big">🎮</div>
        
        <h1 class="limit-title">Mola Zamanı!</h1>
        
        <p class="limit-message">
            Bugün çok fazla video izledin! 🎉<br>
            Şimdi bu çocuk gibi ekranı kapat ve oyuncaklarınla oyna!
        </p>

        <div class="activity-suggestions">
            <div class="activity-title">Şimdi Ne Yapabilirsin? 🤔</div>
            
            <div class="activities">
                <div class="activity-item" title="Oyun Oyna">
                    🎲
                    <div class="activity-label">Oyun</div>
                </div>
                <div class="activity-item" title="Kitap Oku">
                    📚
                    <div class="activity-label">Kitap</div>
                </div>
                <div class="activity-item" title="Resim Yap">
                    🎨
                    <div class="activity-label">Resim</div>
                </div>
                <div class="activity-item" title="Bahçeye Çık">
                    🌳
                    <div class="activity-label">Bahçe</div>
                </div>
                <div class="activity-item" title="Arkadaşlarınla Oyna">
                    👫
                    <div class="activity-label">Arkadaş</div>
                </div>
                <div class="activity-item" title="Su İç">
                    💧
                    <div class="activity-label">Su İç</div>
                </div>
            </div>
        </div>

        <div class="countdown-timer">
            <div class="timer-icon">⏰</div>
            <div>Yarın Tekrar Gel!</div>
            <div style="font-size: 20px; margin-top: 10px;">
                Yeni videolar seni bekliyor olacak! 🎬
            </div>
        </div>

        <button class="back-btn" onclick="window.location.href='{{ route('home') }}'">
            🏠 Ana Sayfaya Dön
        </button>
    </div>
</div>

<script>
    // Aktivitelere tıklanınca animasyon
    document.querySelectorAll('.activity-item').forEach(item => {
        item.addEventListener('click', function() {
            this.style.animation = 'none';
            setTimeout(() => {
                this.style.animation = '';
            }, 10);
        });
    });
</script>
@endsection

@push('header')
<style>
    .limit-container {
        min-height: 100vh;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        position: relative;
        overflow: hidden;
    }

    .stars {
        position: absolute;
        width: 100%;
        height: 100%;
        pointer-events: none;
    }

    .star {
        position: absolute;
        color: white;
        font-size: 30px;
        animation: twinkle 2s ease-in-out infinite;
    }

    .star:nth-child(1) { top: 10%; left: 10%; animation-delay: 0s; }
    .star:nth-child(2) { top: 20%; right: 15%; animation-delay: 0.5s; }
    .star:nth-child(3) { bottom: 15%; left: 20%; animation-delay: 1s; }
    .star:nth-child(4) { bottom: 25%; right: 10%; animation-delay: 1.5s; }
    .star:nth-child(5) { top: 50%; left: 5%; animation-delay: 0.3s; }
    .star:nth-child(6) { top: 40%; right: 8%; animation-delay: 0.8s; }

    @keyframes twinkle {
        0%, 100% { opacity: 0.3; transform: scale(1); }
        50% { opacity: 1; transform: scale(1.3); }
    }

    .limit-card {
        background: white;
        border-radius: 40px;
        padding: 60px 40px;
        max-width: 700px;
        width: 100%;
        text-align: center;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        animation: bounceIn 0.8s ease-out;
        position: relative;
        z-index: 10;
    }

    @keyframes bounceIn {
        0% { transform: scale(0.3); opacity: 0; }
        50% { transform: scale(1.05); }
        70% { transform: scale(0.9); }
        100% { transform: scale(1); opacity: 1; }
    }

    .video-section {
        margin-bottom: 30px;
        animation: slideDown 1s ease-out;
    }

    @keyframes slideDown {
        0% { transform: translateY(-30px); opacity: 0; }
        100% { transform: translateY(0); opacity: 1; }
    }

    .video-wrapper {
        position: relative;
        border-radius: 25px;
        overflow: hidden;
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
        max-width: 500px;
        margin: 0 auto;
        background: #000;
    }

    .limit-video {
        width: 100%;
        height: auto;
        display: block;
        border-radius: 25px;
    }

    .video-overlay {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, transparent 100%);
        padding: 20px;
        color: white;
        animation: fadeInUp 1.5s ease-out;
    }

    @keyframes fadeInUp {
        0% { transform: translateY(20px); opacity: 0; }
        100% { transform: translateY(0); opacity: 1; }
    }

    .overlay-text {
        font-size: 24px;
        font-weight: bold;
        margin-bottom: 5px;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        font-family: 'Comic Sans MS', cursive, sans-serif;
    }

    .overlay-subtext {
        font-size: 16px;
        opacity: 0.9;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
        font-family: 'Comic Sans MS', cursive, sans-serif;
    }

    .emoji-big {
        font-size: 80px;
        margin-bottom: 20px;
        animation: float 3s ease-in-out infinite;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-20px); }
    }

    .limit-title {
        font-size: 48px;
        font-weight: bold;
        color: #333;
        margin-bottom: 20px;
        font-family: 'Comic Sans MS', cursive, sans-serif;
    }

    .limit-message {
        font-size: 24px;
        color: #666;
        line-height: 1.6;
        margin-bottom: 30px;
        font-family: 'Comic Sans MS', cursive, sans-serif;
    }

    .activity-suggestions {
        background: linear-gradient(135deg, #FFE66D 0%, #FFEB99 100%);
        border-radius: 20px;
        padding: 30px;
        margin: 30px 0;
    }

    .activity-title {
        font-size: 28px;
        font-weight: bold;
        color: #FF6B6B;
        margin-bottom: 20px;
        font-family: 'Comic Sans MS', cursive, sans-serif;
    }

    .activities {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
        gap: 15px;
        margin-top: 20px;
    }

    .activity-item {
        background: white;
        border-radius: 15px;
        padding: 15px;
        font-size: 40px;
        transition: transform 0.3s;
        cursor: pointer;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .activity-item:hover {
        transform: scale(1.1) rotate(5deg);
    }

    .activity-label {
        font-size: 14px;
        color: #666;
        margin-top: 5px;
        font-family: 'Comic Sans MS', cursive, sans-serif;
    }

    .countdown-timer {
        background: linear-gradient(135deg, #4ECDC4 0%, #44A08D 100%);
        color: white;
        border-radius: 20px;
        padding: 20px;
        font-size: 28px;
        font-weight: bold;
        margin-top: 30px;
        font-family: 'Comic Sans MS', cursive, sans-serif;
    }

    .timer-icon {
        font-size: 50px;
        margin-bottom: 10px;
    }

    .back-btn {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 20px;
        padding: 20px 50px;
        font-size: 24px;
        font-weight: bold;
        cursor: pointer;
        margin-top: 30px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        transition: all 0.3s;
        font-family: 'Comic Sans MS', cursive, sans-serif;
    }

    .back-btn:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.3);
    }

    @media (max-width: 768px) {
        .limit-card {
            padding: 40px 20px;
        }
        
        .limit-title {
            font-size: 36px;
        }
        
        .limit-message {
            font-size: 20px;
        }
        
        .emoji-big {
            font-size: 60px;
        }

        .video-wrapper {
            max-width: 100%;
        }

        .overlay-text {
            font-size: 20px;
        }

        .overlay-subtext {
            font-size: 14px;
        }
    }
</style>
@endpush