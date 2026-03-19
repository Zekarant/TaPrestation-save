# Secrets GitHub — Guide d'ajout pour CI (Play Store)

Ce guide explique quels secrets ajouter dans votre dépôt GitHub pour que le workflow `build-android.yml` puisse construire un AAB signé et (optionnellement) le publier.

1) ANDROID_KEYSTORE_BASE64
- Contenu: keystore Android encodée en base64
- Comment générer (Linux/macOS):
  ```bash
  base64 ta-prestation.keystore > keystore.base64
  cat keystore.base64
  ```
- Windows PowerShell:
  ```powershell
  $b64 = [Convert]::ToBase64String([IO.File]::ReadAllBytes('ta-prestation.keystore'))
  $b64 | Out-File -FilePath keystore.base64
  Get-Content keystore.base64
  ```
- Ajoutez la sortie complète dans un secret GitHub nommé `ANDROID_KEYSTORE_BASE64`.

2) ANDROID_KEYSTORE_PASSWORD
- Mot de passe du keystore (set it in GitHub secrets)

3) ANDROID_KEY_ALIAS
- Alias (par ex: `taprestation`)

4) ANDROID_KEY_PASSWORD
- Mot de passe de la clé alias (souvent identique au keystore password)

5) PLAY_STORE_JSON (optionnel pour publication automatique)
- Format: JSON du Service Account (Google Play Console) converti en contenu (plein JSON). Ajoutez-le dans GitHub Secret.
- Exemple: `PLAY_STORE_JSON` => `{ "type": "service_account", "project_id": "..." ... }`

6) Recommandation de sécurité
- Limitez l'accès à ces secrets uniquement aux utilisateurs de confiance.
- Ne téléversez pas la keystore ou le mot de passe dans votre répertoire source.

---

Après avoir ajouté ces secrets, vous pouvez forcer un build en poussant sur `main` ou en lançant manuellement le workflow depuis GitHub Actions pour vérifier l’artefact `app-release.aab`.
