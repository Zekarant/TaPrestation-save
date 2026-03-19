#!/usr/bin/env bash
# Non-interactive Bubblewrap initialization script for CI
# Usage: init-noninteractive.sh <manifest_url> <package_name>
set -euo pipefail

if [ "$#" -lt 2 ]; then
  echo "Usage: $0 https://example.com/manifest.webmanifest com.example.taprestation"
  exit 1
fi

MANIFEST_URL="$1"
PACKAGE_ID="$2"
HOST_URL="${MANIFEST_URL%/manifest.webmanifest}"

# Ensure @bubblewrap/cli installed
npm i -g @bubblewrap/cli

# init
bubblewrap init --manifest="$MANIFEST_URL" --host="$HOST_URL" --packageId="$PACKAGE_ID" --name="TaPrestation" --short-name="TaP" --display="standalone" --useGeneratedKey

echo "Bubblewrap init complete; project generated in ./android/"

# Build project
bubblewrap build --output=android

echo "Bubblewrap build done. Check output in android/ directory."