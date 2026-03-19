#!/usr/bin/env bash
# Generate placeholder screenshots for Play Store using ImageMagick
# Requires ImageMagick `magick` to be installed

OUTDIR=playstore/assets/screenshots
mkdir -p "$OUTDIR"

sizes=("1080x1920" "1080x1920" "1080x1920" "1080x1920")
count=1
for s in "${sizes[@]}"; do
  FILE="$OUTDIR/screenshot-$count.png"
  magick -size $s xc:'#f8fafc' -gravity center -pointsize 48 -fill '#111827' -annotate +0+0 "TaPrestation - Screenshot $count" "$FILE"
  echo "Generated $FILE"
  count=$((count+1))
done

echo "All placeholder screenshots generated in $OUTDIR"
