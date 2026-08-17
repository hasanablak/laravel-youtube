#!/usr/bin/env python3
"""
Son izleme hakkı uyarısı için ElevenLabs TTS (kadın ses).

Kullanım:
  pip install requests python-dotenv
  python generate_speech.py
  python generate_speech.py --list-voices
  python generate_speech.py --force
"""

from __future__ import annotations

import argparse
import os
import sys
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
PROJECT_ROOT = SCRIPT_DIR.parents[3]
OUTPUT_FILE = SCRIPT_DIR / "warning.mp3"
API_BASE = "https://api.elevenlabs.io/v1"

TEXT = "Hafsacığım son 1 izleme hakkın kaldı"

# Kadın ses (Bella) — free tier ile uyumlu, Türkçe için multilingual model
DEFAULT_FEMALE_VOICE_ID = "EXAVITQu4vr4xnSDxMaL"
DEFAULT_MODEL = "eleven_multilingual_v2"


def load_api_key() -> str:
    if load_dotenv:
        load_dotenv(PROJECT_ROOT / ".env")
        load_dotenv(PROJECT_ROOT / "public/games/want-to/.env")
        load_dotenv(SCRIPT_DIR / ".env")

    key = os.getenv("ELEVENLABS_API_KEY", "").strip()
    if not key:
        print("ELEVENLABS_API_KEY bulunamadı.")
        print("  Proje .env veya public/games/want-to/.env içine ekleyin.")
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
    print(f"{'VOICE_ID':<28} {'LABELS':<24} NAME")
    print("-" * 72)
    for voice in voices:
        labels = voice.get("labels") or {}
        gender = labels.get("gender", "-")
        print(f"{voice.get('voice_id', ''):<28} {gender:<24} {voice.get('name', '')}")


def synthesize(api_key: str, voice_id: str, text: str, model_id: str) -> bytes:
    url = f"{API_BASE}/text-to-speech/{voice_id}"
    payload = {
        "text": text,
        "model_id": model_id,
        "voice_settings": {
            "stability": 0.55,
            "similarity_boost": 0.8,
            "style": 0.35,
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


def main() -> None:
    parser = argparse.ArgumentParser(description="Son izleme hakkı uyarı sesi üret")
    parser.add_argument(
        "--voice-id",
        default=os.getenv("ELEVENLABS_FEMALE_VOICE_ID", DEFAULT_FEMALE_VOICE_ID),
        help=f"Kadın voice_id (varsayılan: Rachel {DEFAULT_FEMALE_VOICE_ID})",
    )
    parser.add_argument(
        "--model",
        default=os.getenv("ELEVENLABS_MODEL", DEFAULT_MODEL),
        help=f"Model (varsayılan: {DEFAULT_MODEL})",
    )
    parser.add_argument("--force", action="store_true", help="Var olan dosyayı yeniden üret")
    parser.add_argument("--list-voices", action="store_true", help="Sesleri listele")
    args = parser.parse_args()

    api_key = load_api_key()

    if args.list_voices:
        list_voices(api_key)
        return

    if OUTPUT_FILE.exists() and not args.force:
        print(f"[skip] {OUTPUT_FILE.name} zaten var (--force ile yeniden üret)")
        return

    print(f"[gen ] {OUTPUT_FILE.name}")
    print(f"       voice: {args.voice_id}")
    print(f"       text:  \"{TEXT}\"")

    audio = synthesize(api_key, args.voice_id, TEXT, args.model)
    OUTPUT_FILE.write_bytes(audio)
    print(f"Bitti: {OUTPUT_FILE}")


if __name__ == "__main__":
    main()
