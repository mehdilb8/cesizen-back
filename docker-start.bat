@echo off
echo ========================================
echo    DEMARRAGE CESIZEN BACKEND DOCKER
echo ========================================
echo.

echo [1/5] Arret des conteneurs existants...
docker-compose down

echo [2/5] Construction des images Docker...
docker-compose build --no-cache

echo [3/5] Demarrage des services de base...
docker-compose up -d mysql phpmyadmin

echo [4/5] Attente du demarrage de MySQL...
timeout /t 15 /nobreak > nul

echo [5/5] Demarrage du backend Laravel...
docker-compose up -d backend

echo.
echo ========================================
echo    SERVICES DOCKER DEMARRES !
echo ========================================
echo.
echo Backend Production: http://localhost:8000
echo Backend Dev:        http://localhost:8001 (avec --profile dev)
echo phpMyAdmin:         http://localhost:8080
echo MySQL:              localhost:3306
echo.
echo Identifiants phpMyAdmin:
echo - Utilisateur: root
echo - Mot de passe: root_password
echo.
echo Base de donnees: cesizen
echo.
echo Appuyez sur une touche pour ouvrir l'API...
pause > nul
start http://localhost:8000/api/test

echo.
echo ========================================
echo    COMMANDES UTILES
echo ========================================
echo.
echo Production (port 8000):
echo - docker-compose up -d
echo.
echo Developpement (port 8001):
echo - docker-compose --profile dev up -d
echo.
echo Avec cache Redis:
echo - docker-compose --profile cache up -d
echo.
echo Pour arreter: docker-compose down
echo Pour voir les logs: docker-compose logs -f backend
echo.
pause
