#!/usr/bin/env python3
"""
Want-to oyunu için ElevenLabs Text-to-Speech ses üretici.

Kullanım:
  export ELEVENLABS_API_KEY="sk_..."
  pip install -r requirements.txt
  python generate_speech.py
  python generate_speech.py --list-voices
  python generate_speech.py --force
  python generate_speech.py --voice-id JBFqnCBsd6RMkjVDRZzb
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

# Oyunla aynı görev listesi (index.html pairs)
PAIRS = [
    # eat
    ("cow", "eat", "apple"),
    ("dog", "eat", "meat"),
    ("horse", "eat", "carrot"),
    ("rabbit", "eat", "carrot"),
    ("monkey", "eat", "banana"),
    ("bear", "eat", "honey"),
    ("squirrel", "eat", "nut"),
    ("chicken", "eat", "corn"),
    ("giraffe", "eat", "leaf"),
    ("sheep", "eat", "grass"),
    ("lion", "eat", "meat"),
    ("hamster", "eat", "carrot"),
    ("frog", "eat", "fly"),
    ("parrot", "eat", "apple"),
    ("bee", "eat", "flower"),
    # drink
    ("bird", "drink", "water"),
    ("cat", "drink", "milk"),
    ("elephant", "drink", "water"),
    ("dog", "drink", "water"),
    ("horse", "drink", "water"),
    ("cow", "drink", "water"),
    ("monkey", "drink", "juice"),
    ("girl", "drink", "milk"),
    # play
    ("dog", "play", "ball"),
    ("girl", "play", "ball"),
    ("cat", "play", "ball"),
    ("monkey", "play", "ball"),
    ("dog", "play", "stick"),
    ("cat", "play", "rope"),
    ("dog", "play", "bone"),
    ("girl", "play", "doll"),
    ("girl", "play", "car"),
]

# Tıklanınca çalınacak tek kelime sesleri: Girl! / Apple!
WORD_NAMES = sorted({animal for animal, _, _ in PAIRS} | {obj for _, _, obj in PAIRS})

# Varsayılan: George (İngilizce çocuk oyunu için net)
DEFAULT_VOICE_ID = "JBFqnCBsd6RMkjVDRZzb"
DEFAULT_MODEL = "eleven_multilingual_v2"


def phrase_for(animal: str, action: str, obj: str) -> str:
    """Oyun ekranındaki cümle kalıbı: Dog want to play ball"""
    return f"{animal.capitalize()} want to {action} {obj}"


def filename_for(animal: str, action: str, obj: str) -> str:
    return f"{animal}-want-to-{action}-{obj}.mp3"


def success_phrase_for(animal: str, action: str, obj: str) -> str:
    """Doğru eşleşme özet cümlesi: Monkey now playing with ball"""
    subject = animal.capitalize()
    if action == "play":
        return f"{subject} now playing with {obj}"
    if action == "drink":
        return f"{subject} now drinking {obj}"
    return f"{subject} now eating {obj}"


def success_filename_for(animal: str, action: str, obj: str) -> str:
    if action == "play":
        return f"{animal}-now-playing-with-{obj}.mp3"
    if action == "drink":
        return f"{animal}-now-drinking-{obj}.mp3"
    return f"{animal}-now-eating-{obj}.mp3"


def word_phrase(name: str) -> str:
    return f"{name.capitalize()}!"


def word_filename(name: str) -> str:
    return f"{name}.mp3"


def load_api_key() -> str:
    if load_dotenv:
        load_dotenv(SCRIPT_DIR / ".env")
        load_dotenv()

    key = os.getenv("ELEVENLABS_API_KEY", "").strip()
    if not key:
        print("ELEVENLABS_API_KEY bulunamadı.")
        print("  export ELEVENLABS_API_KEY='...'")
        print("  veya want-to/.env içine ELEVENLABS_API_KEY=... yaz")
        sys.exit(1)
    return key


def list_voices(api_key: str) -> None:
    response = requests.get(
        f"{API_BASE}/voices",
        headers={"xi-api-key": api_key},
        timeout=60,
    )
    if response.status_code != 200:
        print(f"Voice listesi alınamadı: {response.status_code} {response.text}")
        sys.exit(1)

    voices = response.json().get("voices", [])
    print(f"{'VOICE_ID':<28} NAME")
    print("-" * 56)
    for voice in voices:
        print(f"{voice.get('voice_id', ''):<28} {voice.get('name', '')}")


def synthesize(
    api_key: str,
    voice_id: str,
    text: str,
    model_id: str,
) -> bytes:
    url = f"{API_BASE}/text-to-speech/{voice_id}"
    payload = {
        "text": text,
        "model_id": model_id,
        "voice_settings": {
            "stability": 0.45,
            "similarity_boost": 0.75,
            "style": 0.2,
            "use_speaker_boost": True,
        },
    }
    headers = {
        "xi-api-key": api_key,
        "Content-Type": "application/json",
        "Accept": "audio/mpeg",
    }

    response = requests.post(url, json=payload, headers=headers, timeout=120)
    if response.status_code != 200:
        raise RuntimeError(f"HTTP {response.status_code}: {response.text[:500]}")
    return response.content


def generate_all(
    api_key: str,
    voice_id: str,
    model_id: str,
    force: bool,
    delay: float,
    words_only: bool,
    phrases_only: bool,
    success_only: bool,
) -> None:
    SPEECH_DIR.mkdir(parents=True, exist_ok=True)

    created = 0
    skipped = 0
    failed = 0

    jobs: list[tuple[str, Path]] = []

    if success_only:
        for animal, action, obj in PAIRS:
            jobs.append(
                (
                    success_phrase_for(animal, action, obj),
                    SPEECH_DIR / success_filename_for(animal, action, obj),
                )
            )
    else:
        if not words_only:
            for animal, action, obj in PAIRS:
                jobs.append((phrase_for(animal, action, obj), SPEECH_DIR / filename_for(animal, action, obj)))

        if not phrases_only:
            for name in WORD_NAMES:
                jobs.append((word_phrase(name), SPEECH_DIR / word_filename(name)))

        if not words_only and not phrases_only:
            for animal, action, obj in PAIRS:
                jobs.append(
                    (
                        success_phrase_for(animal, action, obj),
                        SPEECH_DIR / success_filename_for(animal, action, obj),
                    )
                )

    print(f"Çıktı klasörü: {SPEECH_DIR}")
    print(f"Voice: {voice_id}")
    print(f"Model: {model_id}")
    print(f"Toplam dosya: {len(jobs)}\n")

    for text, out_path in jobs:
        if out_path.exists() and not force:
            print(f"[skip] {out_path.name}")
            skipped += 1
            continue

        print(f"[gen ] {out_path.name}  <-  \"{text}\"")
        try:
            audio = synthesize(api_key, voice_id, text, model_id)
            out_path.write_bytes(audio)
            created += 1
        except Exception as exc:
            print(f"  HATA: {exc}")
            failed += 1

        if delay > 0:
            time.sleep(delay)

    print("\nBitti.")
    print(f"  oluşturuldu: {created}")
    print(f"  atlandı:     {skipped}")
    print(f"  hata:        {failed}")


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(description="Want-to ElevenLabs TTS üretici")
    parser.add_argument(
        "--voice-id",
        default=os.getenv("ELEVENLABS_VOICE_ID", DEFAULT_VOICE_ID),
        help=f"ElevenLabs voice_id (varsayılan: {DEFAULT_VOICE_ID})",
    )
    parser.add_argument(
        "--model",
        default=os.getenv("ELEVENLABS_MODEL", DEFAULT_MODEL),
        help=f"Model id (varsayılan: {DEFAULT_MODEL})",
    )
    parser.add_argument(
        "--force",
        action="store_true",
        help="Var olan mp3 dosyalarını yeniden üret",
    )
    parser.add_argument(
        "--delay",
        type=float,
        default=0.4,
        help="İstekler arası bekleme (saniye), rate limit için",
    )
    parser.add_argument(
        "--list-voices",
        action="store_true",
        help="Hesaptaki sesleri listele ve çık",
    )
    parser.add_argument(
        "--words-only",
        action="store_true",
        help="Sadece tek kelime seslerini üret (Girl!, Apple!)",
    )
    parser.add_argument(
        "--phrases-only",
        action="store_true",
        help="Sadece cümle seslerini üret",
    )
    parser.add_argument(
        "--success-only",
        action="store_true",
        help="Sadece doğru eşleşme cümlelerini üret (now eating/drinking/playing)",
    )
    return parser.parse_args()


def main() -> None:
    args = parse_args()
    api_key = load_api_key()

    if args.list_voices:
        list_voices(api_key)
        return

    generate_all(
        api_key=api_key,
        voice_id=args.voice_id,
        model_id=args.model,
        force=args.force,
        delay=args.delay,
        words_only=args.words_only,
        phrases_only=args.phrases_only,
        success_only=args.success_only,
    )


if __name__ == "__main__":
    main()
