# Configuration Session iOS PWA - Guide de Déploiement

## Problème
Sur iOS, les cookies de session sans `Max-Age` sont supprimés lorsque l'application PWA est fermée, causant des déconnexions intempestives.

## Solution

### 1. Variables d'environnement (.env)

Ajoutez ou modifiez ces variables dans votre fichier `.env` sur le serveur OVH :

```env
# Session longue durée (30 jours = 43200 minutes)
SESSION_LIFETIME=43200

# Important: Définir le domaine pour que les cookies fonctionnent
# avec et sans www (ajuster selon votre domaine)
SESSION_DOMAIN=.taprestation.com

# Cookies sécurisés (requis si HTTPS)
SESSION_SECURE_COOKIE=true

# Ne pas expirer à la fermeture du navigateur
SESSION_EXPIRE_ON_CLOSE=false
```

### 2. Configuration Remember Me
Le `LoginController` a été mis à jour pour forcer automatiquement "Remember Me" sur les PWA mobiles/iOS. Cela crée un cookie persistant avec une durée de 30 jours.

### 3. Headers PWA iOS
Assurez-vous que votre manifest.webmanifest contient :
```json
{
  "display": "standalone"
}
```

Et dans le `<head>` de vos pages :
```html
<meta name="apple-mobile-web-app-capable" content="yes">
```

### 4. Configuration SSL/HTTPS
Les cookies avec `SameSite=Lax` (par défaut) requièrent HTTPS pour fonctionner correctement sur iOS.

### 5. Redirection www
Assurez-vous que toutes les requêtes utilisent le même domaine (avec ou sans www) pour éviter les problèmes de cookies. Ajoutez dans `.htaccess` :

```apache
# Forcer www (ou sans www - choisissez l'un des deux)
RewriteEngine On
RewriteCond %{HTTP_HOST} !^www\. [NC]
RewriteRule ^(.*)$ https://www.%{HTTP_HOST}/$1 [R=301,L]
```

## Vérification

1. **Tester sur iOS Safari** : Ouvrez le site, connectez-vous, fermez complètement Safari, rouvrez
2. **Tester sur iOS PWA** : Ajoutez à l'écran d'accueil, ouvrez, connectez-vous, fermez l'app, rouvrez
3. **Vérifier les cookies** : Dans DevTools > Application > Cookies, vérifiez que les cookies ont un `Max-Age` > 0

## Notes Push Notifications

Les subscriptions push sont stockées dans le champ `push_subscriptions` de la table `users`. 
Tant que l'utilisateur reste connecté (grâce au Remember Me), ses subscriptions restent actives.

Si un utilisateur se déconnecte :
- Les subscriptions restent en base de données
- Au prochain login, elles seront automatiquement réutilisées
- Les notifications seront envoyées sur tous les appareils enregistrés
