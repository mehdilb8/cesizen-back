-- ================================================================
-- EXERCICES DE RESPIRATION POUR CESIZEN
-- Basés sur la cohérence cardiaque
-- ================================================================

USE cesizen;

-- Insérer les 3 exercices de respiration spécifiés
INSERT INTO exercice_respiration (
    nom_exercice, 
    description, 
    duree_inspiration, 
    duree_apnee, 
    duree_expiration, 
    nb_cycles_recommandes, 
    niveau_difficulte, 
    couleur_theme, 
    statut
) VALUES 

-- Exercice 7-4-8 (cohérence cardiaque avancée)
(
    'Respiration 7-4-8', 
    'Exercice de cohérence cardiaque avancé : Inspiration 7 secondes, Apnée 4 secondes, Expiration 8 secondes. Idéal pour la relaxation profonde et la gestion du stress.', 
    7, 
    4, 
    8, 
    5, 
    'avancé', 
    '#7B68EE', 
    'actif'
),

-- Exercice 5-0-5 (cohérence cardiaque simple)
(
    'Respiration 5-5', 
    'Exercice de cohérence cardiaque simple : Inspiration 5 secondes, Expiration 5 secondes. Parfait pour débuter et établir un rythme cardiaque cohérent.', 
    5, 
    0, 
    5, 
    8, 
    'débutant', 
    '#4A90E2', 
    'actif'
),

-- Exercice 4-0-6 (cohérence cardiaque intermédiaire)
(
    'Respiration 4-6', 
    'Exercice de cohérence cardiaque intermédiaire : Inspiration 4 secondes, Expiration 6 secondes. Favorise la détente et l\'activation du système parasympathique.', 
    4, 
    0, 
    6, 
    6, 
    'intermédiaire', 
    '#32CD32', 
    'actif'
);

-- Vérification des exercices insérés
SELECT 
    id_exercice,
    nom_exercice,
    CONCAT(duree_inspiration, '-', duree_apnee, '-', duree_expiration) as rythme,
    niveau_difficulte,
    statut
FROM exercice_respiration 
ORDER BY niveau_difficulte, id_exercice; 