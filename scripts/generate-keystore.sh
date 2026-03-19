#!/bin/bash
# Script de génération du keystore pour TaPrestation
# Exécuter depuis la racine du projet: bash scripts/generate-keystore.sh

echo "=== Génération du keystore TaPrestation ==="
echo ""

KEYSTORE_PATH="android/taprestation-release.keystore"

if [ -f "$KEYSTORE_PATH" ]; then
    echo "ERREUR: Le keystore existe déjà: $KEYSTORE_PATH"
    echo "Supprimez-le d'abord si vous voulez en générer un nouveau."
    exit 1
fi

echo "Création du keystore de release..."
echo "Vous allez devoir répondre à quelques questions."
echo ""

keytool -genkey -v \
    -keystore "$KEYSTORE_PATH" \
    -alias taprestation \
    -keyalg RSA \
    -keysize 2048 \
    -validity 10000 \
    -storepass taprestation2026 \
    -keypass taprestation2026 \
    -dname "CN=TaPrestation, OU=Mobile, O=TaPrestation, L=Paris, ST=IDF, C=FR"

if [ $? -eq 0 ]; then
    echo ""
    echo "=== Keystore créé avec succès! ==="
    echo "Fichier: $KEYSTORE_PATH"
    echo ""
    
    # Extraire le SHA-256
    echo "=== Fingerprint SHA-256 (pour assetlinks.json) ==="
    keytool -list -v -keystore "$KEYSTORE_PATH" -alias taprestation -storepass taprestation2026 2>/dev/null | grep "SHA256:"
    
    echo ""
    echo "IMPORTANT:"
    echo "1. Mettez à jour android/key.properties avec les mots de passe"
    echo "2. Copiez le SHA-256 dans public/.well-known/assetlinks.json"
    echo "3. NE JAMAIS perdre ce keystore - il est impossible de mettre à jour l'app sans!"
    echo "4. Sauvegardez le keystore dans un endroit sûr"
else
    echo "ERREUR lors de la création du keystore"
    exit 1
fi
