# Fastlane guide (optionnel) for Play Store

You can use fastlane to upload AAB quickly. Add a `serviceAccount` JSON file or set as secret. Example steps:

1. Install fastlane locally
2. Configure `fastlane/Appfile` with the package name
3. Configure `fastlane/metadata` folder for Play Store listing
4. Use `fastlane supply --aab path/to/app-release.aab` to upload

This is optional; GitHub Actions action `r0adkll/upload-google-play` is often easier for CI.
