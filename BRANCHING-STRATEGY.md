# 🌿 Stratégie de Branches - Cesizen Backend

## 📋 **Vue d'ensemble**

Basé sur l'analyse de votre code, voici une stratégie de branches optimale pour votre projet **Cesizen Backend** - une application Laravel de bien-être et respiration.

## 🎯 **Branches Principales (3 branches)**

### **1. `master`** - Production
- **Rôle** : Code stable en production
- **Protection** : ✅ Requiert PR + Tests + Review
- **Merge** : Seulement depuis `develop` ou `hotfix/*`
- **Déploiement** : Automatique en production

### **2. `develop`** - Intégration
- **Rôle** : Code en cours de développement
- **Protection** : ✅ Requiert Tests
- **Merge** : Depuis `feature/*`, `bugfix/*`
- **Déploiement** : Automatique en staging

### **3. `maintenance`** - Maintenance
- **Rôle** : Corrections de bugs critiques
- **Protection** : ✅ Requiert Tests
- **Merge** : Vers `master` et `develop`
- **Utilisation** : Bugs critiques, sécurité

## 🌱 **Branches de Fonctionnalités (4 branches)**

### **4. `feature/auth-system`** - Système d'authentification
- **Fonctionnalités** : Login, Register, Logout, Sanctum
- **Modèles** : Utilisateur, Role, PersonalAccessToken
- **Contrôleurs** : AuthController, UserController
- **Tests** : Tests d'authentification

### **5. `feature/breathing-exercises`** - Exercices de respiration
- **Fonctionnalités** : CRUD exercices, favoris, sessions
- **Modèles** : ExerciceRespiration, SessionRespiration, FavoriExercice
- **Contrôleurs** : ExerciceRespirationController
- **Tests** : Tests des exercices et sessions

### **6. `feature/content-management`** - Gestion de contenu
- **Fonctionnalités** : Articles, pages, blog
- **Modèles** : ContenuInformation
- **Contrôleurs** : ContenuInformationController
- **Tests** : Tests de gestion de contenu

### **7. `feature/admin-dashboard`** - Tableau de bord admin
- **Fonctionnalités** : Statistiques, gestion utilisateurs, monitoring
- **Contrôleurs** : AdminController
- **Middleware** : AdminMiddleware
- **Tests** : Tests d'administration

## 🐛 **Branches de Corrections (2 branches)**

### **8. `bugfix/`** - Corrections de bugs
- **Format** : `bugfix/description-courte`
- **Exemples** :
  - `bugfix/fix-user-registration-validation`
  - `bugfix/fix-admin-middleware-permissions`
  - `bugfix/fix-database-connection-issues`

### **9. `hotfix/`** - Corrections critiques
- **Format** : `hotfix/description-courte`
- **Exemples** :
  - `hotfix/security-vulnerability-fix`
  - `hotfix/critical-api-endpoint-fix`
  - `hotfix/database-performance-issue`

## 🔄 **Workflow de Développement**

### **Développement de fonctionnalités**
```bash
# 1. Créer une branche feature
git checkout -b feature/auth-system develop

# 2. Développer et commiter
git add .
git commit -m "feat: Add user authentication system"

# 3. Pousser et créer PR
git push origin feature/auth-system
# Créer Pull Request vers develop
```

### **Correction de bugs**
```bash
# 1. Créer une branche bugfix
git checkout -b bugfix/fix-validation-errors develop

# 2. Corriger et commiter
git add .
git commit -m "fix: Resolve validation errors in user registration"

# 3. Pousser et créer PR
git push origin bugfix/fix-validation-errors
# Créer Pull Request vers develop
```

### **Correction critique (hotfix)**
```bash
# 1. Créer une branche hotfix depuis master
git checkout -b hotfix/security-fix master

# 2. Corriger et commiter
git add .
git commit -m "hotfix: Fix critical security vulnerability"

# 3. Pousser et créer PRs
git push origin hotfix/security-fix
# Créer PR vers master ET develop
```

## 📊 **Règles de Merge**

### **Feature → Develop**
- ✅ Tests passent
- ✅ Code review approuvé
- ✅ Pas de conflits
- ✅ Documentation mise à jour

### **Develop → Master**
- ✅ Tous les tests passent
- ✅ Code review approuvé
- ✅ Tests d'intégration OK
- ✅ Déploiement staging réussi

### **Hotfix → Master + Develop**
- ✅ Tests critiques passent
- ✅ Code review approuvé
- ✅ Déploiement d'urgence testé

## 🚀 **Déploiement par Branche**

| Branche | Environnement | Déploiement | Tests |
|---------|---------------|-------------|-------|
| `master` | Production | Automatique | ✅ Tous |
| `develop` | Staging | Automatique | ✅ Intégration |
| `feature/*` | Local | Manuel | ✅ Unitaires |
| `bugfix/*` | Local | Manuel | ✅ Unitaires |
| `hotfix/*` | Production | Automatique | ✅ Critiques |

## 🛡️ **Protection des Branches**

### **Master**
```yaml
# GitHub Settings > Branches > master
- Require pull request reviews before merging
- Require status checks to pass before merging
- Require branches to be up to date before merging
- Restrict pushes that create files
- Allow force pushes: ❌
- Allow deletions: ❌
```

### **Develop**
```yaml
# GitHub Settings > Branches > develop
- Require status checks to pass before merging
- Require branches to be up to date before merging
- Allow force pushes: ❌
- Allow deletions: ❌
```

## 📝 **Conventions de Nommage**

### **Branches**
- `feature/` : Nouvelles fonctionnalités
- `bugfix/` : Corrections de bugs
- `hotfix/` : Corrections critiques
- `maintenance/` : Maintenance générale

### **Commits**
- `feat:` : Nouvelle fonctionnalité
- `fix:` : Correction de bug
- `docs:` : Documentation
- `style:` : Formatage du code
- `refactor:` : Refactoring
- `test:` : Tests
- `chore:` : Maintenance

## 🎯 **Avantages de cette Stratégie**

- ✅ **Séparation claire** des responsabilités
- ✅ **Déploiement sécurisé** en production
- ✅ **Développement parallèle** des fonctionnalités
- ✅ **Gestion des urgences** avec hotfix
- ✅ **Tests automatisés** à chaque étape
- ✅ **Code review** obligatoire pour la production
- ✅ **Traçabilité** complète des changements

---

**🌿 Cette stratégie de branches vous permettra de développer de manière professionnelle et sécurisée !**
