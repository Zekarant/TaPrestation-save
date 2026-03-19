# PowerShell script to generate multiple icon sizes using ImageMagick
# Requires: ImageMagick `magick` to be installed on Windows

$logo = "public/images/logo.png"
$outdir = "public/icons"

if (-not (Test-Path $logo)) {
    Write-Host "Logo non trouvé: $logo" -ForegroundColor Red
    exit 1
}

if (-not (Test-Path $outdir)) { New-Item -Path $outdir -ItemType Directory -Force }

 $sizes = @(48,72,96,144,192,256,384,512)
foreach ($s in $sizes) {
    $dest = "$outdir/icon-${s}x${s}.png"
    & magick $logo -resize ${s}x${s}^ -gravity center -extent ${s}x${s} $dest
    Write-Host "Generated $dest"
}

# Create a 1080x1080 for Play Console feature/launcher (maskable adaptive)
$foreground = "$outdir/icon-512x512.png"
$adaptive = "$outdir/icon-1024x1024.png"
& magick $logo -resize 1024x1024^ -gravity center -extent 1024x1024 $adaptive
Write-Host "Generated $adaptive"
 
# Generate Play Store feature graphic 1024x500
$feature = "$outdir/feature-1024x500.png"
& magick $logo -resize 1024x512^ -gravity center -extent 1024x500 $feature
Write-Host "Generated $feature"

# Also copy assets to playstore/assets (for Play Console)
$playstoreAssets = "playstore/assets"
if (-not (Test-Path $playstoreAssets)) { New-Item -Path $playstoreAssets -ItemType Directory -Force }
Copy-Item -Path "$outdir/*" -Destination $playstoreAssets -Force -Recurse
Write-Host "Copied icon assets to $playstoreAssets"

Write-Host "All icons generated in $outdir"