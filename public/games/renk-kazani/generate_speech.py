#!/usr/bin/env python3
"""
"Renk Kazanı" oyunu için ElevenLabs ses üretici.

Üretilenler (assets/speech/):
  <anahtar>.mp3        Pofuduk'un cümleleri (index.html'deki LINES ile aynı anahtarlar)
  renk/<renk>.mp3      Renk adları: "turuncu!" (index.html'deki COLORS ile aynı anahtarlar)

Anahtar sırasıyla: ELEVENLABS_API_KEY ortam değişkeni, bu klasördeki .env, ../want-to/.env.
Anahtarı asla commit etme.

Kullanım:
  python3 generate_speech.py          # eksik dosyaları üret
  python3 generate_speech.py --force  # hepsini yeniden üret
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

SCRIPT_DIR = Path(__file__).resolve().parent
SPEECH_DIR = SCRIPT_DIR / "assets" / "speech"
API_BASE = "https://api.elevenlabs.io/v1"
MODEL = "eleven_multilingual_v2"
VOICE = "EXAVITQu4vr4xnSDxMaL"  # Sarah: sıcak kadın sesi (diğer oyunla aynı)

LINES = {
    "iste": "Bana bu renkte bir iksir yapar mısın?",
    "nefis": "Mmm, nefiiis! Teşekkür ederim!",
    "iyy": "Iyyy! Bu olmadı. Hadi bir daha deneyelim!",
    "ipucu": "Bak, bu kavanozlar sana yardım edecek!",
    "ve": "ve",
    "karisinca": "karışınca",
    "olur": "olur!",
    "bitti": "Bugün çok güzel iksirler yaptın! Aferin sana!",
}

COLORS = {
    "red": "kırmızı", "yellow": "sarı", "blue": "mavi", "white": "beyaz",
    "orange": "turuncu", "green": "yeşil", "purple": "mor", "pink": "pembe", "sky": "açık mavi",
}


def load_api_key() -> str:
    for env_path in (SCRIPT_DIR / ".env", SCRIPT_DIR.parent / "want-to" / ".env"):
        if os.getenv("ELEVENLABS_API_KEY") or not env_path.exists():
            continue
        for line in env_path.read_text().splitlines():
            if line.strip().startswith("ELEVENLABS_API_KEY="):
                os.environ["ELEVENLABS_API_KEY"] = line.split("=", 1)[1].strip().strip('"').strip("'")
    key = os.getenv("ELEVENLABS_API_KEY", "").strip()
    if not key:
        print("ELEVENLABS_API_KEY bulunamadı (ortam değişkeni, .env veya ../want-to/.env).")
        sys.exit(1)
    return key


def synthesize(api_key: str, text: str) -> bytes:
    response = requests.post(
        f"{API_BASE}/text-to-speech/{VOICE}",
        json={
            "text": text,
            "model_id": MODEL,
            "voice_settings": {"stability": 0.45, "similarity_boost": 0.75, "style": 0.45, "use_speaker_boost": True},
        },
        headers={"xi-api-key": api_key, "Content-Type": "application/json", "Accept": "audio/mpeg"},
        timeout=120,
    )
    if response.status_code != 200:
        raise RuntimeError(f"HTTP {response.status_code}: {response.text[:300]}")
    return response.content


def main() -> None:
    parser = argparse.ArgumentParser(description="Renk Kazanı sesleri")
    parser.add_argument("--force", action="store_true")
    args = parser.parse_args()
    api_key = load_api_key()

    jobs = [(text, SPEECH_DIR / f"{key}.mp3") for key, text in LINES.items()]
    jobs += [(f"{tr}!", SPEECH_DIR / "renk" / f"{key}.mp3") for key, tr in COLORS.items()]

    created = skipped = failed = 0
    for text, path in jobs:
        if path.exists() and not args.force:
            skipped += 1
            continue
        path.parent.mkdir(parents=True, exist_ok=True)
        try:
            path.write_bytes(synthesize(api_key, text))
            created += 1
            print(f"  ✓ {path.relative_to(SCRIPT_DIR)}  ←  {text}")
        except Exception as e:  # noqa: BLE001
            failed += 1
            print(f"  ✗ {path.relative_to(SCRIPT_DIR)}: {e}")
        time.sleep(0.4)

    print(f"\nÜretilen: {created}  Atlanan: {skipped}  Hata: {failed}")
    if failed:
        sys.exit(1)


if __name__ == "__main__":
    main()
