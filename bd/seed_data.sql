-- =============================================================================
-- SAE BUT Informatique S3 - IUT Nancy-Charlemagne
-- Sujet Base de Données 2026 : Touiteur.app
-- Fichier : seed_data.sql
-- Description : Jeu de données de test complet pour MySQL / MariaDB
-- =============================================================================

-- Utilisateurs
INSERT INTO UTILISATEUR (id_user, pseudo, nom, prenom, email, nb_touites, date_inscription) VALUES
(1, 'alice_tech', 'Dupont', 'Alice', 'alice.dupont@univ-lorraine.fr', 4, '2026-10-01 10:00:00'),
(2, 'bob_dev', 'Martin', 'Bob', 'bob.martin@univ-lorraine.fr', 3, '2026-10-02 11:30:00'),
(3, 'charlie_cyber', 'Bernard', 'Charlie', 'charlie.bernard@univ-lorraine.fr', 3, '2026-10-05 14:15:00'),
(4, 'diana_ia', 'Thomas', 'Diana', 'diana.thomas@univ-lorraine.fr', 2, '2026-10-08 09:45:00'),
(5, 'eva_design', 'Robert', 'Eva', 'eva.robert@univ-lorraine.fr', 1, '2026-10-10 16:20:00');

-- Images de test
INSERT INTO IMAGE (id_img, chemin, description) VALUES
(1, 'demo_architecture.png', 'Schéma d''architecture MVC Dispatcher Action'),
(2, 'demo_cyber.png', 'Visualisation d''une analyse de sécurité');

-- Touites (<= 235 caractères chacun)
INSERT INTO TOUITE (id_touite, texte, date_publication, note, id_auteur, id_image) VALUES
(1, 'Bienvenue sur #Touiteur ! Première publication pour lancer la plateforme #web et partager les actus de la promo.', '2026-11-01 08:30:00', 5, 1, NULL),
(2, 'Architecture propre avec Dispatcher et Single Action en #PHP. Que du bonheur ! #dev #architecture', '2026-11-01 09:15:00', 3, 2, 1),
(3, 'Attention aux injections SQL ! Pensez à toujours utiliser des requêtes préparées avec PDO. #cyber #security #php', '2026-11-01 10:00:00', 8, 3, 2),
(4, 'Nos modèles d''IA s''entraînent parfaitement. Vivement la suite des projets ! #ia #innovation', '2026-11-01 11:45:00', 2, 4, NULL),
(5, 'Nouveau design minimaliste pour l''interface utilisateur. Donnez vos retours ! #design #web #ux', '2026-11-01 13:00:00', 4, 5, NULL),
(6, 'Rappel pour le rendu de la SAE 3.1 : vendredi 13 novembre 20h pour le git ! #sae #iut #nancy', '2026-11-02 08:00:00', 6, 1, NULL),
(7, 'Quelqu''un a testé la pagination sur les listes de touites ? Super fluide ! #dev #touiteur #php', '2026-11-02 09:30:00', 1, 2, NULL),
(8, 'Cryptographie asymétrique ou symétrique pour sécuriser les échanges ? Analyse en cours ! #cyber #crypto', '2026-11-02 14:10:00', 3, 3, NULL),
(9, 'La fin du flood : 50 touites max par jour grâce aux triggers Oracle PL/SQL. #oracle #sql #security', '2026-11-02 16:50:00', 4, 1, NULL),
(10, 'Nouvel algorithme de recommandation sur le mur utilisateur. Vos flux sont à jour. #ia #touiteur', '2026-11-03 09:10:00', 2, 4, NULL),
(11, 'Touiteur Corp prépare son back-office pour analyser les tendances. #business #touiteur #stats', '2026-11-03 10:00:00', 0, 1, NULL),
(12, 'Dernier sprint avant la mise en ligne sur le serveur webetu ! Bon courage à tous. #webetu #iut #dev', '2026-11-03 11:20:00', 5, 2, NULL),
(13, 'Test de sécurité XSS : tous les caractères spéciaux sont correctement échappés avec htmlspecialchars ! #security #cyber', '2026-11-03 14:00:00', 4, 3, NULL);

-- Tags
INSERT INTO TAG (id_tag, libelle, description) VALUES
(1, 'Touiteur', 'Discussions officielles autour de la plateforme Touiteur'),
(2, 'web', 'Technologies du web et développement frontend/backend'),
(3, 'PHP', 'Langage PHP, bonnes pratiques et POO'),
(4, 'dev', 'Développement logiciel et programmation'),
(5, 'architecture', 'Patrons de conception et architecture logicielle'),
(6, 'cyber', 'Cybersécurité, pentest et bonnes pratiques'),
(7, 'security', 'Sécurité informatique et protection contre les injections'),
(8, 'ia', 'Intelligence Artificielle et Machine Learning'),
(9, 'innovation', 'Nouvelles technologies et projets innovants'),
(10, 'design', 'Design d''interfaces et expérience utilisateur'),
(11, 'ux', 'Ergonomie et interface graphique'),
(12, 'sae', 'Projets et SAE BUT Informatique'),
(13, 'iut', 'IUT Nancy-Charlemagne'),
(14, 'nancy', 'Actualités du campus de Nancy'),
(15, 'crypto', 'Cryptographie appliquée et sécurité'),
(16, 'oracle', 'Base de données Oracle et PL/SQL'),
(17, 'sql', 'Requêtes SQL et conception relationnelle'),
(18, 'business', 'Monétisation et partenariats'),
(19, 'stats', 'Statistiques et métriques de la plateforme'),
(20, 'webetu', 'Serveur étudiant de l''IUT Nancy-Charlemagne');

-- Association TOUITE_TAG
INSERT INTO TOUITE_TAG (id_touite, id_tag) VALUES
(1, 1), (1, 2),
(2, 3), (2, 4), (2, 5),
(3, 3), (3, 6), (3, 7),
(4, 8), (4, 9),
(5, 10), (5, 2), (5, 11),
(6, 12), (6, 13), (6, 14),
(7, 4), (7, 1), (7, 3),
(8, 6), (8, 15),
(9, 16), (9, 17), (9, 7),
(10, 8), (10, 1),
(11, 18), (11, 1), (11, 19),
(12, 20), (12, 13), (12, 4),
(13, 7), (13, 6);

-- Abonnements utilisateurs (SUIVRE_UTILISATEUR)
-- Alice (1) est suivie par 2, 3, 4, 5 (la plus influente)
-- Bob (2) est suivi par 1, 3
-- Charlie (3) est suivi par 1, 2, 4
-- Diana (4) est suivie par 1
INSERT INTO SUIVRE_UTILISATEUR (id_suiveur, id_suivi, date_suivi) VALUES
(2, 1, '2026-10-15 10:00:00'),
(3, 1, '2026-10-15 11:00:00'),
(4, 1, '2026-10-16 09:30:00'),
(5, 1, '2026-10-16 14:00:00'),
(1, 2, '2026-10-17 08:00:00'),
(3, 2, '2026-10-18 12:00:00'),
(1, 3, '2026-10-19 16:30:00'),
(2, 3, '2026-10-20 09:00:00'),
(4, 3, '2026-10-21 11:20:00'),
(1, 4, '2026-10-22 17:10:00');

-- Abonnements aux tags (ABONNER_TAG)
INSERT INTO ABONNER_TAG (id_user, id_tag, date_abonnement) VALUES
(1, 4, '2026-10-15 10:00:00'), -- Alice suit #dev
(1, 6, '2026-10-15 10:05:00'), -- Alice suit #cyber
(2, 3, '2026-10-16 11:00:00'), -- Bob suit #php
(2, 7, '2026-10-16 11:05:00'), -- Bob suit #security
(3, 7, '2026-10-17 14:00:00'), -- Charlie suit #security
(3, 15, '2026-10-17 14:05:00'), -- Charlie suit #crypto
(4, 8, '2026-10-18 09:00:00'), -- Diana suit #ia
(5, 10, '2026-10-19 15:00:00'); -- Eva suit #design

-- Évaluations de touites (EVALUER_TOUITE)
INSERT INTO EVALUER_TOUITE (id_user, id_touite, valeur, date_eval) VALUES
(2, 1, 1, '2026-11-01 09:00:00'),
(3, 1, 1, '2026-11-01 09:10:00'),
(4, 1, 1, '2026-11-01 10:00:00'),
(5, 1, 1, '2026-11-01 10:30:00'),
(1, 2, 1, '2026-11-01 09:40:00'),
(3, 2, 1, '2026-11-01 10:15:00'),
(4, 2, 1, '2026-11-01 11:00:00'),
(1, 3, 1, '2026-11-01 10:30:00'),
(2, 3, 1, '2026-11-01 10:45:00'),
(4, 3, 1, '2026-11-01 12:00:00'),
(5, 3, 1, '2026-11-01 13:10:00'),
(1, 6, 1, '2026-11-02 08:30:00'),
(2, 6, 1, '2026-11-02 08:45:00'),
(3, 6, 1, '2026-11-02 09:15:00'),
(4, 6, 1, '2026-11-02 10:00:00'),
(5, 6, 1, '2026-11-02 11:00:00'),
(1, 12, 1, '2026-11-03 12:00:00'),
(3, 12, 1, '2026-11-03 12:15:00'),
(4, 12, 1, '2026-11-03 12:30:00'),
(5, 12, 1, '2026-11-03 13:00:00'),
(1, 13, 1, '2026-11-03 14:10:00'),
(2, 13, 1, '2026-11-03 14:30:00'),
(4, 13, 1, '2026-11-03 14:45:00');
