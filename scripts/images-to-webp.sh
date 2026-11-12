#!/usr/bin/env bash
# Convertit les images JPEG de img/lieux et img/events en WebP (2 tailles).
# Usage : bash scripts/images-to-webp.sh   (nécessite le binaire cwebp ou docker)
set -euo pipefail
cd "$(dirname "$0")/.."

OUT_SIZES="1200 800"

if command -v cwebp >/dev/null 2>&1; then
  for f in frontend/overlay/public/img/lieux/*.jpg frontend/overlay/public/img/events/*.jpg; do
    base="${f%.jpg}"
    for w in $OUT_SIZES; do
      cwebp -q 82 -resize "$w 0" "$f" -o "${base}-${w}.webp"
    done
  done
elif command -v docker >/dev/null 2>&1; then
  docker run --rm -v "$PWD/frontend/overlay/public/img:/img" webmproject/libwebp \
    sh -c 'for f in /img/lieux/*.jpg /img/events/*.jpg; do base="${f%.jpg}"; for w in 1200 800; do cwebp -q 82 -resize "$w 0" "$f" -o "${base}-${w}.webp"; done; done'
else
  echo "Ni cwebp ni docker disponible — installez libwebp ou lancez ce script sur la VM." >&2
  exit 1
fi
echo "Terminé : fichiers .webp générés à côté des .jpg."
