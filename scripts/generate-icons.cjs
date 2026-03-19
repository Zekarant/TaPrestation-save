/**
 * Generate TaPrestation web and PWA branding assets.
 *
 * Branding rules:
 *   - App icons, favicons, PWA icons, and launcher assets all use the same
 *     small-mark source so the user always sees one consistent logo.
 *   - The full-art source remains reserved for larger brand imagery such as
 *     public/images/logo.png.
 */

const sharp = require('sharp');
const fs = require('fs');
const path = require('path');
const { spawnSync } = require('child_process');

const ROOT = path.join(__dirname, '..');
const BRAND_DIR = path.join(ROOT, 'icons');
const PUBLIC_DIR = path.join(ROOT, 'public');
const PUBLIC_ICONS_DIR = path.join(PUBLIC_DIR, 'icons');
const PUBLIC_IMAGES_DIR = path.join(PUBLIC_DIR, 'images');
const FULL_ART_SOURCE_CANDIDATES = [
    path.join(BRAND_DIR, 'logo.png'),
    path.join(BRAND_DIR, 'source-logo-full-art.png'),
    path.join(BRAND_DIR, 'source-logo.png'),
];
const SMALL_MARK_SOURCE = path.join(BRAND_DIR, 'source-logo-small-mark.svg');
const FAVICON_PATH = path.join(PUBLIC_DIR, 'favicon.ico');
const MIRROR_TARGETS = [
    path.join(ROOT, 'android', 'app', 'src', 'main', 'assets', 'public'),
    path.join(ROOT, 'ios', 'App', 'App', 'public'),
];

const APP_ICON_SOURCE = SMALL_MARK_SOURCE;
const BRAND_COLORS = {
    maskableBackground: '#07172f',
};

const ICON_SPECS = [
    { name: 'icon-48x48.png', size: 48 },
    { name: 'icon-72x72.png', size: 72 },
    { name: 'icon-96x96.png', size: 96 },
    { name: 'icon-128x128.png', size: 128 },
    { name: 'icon-144x144.png', size: 144 },
    { name: 'icon-152x152.png', size: 152 },
    { name: 'icon-192x192.png', size: 192 },
    { name: 'icon-256x256.png', size: 256 },
    { name: 'icon-384x384.png', size: 384 },
    { name: 'icon-512x512.png', size: 512 },
    { name: 'icon-1024x1024.png', size: 1024 },
];

function resolveFullArtSource() {
    for (const candidate of FULL_ART_SOURCE_CANDIDATES) {
        if (fs.existsSync(candidate)) {
            return candidate;
        }
    }
    throw new Error(`Full-art source not found. Expected one of: ${FULL_ART_SOURCE_CANDIDATES.join(', ')}`);
}

function ensureDir(dirPath) {
    fs.mkdirSync(dirPath, { recursive: true });
}

function hexToRgba(hex, alpha = 1) {
    const value = hex.replace('#', '');
    const normalized = value.length === 3
        ? value.split('').map((char) => char + char).join('')
        : value;

    return {
        r: parseInt(normalized.slice(0, 2), 16),
        g: parseInt(normalized.slice(2, 4), 16),
        b: parseInt(normalized.slice(4, 6), 16),
        alpha,
    };
}

async function renderIcon(sourcePath, size, outputPath) {
    await sharp(sourcePath)
        .resize(size, size, { fit: 'cover', position: 'centre' })
        .png({ compressionLevel: 9 })
        .toFile(outputPath);
}

async function renderMaskableIcon(sourcePath, outputPath) {
    const base = sharp({
        create: {
            width: 512,
            height: 512,
            channels: 4,
            background: hexToRgba(BRAND_COLORS.maskableBackground),
        },
    });

    const safeArt = await sharp(sourcePath)
        .resize(448, 448, { fit: 'cover', position: 'centre' })
        .png()
        .toBuffer();

    await base
        .composite([{ input: safeArt, gravity: 'center' }])
        .png({ compressionLevel: 9 })
        .toFile(outputPath);
}

async function renderVisibleLogo(sourcePath) {
    await sharp(sourcePath)
        .resize(1024, 1024, { fit: 'cover', position: 'centre' })
        .png({ compressionLevel: 9 })
        .toFile(path.join(PUBLIC_IMAGES_DIR, 'logo.png'));
}

function createRealIcoFromPng(inputPath, outputPath) {
    const candidates = process.platform === 'win32'
        ? [
            ['python', []],
            ['py', ['-3']],
        ]
        : [
            ['python3', []],
            ['python', []],
        ];

    const pythonScript = [
        'from PIL import Image',
        'import sys',
        'img = Image.open(sys.argv[1])',
        "img.save(sys.argv[2], format='ICO', sizes=[(16, 16), (32, 32), (48, 48)])",
    ].join('; ');

    let lastError = null;

    for (const [command, prefixArgs] of candidates) {
        const result = spawnSync(command, [...prefixArgs, '-c', pythonScript, inputPath, outputPath], {
            stdio: 'pipe',
            encoding: 'utf8',
        });

        if (result.status === 0) {
            return true;
        }

        lastError = result.error || new Error(result.stderr || `Failed to create favicon with ${command}`);
    }

    if (lastError) {
        throw lastError;
    }

    return false;
}

async function renderFavicon(sourcePath) {
    const tempPng = `${FAVICON_PATH}.png`;
    await sharp(sourcePath)
        .resize(256, 256, { fit: 'cover', position: 'centre' })
        .png({ compressionLevel: 9 })
        .toFile(tempPng);

    try {
        createRealIcoFromPng(tempPng, FAVICON_PATH);
    } finally {
        if (fs.existsSync(tempPng)) {
            fs.unlinkSync(tempPng);
        }
    }
}

function mirrorPublicAssets() {
    for (const targetRoot of MIRROR_TARGETS) {
        if (!fs.existsSync(targetRoot)) {
            continue;
        }

        fs.cpSync(PUBLIC_ICONS_DIR, path.join(targetRoot, 'icons'), { recursive: true, force: true });
        fs.copyFileSync(FAVICON_PATH, path.join(targetRoot, 'favicon.ico'));
        fs.copyFileSync(path.join(PUBLIC_DIR, 'manifest.webmanifest'), path.join(targetRoot, 'manifest.webmanifest'));
        fs.copyFileSync(path.join(PUBLIC_DIR, 'service-worker.js'), path.join(targetRoot, 'service-worker.js'));

        ensureDir(path.join(targetRoot, 'images'));
        fs.copyFileSync(path.join(PUBLIC_IMAGES_DIR, 'logo.png'), path.join(targetRoot, 'images', 'logo.png'));

        const nestedPublicDir = path.join(targetRoot, 'public');
        if (fs.existsSync(nestedPublicDir)) {
            ensureDir(path.join(nestedPublicDir, 'images'));
            fs.copyFileSync(path.join(PUBLIC_IMAGES_DIR, 'logo.png'), path.join(nestedPublicDir, 'images', 'logo.png'));
        }
    }
}

async function main() {
    console.log('🎨 Generating TaPrestation web/PWA branding assets...\n');

    const fullArtSource = resolveFullArtSource();
    if (!fs.existsSync(APP_ICON_SOURCE)) {
        throw new Error(`App icon source not found: ${APP_ICON_SOURCE}`);
    }

    ensureDir(PUBLIC_ICONS_DIR);
    ensureDir(PUBLIC_IMAGES_DIR);

    const fullArtMeta = await sharp(fullArtSource).metadata();
    console.log(`Full art source: ${path.relative(ROOT, fullArtSource)} (${fullArtMeta.width}x${fullArtMeta.height})`);
    console.log(`App icon source: ${path.relative(ROOT, APP_ICON_SOURCE)}`);

    for (const spec of ICON_SPECS) {
        await renderIcon(APP_ICON_SOURCE, spec.size, path.join(PUBLIC_ICONS_DIR, spec.name));
        console.log(`✅ ${spec.name}`);
    }

    await renderMaskableIcon(APP_ICON_SOURCE, path.join(PUBLIC_ICONS_DIR, 'icon-maskable-512x512.png'));
    console.log('✅ icon-maskable-512x512.png');

    await renderIcon(APP_ICON_SOURCE, 180, path.join(PUBLIC_ICONS_DIR, 'apple-touch-icon.png'));
    console.log('✅ apple-touch-icon.png');

    await renderFavicon(APP_ICON_SOURCE);
    console.log('✅ favicon.ico');

    await renderVisibleLogo(fullArtSource);
    console.log('✅ images/logo.png');

    fs.copyFileSync(fullArtSource, path.join(PUBLIC_ICONS_DIR, 'source-icon.png'));
    fs.copyFileSync(APP_ICON_SOURCE, path.join(PUBLIC_ICONS_DIR, 'icon-source.svg'));
    fs.copyFileSync(APP_ICON_SOURCE, path.join(PUBLIC_ICONS_DIR, 'icon-maskable-source.svg'));
    console.log('✅ source previews');

    mirrorPublicAssets();
    console.log('✅ mirrored updated web assets to native public folders');

    console.log('\n✨ Web/PWA branding assets generated successfully.');
}

main().catch((error) => {
    console.error(`\n❌ ${error.message}`);
    process.exit(1);
});
