@echo off
echo ================================================================
echo RESTAURATION DE LA BASE DE DONNEES CESIZEN
echo ================================================================

echo Suppression des tables existantes...
"C:\xampp-new\mysql\bin\mysql.exe" -u root -e "DROP DATABASE IF EXISTS cesizen; CREATE DATABASE cesizen;"

echo Creation des tables et insertion des donnees...
"C:\xampp-new\mysql\bin\mysql.exe" -u root < create_cesizen_tables.sql

echo ================================================================
echo RESTAURATION TERMINEE !
echo ================================================================
echo Utilisateurs de test crees :
echo - admin@cesizen.com (Administrateur) - mot de passe: password
echo - user@cesizen.com (Utilisateur) - mot de passe: password
echo ================================================================

pause 