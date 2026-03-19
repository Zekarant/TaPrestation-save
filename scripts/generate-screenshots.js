/**
 * Script de génération de screenshots pour les stores
 * 
 * Prérequis: npm install sharp (déjà dans devDependencies)
 * Usage: node scripts/generate-screenshots.js
 * 
 * Ce script crée des screenshots placeholder avec les bonnes dimensions.
 * Remplacez-les par de vrais screenshots de l'app avant la soumission.
 */

import sharp from 'sharp';
import { mkdirSync, existsSync } from 'fs';
import { join } from 'path';

const SCREENSHOTS_DIR = join(process.cwd(), 'public', 'images', 'screenshots');
const PLAYSTORE_DIR = join(process.cwd(), 'playstore', 'assets');

// Créer les répertoires
[SCREENSHOTS_DIR, PLAYSTORE_DIR].forEach(dir => {
    if (!existsSync(dir)) {
        mkdirSync(dir, { recursive: true });
    }
});

// Couleurs TaPrestation
const ORANGE = '#f97316';
const WHITE = '#ffffff';
const DARK = '#1f2937';

async function createPlaceholderImage(width, height, text, outputPath, bgColor = ORANGE) {
    // Escape XML entities
    const safeText = text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    const svg = `
    <svg width="${width}" height="${height}" xmlns="http://www.w3.org/2000/svg">
        <rect width="100%" height="100%" fill="${bgColor}"/>
        <rect x="40" y="40" width="${width - 80}" height="${height - 80}" rx="20" fill="white" opacity="0.15"/>
        <text x="50%" y="45%" font-family="Arial, sans-serif" font-size="${Math.floor(width / 15)}" fill="white" text-anchor="middle" font-weight="bold">TaPrestation</text>
        <text x="50%" y="55%" font-family="Arial, sans-serif" font-size="${Math.floor(width / 25)}" fill="white" text-anchor="middle" opacity="0.9">${safeText}</text>
        <text x="50%" y="90%" font-family="Arial, sans-serif" font-size="${Math.floor(width / 35)}" fill="white" text-anchor="middle" opacity="0.6">${width}x${height} - Remplacer par vrai screenshot</text>
    </svg>`;

    await sharp(Buffer.from(svg))
        .png()
        .toFile(outputPath);
    
    console.log(`✓ Créé: ${outputPath}`);
}

async function main() {
    console.log('=== Génération des assets pour les stores ===\n');

    // PWA Screenshots (référencés dans manifest.webmanifest)
    console.log('--- Screenshots PWA (1080x1920) ---');
    await createPlaceholderImage(1080, 1920, "Page d'accueil", join(SCREENSHOTS_DIR, 'screenshot-home.png'));
    await createPlaceholderImage(1080, 1920, 'Liste des services', join(SCREENSHOTS_DIR, 'screenshot-services.png'));
    await createPlaceholderImage(1080, 1920, "Réservation d'un service", join(SCREENSHOTS_DIR, 'screenshot-booking.png'));

    // Play Store Screenshots
    console.log('\n--- Screenshots Play Store (1080x1920) ---');
    await createPlaceholderImage(1080, 1920, "Accueil", join(PLAYSTORE_DIR, 'screenshot-1-home.png'));
    await createPlaceholderImage(1080, 1920, "Recherche de services", join(PLAYSTORE_DIR, 'screenshot-2-services.png'));
    await createPlaceholderImage(1080, 1920, "Détail prestataire", join(PLAYSTORE_DIR, 'screenshot-3-provider.png'));
    await createPlaceholderImage(1080, 1920, "Réservation", join(PLAYSTORE_DIR, 'screenshot-4-booking.png'));
    await createPlaceholderImage(1080, 1920, "Location matériel", join(PLAYSTORE_DIR, 'screenshot-5-equipment.png'));
    await createPlaceholderImage(1080, 1920, "Food & Livraison", join(PLAYSTORE_DIR, 'screenshot-6-food.png'));

    // Feature Graphic (Play Store obligatoire: 1024x500)
    console.log('\n--- Feature Graphic Play Store (1024x500) ---');
    await createPlaceholderImage(1024, 500, 'Services & Prestataires près de chez vous', join(PLAYSTORE_DIR, 'feature-graphic.png'));

    // Hi-res Icon (Play Store: 512x512)
    console.log('\n--- Icône Hi-Res Play Store (512x512) ---');
    const sourceIcon = join(process.cwd(), 'public', 'icons', 'icon-512x512.png');
    if (existsSync(sourceIcon)) {
        await sharp(sourceIcon)
            .resize(512, 512)
            .png()
            .toFile(join(PLAYSTORE_DIR, 'icon-512x512.png'));
        console.log(`✓ Copié: ${join(PLAYSTORE_DIR, 'icon-512x512.png')}`);
    } else {
        await createPlaceholderImage(512, 512, 'Icône', join(PLAYSTORE_DIR, 'icon-512x512.png'));
    }

    console.log('\n=== Terminé! ===');
    console.log('\nIMPORTANT: Ces images sont des placeholders.');
    console.log('Remplacez-les par de vrais screenshots de votre app avant la soumission.');
    console.log('\nDimensions requises:');
    console.log('  - Screenshots Play Store: 1080x1920 (min 2, max 8)');
    console.log('  - Feature Graphic: 1024x500 (obligatoire)');
    console.log('  - Icône Hi-Res: 512x512');
    console.log('  - Screenshots App Store: voir App Store Connect pour les dimensions exactes');
}

main().catch(console.error);
