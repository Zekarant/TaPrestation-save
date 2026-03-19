@echo off
REM Script de build Android Release pour TaPrestation
REM Usage: Depuis la racine du projet, double-cliquer ou exécuter: scripts\build-android-release.bat

echo === Build TaPrestation Android Release ===
echo.

REM Vérifier que le keystore existe
if not exist "android\taprestation-release.keystore" (
    echo ERREUR: Keystore non trouvé!
    echo Exécutez d'abord: bash scripts/generate-keystore.sh
    echo Ou générez manuellement le keystore dans android/
    pause
    exit /b 1
)

REM Vérifier key.properties
findstr "REPLACE_WITH" "android\key.properties" >nul 2>&1
if %errorlevel%==0 (
    echo ERREUR: android/key.properties contient encore des valeurs placeholder!
    echo Mettez à jour les mots de passe dans android/key.properties
    pause
    exit /b 1
)

echo [1/3] Synchronisation Capacitor...
call npx cap sync android
if %errorlevel% neq 0 (
    echo ERREUR lors de la synchronisation Capacitor
    pause
    exit /b 1
)

echo.
echo [2/3] Build AAB Release...
cd android
call gradlew.bat bundleRelease
if %errorlevel% neq 0 (
    echo ERREUR lors du build
    cd ..
    pause
    exit /b 1
)
cd ..

echo.
echo [3/3] Build terminé!
echo.
echo Le fichier AAB se trouve dans:
echo   android\app\build\outputs\bundle\release\app-release.aab
echo.
echo Prochaine étape: uploadez ce fichier sur Google Play Console
echo.
pause
