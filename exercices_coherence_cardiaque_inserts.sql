-- =====================================================
-- EXERCICES DE RESPIRATION ET COHÉRENCE CARDIAQUE
-- Insertion des 3 variantes spécifiées
-- =====================================================

USE cesizen;

-- Exercice 748 : 7-4-8
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
) VALUES (
    'Cohérence cardiaque 7-4-8',
    'Exercice de cohérence cardiaque avancé. Inspirez pendant 7 secondes, retenez votre souffle 4 secondes, puis expirez lentement pendant 8 secondes. Idéal pour une relaxation profonde et la gestion du stress.',
    7,
    4, 
    8,
    6,
    'avance',
    '#9B59B6',
    'actif'
);

-- Exercice 55 : 5-0-5 
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
) VALUES (
    'Cohérence cardiaque 5-5',
    'Exercice de cohérence cardiaque classique. Respirez de manière équilibrée : 5 secondes d\'inspiration et 5 secondes d\'expiration, sans apnée. Parfait pour harmoniser le rythme cardiaque et réduire le stress au quotidien.',
    5,
    0,
    5,
    8,
    'debutant',
    '#27AE60',
    'actif'
);

-- Exercice 46 : 4-0-6
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
) VALUES (
    'Cohérence cardiaque 4-6',
    'Exercice de cohérence cardiaque avec expiration prolongée. Inspirez pendant 4 secondes puis expirez lentement pendant 6 secondes. Cette technique favorise l\'activation du système nerveux parasympathique et procure un effet calmant.',
    4,
    0,
    6,
    10,
    'intermediaire',
    '#3498DB',
    'actif'
);

-- Vérification des exercices ajoutés
SELECT 'Exercices de cohérence cardiaque ajoutés avec succès!' as message;

SELECT 
    nom_exercice,
    CONCAT(duree_inspiration, '-', duree_apnee, '-', duree_expiration) as rythme,
    niveau_difficulte,
    nb_cycles_recommandes
FROM exercice_respiration 
WHERE nom_exercice LIKE '%Cohérence cardiaque%'
ORDER BY niveau_difficulte; 