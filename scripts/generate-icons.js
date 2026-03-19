/**
 * Script de génération d'icônes pour toutes les plateformes
 * Usage: node scripts/generate-icons.js
 * 
 * Prérequis: Placer le logo source (1024x1024 ou plus) dans:
 *   - taprestation/source-logo.png
 */

const sharp = require('sharp');
const fs = require('fs');
const path = require('path');

const SOURCE_LOGO = path.join(__dirname, '..', 'source-logo.png');

// Toutes les tailles nécessaires
const ICONS = {
    // PWA Icons (public/icons/)
    pwa: [
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
        { name: 'icon-maskable-512x512.png', size: 512, maskable: true },
        { name: 'apple-touch-icon.png', size: 180 },
    ],
    
    // iOS App Icons
    ios: [
        { name: 'AppIcon-512@2x.png', size: 1024 },
    ],
    
    // Android mipmap icons
    android: [
        { folder: 'mipmap-mdpi', size: 48 },
        { folder: 'mipmap-hdpi', size: 72 },
        { folder: 'mipmap-xhdpi', size: 96 },
        { folder: 'mipmap-xxhdpi', size: 144 },
        { folder: 'mipmap-xxxhdpi', size: 192 },
    ],
    
    // Web images
    web: [
        { name: 'logo.png', size: 512 },
    ]
};

const PATHS = {
    pwa: path.join(__dirname, '..', 'public', 'icons'),
    ios: path.join(__dirname, '..', 'ios', 'App', 'App', 'Assets.xcassets', 'AppIcon.appiconset'),
    androidRes: path.join(__dirname, '..', 'android', 'app', 'src', 'main', 'res'),
    web: path.join(__dirname, '..', 'public', 'images'),
};

async function generateIcon(sourcePath, outputPath, size, options = {}) {
    const { maskable = false, background = '#ffffff' } = options;
    
    let image = sharp(sourcePath);
    
    if (maskable) {
        // Pour les icônes maskables, ajouter un padding de 10% et fond blanc
        const padding = Math.round(size * 0.1);
        const innerSize = size - (padding * 2);
        
        image = await sharp(sourcePath)
            .resize(innerSize, innerSize, { fit: 'contain', background })
            .extend({
                top: padding,
                bottom: padding,
                left: padding,
                right: padding,
                background
            });
    } else {
        image = image.resize(size, size, { fit: 'contain', background });
    }
    
    await image.png().toFile(outputPath);
    console.log(`✅ Généré: ${outputPath}`);
}

async function generateAndroidForeground(sourcePath, outputPath, size) {
    // Android adaptive icons: foreground doit être 108dp avec safe zone de 66dp
    // On met le logo dans la safe zone (environ 61% de l'image)
    const foregroundSize = size;
    const logoSize = Math.round(size * 0.61);
    const padding = Math.round((size - logoSize) / 2);
    
    await sharp(sourcePath)
        .resize(logoSize, logoSize, { fit: 'contain', background: { r: 0, g: 0, b: 0, alpha: 0 } })
        .extend({
            top: padding,
            bottom: padding,
            left: padding,
            right: padding,
            background: { r: 0, g: 0, b: 0, alpha: 0 }
        })
        .png()
        .toFile(outputPath);
    
    console.log(`✅ Généré (foreground): ${outputPath}`);
}

async function main() {
    console.log('🎨 Génération des icônes TaPrestation\n');
    
    // Vérifier que le fichier source existe
    if (!fs.existsSync(SOURCE_LOGO)) {
        console.error(`❌ Erreur: Fichier source non trouvé: ${SOURCE_LOGO}`);
        console.log('\n📝 Instructions:');
        console.log('1. Sauvegarde ton logo en PNG (1024x1024 ou plus, carré)');
        console.log(`2. Place-le ici: ${SOURCE_LOGO}`);
        console.log('3. Relance ce script: node scripts/generate-icons.js');
        process.exit(1);
    }
    
    // Obtenir les dimensions de l'image source
    const metadata = await sharp(SOURCE_LOGO).metadata();
    console.log(`📷 Image source: ${metadata.width}x${metadata.height}`);
    
    if (metadata.width < 1024 || metadata.height < 1024) {
        console.warn(`⚠️  Attention: L'image source devrait être au moins 1024x1024 pour une meilleure qualité`);
    }
    
    // Créer les dossiers si nécessaire
    for (const p of Object.values(PATHS)) {
        if (!fs.existsSync(p)) {
            fs.mkdirSync(p, { recursive: true });
        }
    }
    
    console.log('\n📱 PWA Icons...');
    for (const icon of ICONS.pwa) {
        const outputPath = path.join(PATHS.pwa, icon.name);
        await generateIcon(SOURCE_LOGO, outputPath, icon.size, { maskable: icon.maskable });
    }
    
    console.log('\n🍎 iOS Icons...');
    for (const icon of ICONS.ios) {
        const outputPath = path.join(PATHS.ios, icon.name);
        await generateIcon(SOURCE_LOGO, outputPath, icon.size);
    }
    
    console.log('\n🤖 Android Icons...');
    for (const icon of ICONS.android) {
        const folderPath = path.join(PATHS.androidRes, icon.folder);
        if (!fs.existsSync(folderPath)) {
            fs.mkdirSync(folderPath, { recursive: true });
        }
        
        // ic_launcher.png (standard)
        await generateIcon(SOURCE_LOGO, path.join(folderPath, 'ic_launcher.png'), icon.size);
        
        // ic_launcher_round.png (rond)
        await generateIcon(SOURCE_LOGO, path.join(folderPath, 'ic_launcher_round.png'), icon.size);
        
        // ic_launcher_foreground.png (adaptive icon foreground)
        await generateAndroidForeground(SOURCE_LOGO, path.join(folderPath, 'ic_launcher_foreground.png'), icon.size);
    }
    
    console.log('\n🌐 Web Images...');
    for (const icon of ICONS.web) {
        const outputPath = path.join(PATHS.web, icon.name);
        await generateIcon(SOURCE_LOGO, outputPath, icon.size);
    }
    
    console.log('\n✨ Terminé! Toutes les icônes ont été générées.');
    console.log('\n📋 Prochaines étapes:');
    console.log('1. Vérifier les icônes générées');
    console.log('2. Rebuild l\'app Android: cd android && ./gradlew assembleRelease');
    console.log('3. Rebuild l\'app iOS dans Xcode');
    console.log('4. Déployer le site web');
}

main().catch(console.error);
