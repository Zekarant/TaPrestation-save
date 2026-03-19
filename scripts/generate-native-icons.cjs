/**
 * Script de génération d'icônes pour Android et iOS natifs
 */

const sharp = require('sharp');
const fs = require('fs');
const path = require('path');

const SOURCE = path.join(__dirname, '..', 'source-logo.png');
const ANDROID_RES = path.join(__dirname, '..', 'android', 'app', 'src', 'main', 'res');
const IOS_ASSETS = path.join(__dirname, '..', 'ios', 'App', 'App', 'Assets.xcassets', 'AppIcon.appiconset');
const WEB_IMAGES = path.join(__dirname, '..', 'public', 'images');

// Android mipmap sizes (dp * scale)
const ANDROID_SIZES = [
    { folder: 'mipmap-mdpi', size: 48 },
    { folder: 'mipmap-hdpi', size: 72 },
    { folder: 'mipmap-xhdpi', size: 96 },
    { folder: 'mipmap-xxhdpi', size: 144 },
    { folder: 'mipmap-xxxhdpi', size: 192 },
];

async function generateAndroidIcons() {
    console.log('\n🤖 Android Icons...');
    
    for (const { folder, size } of ANDROID_SIZES) {
        const folderPath = path.join(ANDROID_RES, folder);
        
        if (!fs.existsSync(folderPath)) {
            fs.mkdirSync(folderPath, { recursive: true });
        }
        
        // ic_launcher.png
        await sharp(SOURCE)
            .resize(size, size, { fit: 'contain', background: { r: 255, g: 255, b: 255, alpha: 1 } })
            .png()
            .toFile(path.join(folderPath, 'ic_launcher.png'));
        console.log(`  ✅ ${folder}/ic_launcher.png (${size}x${size})`);
        
        // ic_launcher_round.png
        await sharp(SOURCE)
            .resize(size, size, { fit: 'contain', background: { r: 255, g: 255, b: 255, alpha: 1 } })
            .png()
            .toFile(path.join(folderPath, 'ic_launcher_round.png'));
        console.log(`  ✅ ${folder}/ic_launcher_round.png (${size}x${size})`);
        
        // ic_launcher_foreground.png (for adaptive icons - logo at 66% with padding)
        const foregroundSize = Math.round(size * 1.5); // Adaptive icon foreground is 108dp, icon is 48dp base
        const logoSize = Math.round(foregroundSize * 0.6);
        const padding = Math.round((foregroundSize - logoSize) / 2);
        
        await sharp(SOURCE)
            .resize(logoSize, logoSize, { fit: 'contain', background: { r: 0, g: 0, b: 0, alpha: 0 } })
            .extend({
                top: padding,
                bottom: padding,
                left: padding,
                right: padding,
                background: { r: 0, g: 0, b: 0, alpha: 0 }
            })
            .png()
            .toFile(path.join(folderPath, 'ic_launcher_foreground.png'));
        console.log(`  ✅ ${folder}/ic_launcher_foreground.png (${foregroundSize}x${foregroundSize})`);
    }
}

async function generateIOSIcons() {
    console.log('\n🍎 iOS Icons...');
    
    if (!fs.existsSync(IOS_ASSETS)) {
        fs.mkdirSync(IOS_ASSETS, { recursive: true });
    }
    
    // iOS App Store icon (1024x1024)
    await sharp(SOURCE)
        .resize(1024, 1024, { fit: 'contain', background: { r: 255, g: 255, b: 255, alpha: 1 } })
        .png()
        .toFile(path.join(IOS_ASSETS, 'AppIcon-512@2x.png'));
    console.log('  ✅ AppIcon-512@2x.png (1024x1024)');
    
    // Contents.json for Xcode
    const contents = {
        images: [
            {
                filename: 'AppIcon-512@2x.png',
                idiom: 'universal',
                platform: 'ios',
                size: '1024x1024'
            }
        ],
        info: {
            author: 'xcode',
            version: 1
        }
    };
    
    fs.writeFileSync(
        path.join(IOS_ASSETS, 'Contents.json'),
        JSON.stringify(contents, null, 2)
    );
    console.log('  ✅ Contents.json updated');
}

async function generateWebImages() {
    console.log('\n🌐 Web Images...');
    
    if (!fs.existsSync(WEB_IMAGES)) {
        fs.mkdirSync(WEB_IMAGES, { recursive: true });
    }
    
    // logo.png (512x512)
    await sharp(SOURCE)
        .resize(512, 512, { fit: 'contain', background: { r: 255, g: 255, b: 255, alpha: 1 } })
        .png()
        .toFile(path.join(WEB_IMAGES, 'logo.png'));
    console.log('  ✅ logo.png (512x512)');
}

async function main() {
    console.log('🎨 Génération des icônes natives TaPrestation');
    
    if (!fs.existsSync(SOURCE)) {
        console.error(`❌ Fichier source non trouvé: ${SOURCE}`);
        process.exit(1);
    }
    
    const meta = await sharp(SOURCE).metadata();
    console.log(`📷 Source: ${meta.width}x${meta.height}`);
    
    await generateAndroidIcons();
    await generateIOSIcons();
    await generateWebImages();
    
    console.log('\n✨ Terminé!');
    console.log('\n📋 Prochaines étapes:');
    console.log('   • Android: cd android && ./gradlew clean assembleRelease');
    console.log('   • iOS: Ouvrir Xcode et rebuild');
    console.log('   • Web: Déployer les fichiers');
}

main().catch(console.error);
