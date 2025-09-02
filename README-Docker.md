# 🐳 Cesizen Backend - Docker

Ce projet Laravel est maintenant configuré pour fonctionner avec Docker Desktop, avec des modes **Production** et **Développement**.

## 📋 Prérequis

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) installé et démarré
- Windows 10/11 ou macOS
- Au moins 4GB de RAM disponible

## 🚀 Démarrage rapide

### Option 1: Scripts automatiques (Recommandé)

#### **Production (Port 8000)**
```bash
# Double-cliquez sur le fichier
docker-start.bat
```

#### **Développement (Port 8001)**
```bash
# Double-cliquez sur le fichier
docker-dev.bat
```

### Option 2: Commandes manuelles

#### **Mode Production**
```bash
# 1. Construire les images
docker-compose build

# 2. Démarrer les services
docker-compose up -d

# 3. Vérifier le statut
docker-compose ps
```

#### **Mode Développement**
```bash
# 1. Construire les images de dev
docker-compose build backend-dev

# 2. Démarrer avec le profil dev
docker-compose --profile dev up -d

# 3. Vérifier le statut
docker-compose ps
```

#### **Mode avec Cache Redis**
```bash
# Démarrer avec Redis et Queue Worker
docker-compose --profile cache up -d
```

## 🌐 Services disponibles

| Service | URL | Port | Description | Mode |
|---------|-----|------|-------------|------|
| **Backend Production** | http://localhost:8000 | 8000 | API Laravel optimisée | Production |
| **Backend Dev** | http://localhost:8001 | 8001 | API Laravel avec hot reload | Développement |
| **phpMyAdmin** | http://localhost:8080 | 8080 | Gestion base de données | Tous |
| **MySQL** | localhost | 3307 | Base de données | Tous |
| **Redis** | localhost | 6379 | Cache et queues | Cache |

## 🔐 Identifiants par défaut

### phpMyAdmin
- **Utilisateur:** `root`
- **Mot de passe:** `root_password`
- **Base de données:** `cesizen`

### Utilisateur admin Laravel
- **Email:** `admin@cesizen.fr`
- **Mot de passe:** `password`

## 📁 Structure des fichiers Docker

```
cesizen-backend/
├── docker-compose.yml          # Orchestration des services
├── Dockerfile                  # Image Laravel Production
├── Dockerfile.dev             # Image Laravel Développement
├── .dockerignore              # Fichiers exclus du build
├── docker.env                 # Variables d'environnement Docker
├── database/init/01-init.sql  # Initialisation de la base
├── docker-start.bat           # Script Production
├── docker-dev.bat             # Script Développement
└── README-Docker.md           # Ce fichier
```

## 🛠️ Commandes utiles

### Gestion des conteneurs
```bash
# Démarrer tous les services
docker-compose up -d

# Démarrer en mode développement
docker-compose --profile dev up -d

# Démarrer avec Redis
docker-compose --profile cache up -d

# Arrêter les services
docker-compose down

# Redémarrer un service spécifique
docker-compose restart backend
docker-compose restart backend-dev

# Voir les logs en temps réel
docker-compose logs -f backend
docker-compose logs -f backend-dev
```

### Base de données
```bash
# Accéder au conteneur MySQL
docker-compose exec mysql mysql -u root -p cesizen

# Sauvegarder la base
docker-compose exec mysql mysqldump -u root -p cesizen > backup.sql

# Restaurer la base
docker-compose exec -T mysql mysql -u root -p cesizen < backup.sql
```

### Application Laravel
```bash
# Exécuter des commandes Artisan (Production)
docker-compose exec backend php artisan migrate
docker-compose exec backend php artisan key:generate
docker-compose exec backend php artisan cache:clear

# Exécuter des commandes Artisan (Développement)
docker-compose exec backend-dev php artisan migrate
docker-compose exec backend-dev php artisan key:generate
docker-compose exec backend-dev php artisan cache:clear

# Accéder au shell du conteneur
docker-compose exec backend bash
docker-compose exec backend-dev bash
```

## 🔧 Configuration

### Variables d'environnement
Les variables d'environnement sont définies dans `docker.env` :
- `DB_HOST=mysql` (nom du service Docker)
- `DB_DATABASE=cesizen`
- `DB_USERNAME=cesizen_user`
- `DB_PASSWORD=cesizen_password`

### Ports
- **8000:** Laravel API Production
- **8001:** Laravel API Développement
- **8080:** phpMyAdmin
- **3307:** MySQL (Docker)
- **6379:** Redis (optionnel)

## 📊 Base de données

### Tables créées automatiquement
- `role` - Rôles utilisateurs
- `utilisateur` - Utilisateurs du système
- `contenu_information` - Contenus de l'application
- `exercice_respiration` - Exercices de respiration
- `session_respiration` - Sessions utilisateurs
- `favori_exercice` - Exercices favoris
- `statistique_mensuelle` - Statistiques utilisateurs
- `personal_access_tokens` - Tokens d'authentification
- `sessions` - Sessions Laravel
- `cache` - Cache de l'application
- `jobs` - Files d'attente

### Données d'exemple
- 2 rôles : `utilisateur` et `administrateur`
- 1 utilisateur admin : `admin@cesizen.fr`
- 2 contenus d'exemple
- 2 exercices de respiration

## 🧪 Tests de l'API

### 1. Test de base
```bash
# Production
GET http://localhost:8000/api/test

# Développement
GET http://localhost:8001/api/test
```

### 2. Contenus publics
```bash
# Production
GET http://localhost:8000/api/contenus
GET http://localhost:8000/api/contenus/1

# Développement
GET http://localhost:8001/api/contenus
GET http://localhost:8001/api/contenus/1
```

### 3. Authentification
```bash
# Production
POST http://localhost:8000/api/login
Content-Type: application/json

{
  "email": "admin@cesizen.fr",
  "mot_de_passe": "password"
}

# Développement
POST http://localhost:8001/api/login
Content-Type: application/json

{
  "email": "admin@cesizen.fr",
  "mot_de_passe": "password"
}
```

## 🚨 Dépannage

### Problème de ports
Si les ports sont déjà utilisés :
```bash
# Voir les processus utilisant les ports
netstat -ano | findstr :8000
netstat -ano | findstr :8001
netstat -ano | findstr :8080
netstat -ano | findstr :3307

# Modifier les ports dans docker-compose.yml
```

### Problème de permissions
```bash
# Réinitialiser les permissions (Production)
docker-compose exec backend chown -R www-data:www-data /var/www/html
docker-compose exec backend chmod -R 775 storage bootstrap/cache

# Réinitialiser les permissions (Développement)
docker-compose exec backend-dev chown -R www-data:www-data /var/www/html
docker-compose exec backend-dev chmod -R 775 storage bootstrap/cache
```

### Problème de base de données
```bash
# Recréer la base
docker-compose down -v
docker-compose up -d
```

### Logs détaillés
```bash
# Voir tous les logs
docker-compose logs

# Logs d'un service spécifique
docker-compose logs mysql
docker-compose logs backend
docker-compose logs backend-dev
docker-compose logs phpmyadmin
```

## 🔄 Mise à jour

### Mettre à jour le code
```bash
# Arrêter les services
docker-compose down

# Mettre à jour le code source
git pull origin main

# Reconstruire et redémarrer
docker-compose build --no-cache
docker-compose up -d
```

### Mettre à jour les dépendances
```bash
# Mettre à jour Composer (Production)
docker-compose exec backend composer update

# Mettre à jour Composer (Développement)
docker-compose exec backend-dev composer update

# Mettre à jour les migrations
docker-compose exec backend php artisan migrate
docker-compose exec backend-dev php artisan migrate
```

## 📱 Utilisation avec Postman

### Collection Postman
1. Importez la collection "Cesizen Backend API"
2. Configurez l'environnement avec `base_url: http://localhost:8000/api` (Production)
3. Ou `base_url: http://localhost:8001/api` (Développement)
4. Testez les routes dans l'ordre recommandé

### Variables d'environnement Postman
```json
{
  "base_url_prod": "http://localhost:8000/api",
  "base_url_dev": "http://localhost:8001/api",
  "auth_token": "",
  "admin_token": ""
}
```

## 🎯 Avantages Docker

- ✅ **Environnement isolé** - Pas de conflit avec votre système
- ✅ **Reproductibilité** - Fonctionne sur tous les ordinateurs
- ✅ **Facilité de déploiement** - Un seul fichier pour tout
- ✅ **Gestion des dépendances** - Tout est inclus dans l'image
- ✅ **Scalabilité** - Facile d'ajouter des services
- ✅ **Mode développement** - Hot reload et debug
- ✅ **Mode production** - Optimisé et sécurisé

## 🆘 Support

Si vous rencontrez des problèmes :

1. Vérifiez que Docker Desktop est démarré
2. Consultez les logs : `docker-compose logs`
3. Redémarrez les services : `docker-compose restart`
4. Recréez l'environnement : `docker-compose down -v && docker-compose up -d`

---

**🎉 Votre backend Cesizen est maintenant prêt avec Docker en mode Production ET Développement !**
