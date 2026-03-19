#!/usr/bin/env bash
# Bash script to generate icons for PWA and Play Store using ImageMagick
# Requires: ImageMagick installed (use `magick` on Windows or `convert` on Linux)

LOGO=public/images/logo.png
OUTDIR=public/icons

if [ ! -f "$LOGO" ]; then
  echo "Logo introuvable: $LOGO"
  exit 1
fi

mkdir -p "$OUTDIR"

sizes=(48 72 96 144 192 256 384 512)
for s in "${sizes[@]}"; do
  dest="$OUTDIR/icon-${s}x${s}.png"
  magick "$LOGO" -resize ${s}x${s}^ -gravity center -extent ${s}x${s} "$dest"
  echo "Generated: $dest"
 done

# Create 1024x1024
magick "$LOGO" -resize 1024x1024^ -gravity center -extent 1024x1024 "$OUTDIR/icon-1024x1024.png"

echo "All icons generated in $OUTDIR"
 
# Create Play Store feature graphic 1024x500
magick "$LOGO" -resize 1024x512^ -gravity center -extent 1024x500 "$OUTDIR/feature-1024x500.png"
echo "Generated: $OUTDIR/feature-1024x500.png"

# Copy to playstore/assets
mkdir -p playstore/assets
cp "$OUTDIR"/* playstore/assets/
echo "Copied all icons to playstore/assets"
