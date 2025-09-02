@echo off
echo ========================================
echo    DEMARRAGE CESIZEN BACKEND DEV
echo ========================================
echo.

echo [1/5] Arret des conteneurs existants...
docker-compose down

echo [2/5] Construction des images de developpement...
docker-compose build --no-cache backend-dev

echo [3/5] Demarrage des services de base...
docker-compose up -d mysql phpmyadmin

echo [4/5] Attente du demarrage de MySQL...
timeout /t 15 /nobreak > nul

echo [5/5] Demarrage du backend developpement...
docker-compose --profile dev up -d

echo.
echo ========================================
echo    MODE DEVELOPPEMENT ACTIVE !
echo ========================================
echo.
echo Backend Dev (Hot Reload): http://localhost:8001
echo phpMyAdmin:               http://localhost:8080
echo MySQL:                    localhost:3306
echo.
echo AVANTAGES MODE DEV:
echo - Hot reload automatique
echo - Debug active
echo - Volumes montes pour edition en temps reel
echo - Port 8001 pour eviter les conflits
echo.
echo Appuyez sur une touche pour ouvrir l'API dev...
pause > nul
start http://localhost:8001/api/test

echo.
echo ========================================
echo    COMMANDES UTILES
echo ========================================
echo.
echo Voir les logs en temps reel:
echo - docker-compose logs -f backend-dev
echo.
echo Redemarrer le backend:
echo - docker-compose restart backend-dev
echo.
echo Acceder au conteneur:
echo - docker-compose exec backend-dev bash
echo.
echo Pour arreter: docker-compose down
echo.
pause
