# TWA + Bubblewrap — Guide rapide

Ce README explique comment partir du manifest PWA existant pour créer un Android AAB via Bubblewrap.

## Pré-requis
- Node.js
- Java JDK
- Android Studio ou Android SDK
- Bubblewrap

## Installer Bubblewrap
```bash
npm i -g @bubblewrap/cli
```

## Étapes (exemple)
1. Assurez-vous que `manifest.webmanifest` est accessible via `https://example.com/manifest.webmanifest` et que le service worker est actif.
2. Générez les icônes si nécessaire :
   - Windows : `pwsh.exe scripts/generate-icons.ps1` (nécessite ImageMagick installed)
   - macOS / Linux : `./scripts/generate-icons.sh`
3. Initialiser le projet TWA :
```bash
bubblewrap init --manifest=https://example.com/manifest.webmanifest --host=example.com --name="TaPrestation" --short-name="TaP"
```
- Durant `init` Bubblewrap : vous allez répondre à des prompts (package name, keystore path, etc.)

4. Build Android :
```bash
bubblewrap build
```

5. Ouvrez le projet (généré dans `./android/`) dans Android Studio pour générer puis signer le `bundle.aab`.

6. Signez le AAB et téléversez-le sur Google Play Console.

## Notes
- Bubblewrap générera un projet Android qui utilise le service worker et la PWA manifest.
- Assurez-vous que le domaine `host` est le même que la `start_url` dans `manifest.webmanifest`.
- Si votre site n’est pas encore public, vous devrez configurer une URL HTTPS par hébergement (AWS, Vercel, Netlify, GitHub Pages with custom domain?)

---

Si vous fournissez votre domaine HTTPS, je peux préparer un `bubblewrap` init script et un `twa/twa-manifest.json` complété avec votre `host` et `fullName`.