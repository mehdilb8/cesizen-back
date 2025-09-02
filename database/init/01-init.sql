-- Script d'initialisation de la base de données Cesizen
-- Ce script sera exécuté automatiquement lors du premier démarrage du conteneur MySQL

-- Création de la base de données si elle n'existe pas
CREATE DATABASE IF NOT EXISTS cesizen CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Utilisation de la base de données
USE cesizen;

-- Création de la table role
CREATE TABLE IF NOT EXISTS `role` (
  `id_role` int(11) NOT NULL AUTO_INCREMENT,
  `nom_role` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `permissions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`permissions`)),
  `date_creation` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_role`),
  UNIQUE KEY `nom_role` (`nom_role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertion des rôles de base
INSERT IGNORE INTO `role` (`id_role`, `nom_role`, `description`, `permissions`, `date_creation`) VALUES
(1, 'utilisateur', 'Utilisateur standard avec accès aux fonctionnalités de base', '{"can_read": true, "can_practice": true, "can_edit_profile": true}', '2025-07-08 06:06:02'),
(2, 'administrateur', 'Administrateur avec accès complet à la gestion du système', '{"can_read": true, "can_practice": true, "can_edit_profile": true, "can_admin": true, "can_manage_users": true, "can_manage_content": true}', '2025-07-08 06:06:02');

-- Création de la table utilisateur
CREATE TABLE IF NOT EXISTS `utilisateur` (
  `id_utilisateur` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(50) NOT NULL,
  `prenom` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `date_naissance` date DEFAULT NULL,
  `date_creation` datetime NOT NULL DEFAULT current_timestamp(),
  `date_derniere_connexion` datetime DEFAULT NULL,
  `statut` enum('actif','inactif') NOT NULL DEFAULT 'actif',
  `id_role` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_utilisateur`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_utilisateur_email` (`email`),
  KEY `idx_utilisateur_statut` (`statut`),
  KEY `idx_utilisateur_role` (`id_role`),
  CONSTRAINT `fk_utilisateur_role` FOREIGN KEY (`id_role`) REFERENCES `role` (`id_role`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertion d'un utilisateur admin par défaut
INSERT IGNORE INTO `utilisateur` (`id_utilisateur`, `nom`, `prenom`, `email`, `mot_de_passe`, `date_naissance`, `date_creation`, `date_derniere_connexion`, `statut`, `id_role`) VALUES
(1, 'Admin', 'Docker', 'admin@cesizen.fr', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.', NULL, NOW(), NULL, 'actif', 2);

-- Création de la table personal_access_tokens
CREATE TABLE IF NOT EXISTS `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Création de la table contenu_information
CREATE TABLE IF NOT EXISTS `contenu_information` (
  `id_contenu` int(11) NOT NULL AUTO_INCREMENT,
  `titre` varchar(200) NOT NULL,
  `contenu` text NOT NULL,
  `type_contenu` enum('page','article') NOT NULL,
  `meta_description` varchar(160) DEFAULT NULL,
  `date_creation` datetime NOT NULL DEFAULT current_timestamp(),
  `date_modification` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `statut` enum('publie','brouillon','archive') NOT NULL DEFAULT 'brouillon',
  `ordre_affichage` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_contenu`),
  KEY `idx_contenu_statut` (`statut`),
  KEY `idx_contenu_type` (`type_contenu`),
  KEY `idx_contenu_ordre` (`ordre_affichage`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertion de contenus d'exemple
INSERT IGNORE INTO `contenu_information` (`id_contenu`, `titre`, `contenu`, `type_contenu`, `meta_description`, `date_creation`, `date_modification`, `statut`, `ordre_affichage`) VALUES
(1, 'Bienvenue sur Cesizen', 'Bienvenue sur Cesizen, votre plateforme de bien-être et de respiration consciente.', 'page', 'Découvrez Cesizen, votre plateforme de bien-être', NOW(), NULL, 'publie', 1),
(2, 'Guide de respiration', 'Apprenez les techniques de respiration pour réduire le stress et améliorer votre bien-être.', 'article', 'Guide complet des techniques de respiration', NOW(), NULL, 'publie', 2);

-- Création de la table exercice_respiration
CREATE TABLE IF NOT EXISTS `exercice_respiration` (
  `id_exercice` int(11) NOT NULL AUTO_INCREMENT,
  `nom_exercice` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `duree_inspiration` int(11) NOT NULL,
  `duree_apnee` int(11) NOT NULL DEFAULT 0,
  `duree_expiration` int(11) NOT NULL,
  `nb_cycles_recommandes` int(11) NOT NULL DEFAULT 10,
  `niveau_difficulte` enum('debutant','intermediaire','avance') NOT NULL DEFAULT 'debutant',
  `couleur_theme` varchar(7) DEFAULT NULL,
  `statut` enum('actif','inactif') NOT NULL DEFAULT 'actif',
  `date_creation` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_exercice`),
  KEY `idx_exercice_statut` (`statut`),
  KEY `idx_exercice_difficulte` (`niveau_difficulte`),
  KEY `idx_exercice_nom` (`nom_exercice`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertion d'exercices d'exemple
INSERT IGNORE INTO `exercice_respiration` (`id_exercice`, `nom_exercice`, `description`, `duree_inspiration`, `duree_apnee`, `duree_expiration`, `nb_cycles_recommandes`, `niveau_difficulte`, `couleur_theme`, `statut`, `date_creation`) VALUES
(1, 'Cohérence cardiaque 4-7-8', 'Technique de respiration relaxante : inspirez 4s, retenez 7s, expirez 8s', 4, 7, 8, 6, 'debutant', '#27AE60', 'actif', NOW()),
(2, 'Respiration carrée', 'Respiration équilibrée : inspirez 4s, retenez 4s, expirez 4s, pause 4s', 4, 4, 4, 8, 'intermediaire', '#3498DB', 'actif', NOW());

-- Création de la table session_respiration
CREATE TABLE IF NOT EXISTS `session_respiration` (
  `id_session` int(11) NOT NULL AUTO_INCREMENT,
  `id_utilisateur` int(11) NOT NULL,
  `id_exercice` int(11) NOT NULL,
  `date_debut` datetime NOT NULL DEFAULT current_timestamp(),
  `date_fin` datetime DEFAULT NULL,
  `nb_cycles_realises` int(11) NOT NULL DEFAULT 0,
  `duree_totale_secondes` int(11) DEFAULT NULL,
  `session_completee` tinyint(1) NOT NULL DEFAULT 0,
  `ressenti_avant` tinyint(4) DEFAULT NULL,
  `ressenti_apres` tinyint(4) DEFAULT NULL,
  `commentaire` text DEFAULT NULL,
  PRIMARY KEY (`id_session`),
  KEY `idx_session_utilisateur` (`id_utilisateur`),
  KEY `idx_session_exercice` (`id_exercice`),
  KEY `idx_session_date_debut` (`date_debut`),
  KEY `idx_session_completee` (`session_completee`),
  CONSTRAINT `fk_session_exercice` FOREIGN KEY (`id_exercice`) REFERENCES `exercice_respiration` (`id_exercice`) ON UPDATE CASCADE,
  CONSTRAINT `fk_session_utilisateur` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Création de la table favori_exercice
CREATE TABLE IF NOT EXISTS `favori_exercice` (
  `id_favori` int(11) NOT NULL AUTO_INCREMENT,
  `id_utilisateur` int(11) NOT NULL,
  `id_exercice` int(11) NOT NULL,
  `date_ajout` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_favori`),
  UNIQUE KEY `uk_favori_utilisateur_exercice` (`id_utilisateur`,`id_exercice`),
  KEY `idx_favori_utilisateur` (`id_utilisateur`),
  KEY `idx_favori_exercice` (`id_exercice`),
  CONSTRAINT `fk_favori_exercice` FOREIGN KEY (`id_exercice`) REFERENCES `exercice_respiration` (`id_exercice`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_favori_utilisateur` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Création de la table statistique_mensuelle
CREATE TABLE IF NOT EXISTS `statistique_mensuelle` (
  `id_statistique` int(11) NOT NULL AUTO_INCREMENT,
  `id_utilisateur` int(11) NOT NULL,
  `mois_annee` varchar(7) NOT NULL,
  `nb_sessions` int(11) NOT NULL DEFAULT 0,
  `temps_total_minutes` int(11) NOT NULL DEFAULT 0,
  `progression_score` decimal(3,2) DEFAULT NULL,
  `date_calcul` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_statistique`),
  UNIQUE KEY `uk_stat_utilisateur_mois` (`id_utilisateur`,`mois_annee`),
  KEY `idx_stat_utilisateur` (`id_utilisateur`),
  KEY `idx_stat_mois` (`mois_annee`),
  CONSTRAINT `fk_stat_utilisateur` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Création de la table cache
CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(255) NOT NULL,
  `value` longtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Création de la table jobs
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Création de la table job_batches
CREATE TABLE IF NOT EXISTS `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` longtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Création de la table failed_jobs
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Création de la table migrations
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertion des migrations de base
INSERT IGNORE INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_07_08_040907_create_personal_access_tokens_table', 1),
(5, '2025_07_08_040932_create_role_table', 1),
(6, '2025_07_08_040932_create_utilisateur_table', 1),
(7, '2025_07_08_040933_create_exercice_respiration_table', 1),
(8, '2025_07_08_040934_create_remaining_cesizen_tables', 1);

-- Création de la table password_reset_tokens
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Création de la table sessions
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Message de confirmation
SELECT 'Base de données Cesizen initialisée avec succès !' as message;
