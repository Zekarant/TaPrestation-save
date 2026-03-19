# Play Store Publication — Checklist pour TaPrestation

## 1) Type de packaging recommandé
- TWA (Trusted Web Activity) via Bubblewrap → recommandé si vous avez un site PWA en HTTPS.
- Capacitor / Cordova → si vous voulez une App native qui embarque le site dans une WebView.

## 2) Ce que j’ai préparé dans le projet
- `public/manifest.webmanifest` ✅ (déjà présent)
- `public/service-worker.js` ✅ (déjà présent)
- `twa/twa-manifest.json` (modèle) ✅
- Scripts d’aide pour générer icônes (`scripts/generate-icons.ps1`, `scripts/generate-icons.sh`) ✅
- `playstore/PLAY_STORE_CHECKLIST.md` (ce fichier) ✅

## 3) Pre-requis côté hébergement
- Le site doit être servi via HTTPS sur un domaine public (e.g. `https://app.monsite.com`).
- La page d'accueil doit utiliser le `manifest.webmanifest` et `service-worker.js`.

## 4) Icônes requises et captures d'écran
- Icônes Android (launcher + adaptive): 512x512 (Play icon), 192x192 (PWA), adaptative (foreground/background) et logo pour notifications.
- Captures d’écran Play Store requises:
  - 2-8 screenshots minimum. Taille standard : 1080 × 1920 (portrait), 1920 × 1080 (landscape). Format PNG/JPEG.

## 5) Données Play Console à préparer
- Nom de l’application (titre), court + long
- Description courte et longue
- Images: screenshots, feature graphic (1024×500), promo graphic (optional), icon (512×512)
- Politique de confidentialité (URL) (exigé si vous collectez des données)
- E-mail support, site web, numéro de téléphone (optionnel)
- Compte développeur Google (25 USD frais unique)

## 6) Signature / clé
- Générer keystore Android (si non fait) :
  ```powershell
  keytool -genkey -v -keystore ta-prestation.keystore -alias taprestation -keyalg RSA -keysize 2048 -validity 10000
  ```
- Conserver la clé privée dans un endroit sûr; elle sera requise pour signer le bundle.

## 7) Build & Publish (TWA via Bubblewrap)
- Installer Bubblewrap : `npm i -g @bubblewrap/cli`
- Initialiser :
  ```powershell
  bubblewrap init --manifest=https://example.com/manifest.webmanifest
  ```
- Générer le projet Android :
  ```powershell
  bubblewrap build
  ```
- Signer et générer AAB :
  ```powershell
  jarsigner -keystore ta-prestation.keystore path/to/app.aab alias
  ```
  (ou suivre les étapes avec Android Studio / key.properties pour signature automatique)

## 8) Tests
- Tester sur un appareil Android via `adb install` (ou `bundletool` pour AAB testing).
- Tester que username/password et push notifications fonctionnent, que les fichiers sw/manifest sont bien servis sur la production HTTPS.

## 9) Publication
- Ouvrir Google Play Console → Créer App → Remplir les listes, télécharger AAB signé, fournir assets + politique de confidentialité, remplir store listing et publiez.

---

Si vous voulez, je peux :
1) Générer (dans le repo) un ensemble d’icônes à partir de `public/images/logo.png` (script d’automatisation),
2) Remplir `twa/twa-manifest.json` et `README-PUBLISH-PLAYSTORE.md` avec commandes exactes (je l’ai déjà commencé),
3) Vous aider à lancer les commandes Bubblewrap sur votre machine (je ne peux pas exécuter `bubblewrap` ici, mais je fournirai les commandes pas à pas),
4) Aider à créer le fichier `key.properties` et la configuration `android/` si vous choisissez Capacitor.

Dites-moi quelle option vous préférez (TWA/Bubblewrap ou Capacitor) et si vous avez un domaine HTTPS public à utiliser (ex: `https://app.monsite.com`).
