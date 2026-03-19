#!/usr/bin/env bash
# Script to init Bubblewrap TWA project (replace example.com with your host)
if [ -z "$1" ]; then
  echo "Usage: $0 taprestation.com OR $0 https://taprestation.com/manifest.webmanifest"
  exit 1
fi

# Accept domain or full manifest URL
if [[ "$1" == http* ]]; then
  MANIFEST_URL=$1
else
  DOMAIN=$1
  MANIFEST_URL="https://$DOMAIN/manifest.webmanifest"
fi

PACKAGE_NAME="com.taprestation.app"
# init project with sensible defaults
bubblewrap init --manifest=$MANIFEST_URL --name="TaPrestation" --short-name="TaP" --host="${MANIFEST_URL%/manifest.webmanifest}" --packageId=$PACKAGE_NAME

echo "Bubblewrap project initialized. Open android project in Android Studio to build & sign."
echo "Note: package name set to $PACKAGE_NAME. Update to your own if necessary."