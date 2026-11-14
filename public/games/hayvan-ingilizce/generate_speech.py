#!/usr/bin/env python3
"""
"Hayvanı İngilizce Söyle" oyunu için ElevenLabs ses üretici.

Üretilenler (assets/speech/):
  tr/<anahtar>.mp3   Türkçe sorular ve tepkiler (index.html'deki TR_LINES ile aynı anahtarlar)
  en/<kelime>.mp3    İngilizce kelimeler: "Cat!", "Red!", "Apple!" (index.html'deki ANIMALS/COLORS/OBJECTS ile aynı)

Anahtar sırasıyla şuralardan okunur: ELEVENLABS_API_KEY ortam değişkeni,
bu klasördeki .env, ../want-to/.env. Anahtarı asla commit etme.

Kullanım:
  python3 generate_speech.py            # eksik dosyaları üret
  python3 generate_speech.py --force    # hepsini yeniden üret
  python3 generate_speech.py --only tr  # sadece Türkçe (ya da: --only en)
"""
from __future__ import annotations

import argparse
import os
import sys
import time
from pathlib import Path

try:
    import requests
except ImportError:
    print("requests gerekli: pip install requests")
    sys.exit(1)

try:
    from dotenv import load_dotenv
except ImportError:
    load_dotenv = None

SCRIPT_DIR = Path(__file__).resolve().parent
SPEECH_DIR = SCRIPT_DIR / "assets" / "speech"
API_BASE = "https://api.elevenlabs.io/v1"
MODEL = "eleven_multilingual_v2"

# ElevenLabs hazır sesleri
VOICE_TR = "EXAVITQu4vr4xnSDxMaL"  # Sarah: sıcak kadın sesi (Türkçe sorular)
VOICE_EN = "JBFqnCBsd6RMkjVDRZzb"  # George: want-to oyunundaki İngilizce ses

TR_LINES = {
    "soru": "Bu hayvanın İngilizcesi ne?",
    "renk-soru": "Bu renk İngilizce ne?",
    "nesne-soru": "Bu ne? İngilizcesini söyle!",
    "harika": "Harika!",
    "neredeyse": "Neredeyse! Hadi birlikte söyleyelim.",
    "duyamadim": "Seni duyamadım. Bir daha söyler misin?",
    "bu-bir": "Bu bir...",
    "supersin": "Süpersin! Çok güzel söyledin!",
    "aferin": "Aferin sana! Yine oynayalım mı?",
}

EN_WORDS = [
    "cat", "dog", "cow", "sheep", "horse", "lion", "elephant", "monkey", "frog", "chicken",
    "rabbit", "bird", "bee", "giraffe", "whale", "bear", "donkey", "camel", "crocodile", "squirrel",
    # renkler
    "red", "blue", "green", "yellow", "pink", "purple", "orange",
    # nesneler (assets/images/objects/*.png)
    "apple", "ball", "car", "star", "sun", "house", "flower", "book", "cake", "balloon", "banana", "moon",
]


def load_api_key() -> str:
    if load_dotenv:
        load_dotenv(SCRIPT_DIR / ".env")
        load_dotenv(SCRIPT_DIR.parent / "want-to" / ".env")
    else:
        # python-dotenv yoksa .env dosyalarını basitçe oku
        for env_path in (SCRIPT_DIR / ".env", SCRIPT_DIR.parent / "want-to" / ".env"):
            if env_path.exists() and not os.getenv("ELEVENLABS_API_KEY"):
                for line in env_path.read_text().splitlines():
                    if line.strip().startswith("ELEVENLABS_API_KEY="):
                        os.environ["ELEVENLABS_API_KEY"] = line.split("=", 1)[1].strip().strip('"').strip("'")
    key = os.getenv("ELEVENLABS_API_KEY", "").strip()
    if not key:
        print("ELEVENLABS_API_KEY bulunamadı (ortam değişkeni, .env veya ../want-to/.env).")
        sys.exit(1)
    return key


def synthesize(api_key: str, voice_id: str, text: str, stability: float) -> bytes:
    response = requests.post(
        f"{API_BASE}/text-to-speech/{voice_id}",
        json={
            "text": text,
            "model_id": MODEL,
            "voice_settings": {
                "stability": stability,
                "similarity_boost": 0.75,
                "style": 0.35,
                "use_speaker_boost": True,
            },
        },
        headers={"xi-api-key": api_key, "Content-Type": "application/json", "Accept": "audio/mpeg"},
        timeout=120,
    )
    if response.status_code != 200:
        raise RuntimeError(f"HTTP {response.status_code}: {response.text[:300]}")
    return response.content


def main() -> None:
    parser = argparse.ArgumentParser(description="Hayvanı İngilizce Söyle sesleri")
    parser.add_argument("--force", action="store_true", help="Var olan dosyaların üzerine yaz")
    parser.add_argument("--only", choices=["tr", "en"], help="Sadece bir dil üret")
    parser.add_argument("--delay", type=float, default=0.4, help="İstekler arası bekleme (sn)")
    args = parser.parse_args()

    api_key = load_api_key()

    jobs: list[tuple[str, str, Path, float]] = []  # (voice, text, path, stability)
    if args.only in (None, "tr"):
        for key, text in TR_LINES.items():
            jobs.append((VOICE_TR, text, SPEECH_DIR / "tr" / f"{key}.mp3", 0.5))
    if args.only in (None, "en"):
        for word in EN_WORDS:
            jobs.append((VOICE_EN, f"{word.capitalize()}!", SPEECH_DIR / "en" / f"{word}.mp3", 0.6))

    created = skipped = failed = 0
    for voice, text, path, stability in jobs:
        if path.exists() and not args.force:
            skipped += 1
            continue
        path.parent.mkdir(parents=True, exist_ok=True)
        try:
            path.write_bytes(synthesize(api_key, voice, text, stability))
            created += 1
            print(f"  ✓ {path.relative_to(SCRIPT_DIR)}  ←  {text}")
        except Exception as e:  # noqa: BLE001
            failed += 1
            print(f"  ✗ {path.relative_to(SCRIPT_DIR)}: {e}")
        time.sleep(args.delay)

    print(f"\nÜretilen: {created}  Atlanan: {skipped}  Hata: {failed}")
    if failed:
        sys.exit(1)


if __name__ == "__main__":
    main()
