-- ================================================================
-- SCRIPT DE CRÉATION DES TABLES POUR CESIZEN
-- Analysé depuis les modèles Laravel
-- ================================================================

-- Utiliser la base de données cesizen
USE cesizen;

-- ================================================================
-- 1. TABLE ROLE (rôles des utilisateurs)
-- ================================================================
CREATE TABLE role (
    id_role INT AUTO_INCREMENT PRIMARY KEY,
    nom_role VARCHAR(50) NOT NULL,
    description TEXT,
    permissions JSON,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Insérer les rôles par défaut
INSERT INTO role (id_role, nom_role, description, permissions) VALUES 
(1, 'Utilisateur', 'Utilisateur standard', '["read_exercises", "create_sessions"]'),
(2, 'Administrateur', 'Administrateur du système', '["*"]');

-- ================================================================
-- 2. TABLE UTILISATEUR (utilisateurs du système)
-- ================================================================
CREATE TABLE utilisateur (
    id_utilisateur INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL,
    prenom VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    date_naissance DATE NULL,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_derniere_connexion DATETIME NULL,
    statut ENUM('actif', 'inactif') DEFAULT 'actif',
    id_role INT DEFAULT 1,
    FOREIGN KEY (id_role) REFERENCES role(id_role)
);

-- ================================================================
-- 3. TABLE EXERCICE_RESPIRATION (exercices de respiration)
-- ================================================================
CREATE TABLE exercice_respiration (
    id_exercice INT AUTO_INCREMENT PRIMARY KEY,
    nom_exercice VARCHAR(100) NOT NULL,
    description TEXT,
    duree_inspiration INT NOT NULL COMMENT 'Durée en secondes',
    duree_apnee INT DEFAULT 0 COMMENT 'Durée en secondes',
    duree_expiration INT NOT NULL COMMENT 'Durée en secondes',
    nb_cycles_recommandes INT DEFAULT 5,
    niveau_difficulte ENUM('débutant', 'intermédiaire', 'avancé') DEFAULT 'débutant',
    couleur_theme VARCHAR(7) DEFAULT '#4A90E2' COMMENT 'Code couleur hexadécimal',
    statut ENUM('actif', 'inactif') DEFAULT 'actif',
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Insérer quelques exercices par défaut
INSERT INTO exercice_respiration (nom_exercice, description, duree_inspiration, duree_apnee, duree_expiration, nb_cycles_recommandes, niveau_difficulte, couleur_theme) VALUES 
('Respiration 4-4-4', 'Respiration équilibrée pour débuter', 4, 4, 4, 5, 'débutant', '#4A90E2'),
('Respiration 4-7-8', 'Technique relaxante pour le sommeil', 4, 7, 8, 4, 'intermédiaire', '#7B68EE'),
('Respiration Box', 'Respiration carrée pour la concentration', 4, 4, 4, 8, 'intermédiaire', '#32CD32');

-- ================================================================
-- 4. TABLE SESSION_RESPIRATION (sessions d'exercices)
-- ================================================================
CREATE TABLE session_respiration (
    id_session INT AUTO_INCREMENT PRIMARY KEY,
    id_utilisateur INT NOT NULL,
    id_exercice INT NOT NULL,
    date_debut DATETIME NOT NULL,
    date_fin DATETIME NULL,
    nb_cycles_realises INT DEFAULT 0,
    duree_totale_secondes INT DEFAULT 0,
    session_completee BOOLEAN DEFAULT FALSE,
    ressenti_avant ENUM('très_stressé', 'stressé', 'neutre', 'calme', 'très_calme') NULL,
    ressenti_apres ENUM('très_stressé', 'stressé', 'neutre', 'calme', 'très_calme') NULL,
    commentaire TEXT NULL,
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateur(id_utilisateur) ON DELETE CASCADE,
    FOREIGN KEY (id_exercice) REFERENCES exercice_respiration(id_exercice) ON DELETE CASCADE
);

-- ================================================================
-- 5. TABLE FAVORI_EXERCICE (exercices favoris des utilisateurs)
-- ================================================================
CREATE TABLE favori_exercice (
    id_favori INT AUTO_INCREMENT PRIMARY KEY,
    id_utilisateur INT NOT NULL,
    id_exercice INT NOT NULL,
    date_ajout DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateur(id_utilisateur) ON DELETE CASCADE,
    FOREIGN KEY (id_exercice) REFERENCES exercice_respiration(id_exercice) ON DELETE CASCADE,
    UNIQUE KEY unique_favori (id_utilisateur, id_exercice)
);

-- ================================================================
-- 6. TABLE STATISTIQUE_MENSUELLE (statistiques mensuelles des utilisateurs)
-- ================================================================
CREATE TABLE statistique_mensuelle (
    id_statistique INT AUTO_INCREMENT PRIMARY KEY,
    id_utilisateur INT NOT NULL,
    mois_annee VARCHAR(7) NOT NULL COMMENT 'Format YYYY-MM',
    nb_sessions INT DEFAULT 0,
    temps_total_minutes INT DEFAULT 0,
    progression_score DECIMAL(5,2) DEFAULT 0.00 COMMENT 'Score sur 100',
    date_calcul DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateur(id_utilisateur) ON DELETE CASCADE,
    UNIQUE KEY unique_stat_mensuelle (id_utilisateur, mois_annee)
);

-- ================================================================
-- 7. TABLE CONTENU_INFORMATION (articles et contenus informatifs)
-- ================================================================
CREATE TABLE contenu_information (
    id_contenu INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(200) NOT NULL,
    contenu LONGTEXT NOT NULL,
    type_contenu ENUM('article', 'conseil', 'guide', 'actualité') DEFAULT 'article',
    meta_description VARCHAR(300) NULL,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_modification DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    statut ENUM('publié', 'brouillon', 'archivé') DEFAULT 'brouillon',
    ordre_affichage INT DEFAULT 0
);

-- ================================================================
-- 8. TABLES SYSTÈME LARAVEL (nécessaires pour le fonctionnement)
-- ================================================================

-- Table pour les tokens d'authentification (Laravel Sanctum)
CREATE TABLE personal_access_tokens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tokenable_type VARCHAR(255) NOT NULL,
    tokenable_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    abilities TEXT NULL,
    last_used_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX personal_access_tokens_tokenable_type_tokenable_id_index (tokenable_type, tokenable_id)
);

-- Table pour les sessions Laravel
CREATE TABLE sessions (
    id VARCHAR(255) PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    payload LONGTEXT NOT NULL,
    last_activity INT NOT NULL,
    INDEX sessions_user_id_index (user_id),
    INDEX sessions_last_activity_index (last_activity)
);

-- Table pour le cache Laravel
CREATE TABLE cache (
    `key` VARCHAR(255) PRIMARY KEY,
    value MEDIUMTEXT NOT NULL,
    expiration INT NOT NULL
);

-- Table pour les verrous de cache Laravel
CREATE TABLE cache_locks (
    `key` VARCHAR(255) PRIMARY KEY,
    owner VARCHAR(255) NOT NULL,
    expiration INT NOT NULL
);

-- Table pour les migrations Laravel
CREATE TABLE migrations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    migration VARCHAR(255) NOT NULL,
    batch INT NOT NULL
);

-- ================================================================
-- UTILISATEUR DE TEST
-- ================================================================
-- Insérer un utilisateur de test (mot de passe: "password")
INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, id_role, statut) VALUES 
('Admin', 'Test', 'admin@cesizen.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, 'actif'),
('User', 'Test', 'user@cesizen.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 'actif');

-- ================================================================
-- CONTENU DE TEST
-- ================================================================
INSERT INTO contenu_information (titre, contenu, type_contenu, meta_description, statut) VALUES 
('Bienfaits de la respiration consciente', 'La respiration consciente est une pratique millénaire qui offre de nombreux bienfaits...', 'article', 'Découvrez les bienfaits de la respiration consciente sur votre bien-être', 'publié'),
('Guide du débutant', 'Comment commencer votre pratique de respiration...', 'guide', 'Guide complet pour débuter la respiration consciente', 'publié');

-- ================================================================
-- VÉRIFICATION
-- ================================================================
SELECT 'Tables créées avec succès !' as message;
SELECT COUNT(*) as nb_utilisateurs FROM utilisateur;
SELECT COUNT(*) as nb_exercices FROM exercice_respiration;
SELECT COUNT(*) as nb_contenus FROM contenu_information; 