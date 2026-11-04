-- =============================================================================
-- SAE BUT Informatique S3 - IUT Nancy-Charlemagne
-- Sujet Base de Données 2026 : Touiteur.app
-- Fichier : mysql_schema.sql
-- Description : Schéma relationnel pour MySQL / MariaDB (Webetu)
-- =============================================================================

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS EVALUER_TOUITE;
DROP TABLE IF EXISTS ABONNER_TAG;
DROP TABLE IF EXISTS SUIVRE_UTILISATEUR;
DROP TABLE IF EXISTS TOUITE_TAG;
DROP TABLE IF EXISTS TAG;
DROP TABLE IF EXISTS TOUITE;
DROP TABLE IF EXISTS IMAGE;
DROP TABLE IF EXISTS UTILISATEUR;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. Table UTILISATEUR
CREATE TABLE UTILISATEUR (
    id_user          INT AUTO_INCREMENT PRIMARY KEY,
    pseudo           VARCHAR(50) NOT NULL UNIQUE,
    nom              VARCHAR(100) NOT NULL,
    prenom           VARCHAR(100) NOT NULL,
    email            VARCHAR(150) NOT NULL UNIQUE,
    nb_touites       INT NOT NULL DEFAULT 0,
    date_inscription DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT chk_user_nbtouites CHECK (nb_touites >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Table IMAGE
CREATE TABLE IMAGE (
    id_img           INT AUTO_INCREMENT PRIMARY KEY,
    chemin           VARCHAR(255) NOT NULL,
    description      VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Table TOUITE
CREATE TABLE TOUITE (
    id_touite        INT AUTO_INCREMENT PRIMARY KEY,
    texte            VARCHAR(235) NOT NULL,
    date_publication DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    note             INT NOT NULL DEFAULT 0,
    id_auteur        INT NOT NULL,
    id_image         INT NULL,
    CONSTRAINT fk_touite_auteur FOREIGN KEY (id_auteur) 
        REFERENCES UTILISATEUR(id_user) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_touite_image FOREIGN KEY (id_image) 
        REFERENCES IMAGE(id_img) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT chk_touite_taille CHECK (CHAR_LENGTH(texte) <= 235)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Table TAG
CREATE TABLE TAG (
    id_tag           INT AUTO_INCREMENT PRIMARY KEY,
    libelle          VARCHAR(50) NOT NULL UNIQUE,
    description      VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Table TOUITE_TAG (N:M Touite <-> Tag)
CREATE TABLE TOUITE_TAG (
    id_touite        INT NOT NULL,
    id_tag           INT NOT NULL,
    PRIMARY KEY (id_touite, id_tag),
    CONSTRAINT fk_tt_touite FOREIGN KEY (id_touite) 
        REFERENCES TOUITE(id_touite) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_tt_tag FOREIGN KEY (id_tag) 
        REFERENCES TAG(id_tag) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Table SUIVRE_UTILISATEUR (Abonnements utilisateurs)
CREATE TABLE SUIVRE_UTILISATEUR (
    id_suiveur       INT NOT NULL,
    id_suivi         INT NOT NULL,
    date_suivi       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_suiveur, id_suivi),
    CONSTRAINT fk_suivre_suiveur FOREIGN KEY (id_suiveur) 
        REFERENCES UTILISATEUR(id_user) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_suivre_suivi FOREIGN KEY (id_suivi) 
        REFERENCES UTILISATEUR(id_user) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT chk_auto_suivi CHECK (id_suiveur <> id_suivi)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Table ABONNER_TAG (Abonnements aux tags)
CREATE TABLE ABONNER_TAG (
    id_user          INT NOT NULL,
    id_tag           INT NOT NULL,
    date_abonnement  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_user, id_tag),
    CONSTRAINT fk_abontag_user FOREIGN KEY (id_user) 
        REFERENCES UTILISATEUR(id_user) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_abontag_tag FOREIGN KEY (id_tag) 
        REFERENCES TAG(id_tag) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Table EVALUER_TOUITE (Notes / Likes & Dislikes)
CREATE TABLE EVALUER_TOUITE (
    id_user          INT NOT NULL,
    id_touite        INT NOT NULL,
    valeur           TINYINT NOT NULL, -- +1 (like) ou -1 (dislike)
    date_eval        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_user, id_touite),
    CONSTRAINT fk_eval_user FOREIGN KEY (id_user) 
        REFERENCES UTILISATEUR(id_user) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_eval_touite FOREIGN KEY (id_touite) 
        REFERENCES TOUITE(id_touite) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT chk_valeur_note CHECK (valeur IN (-1, 1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Triggers MySQL pour mise à jour automatique du compteur de touites
DELIMITER //

CREATE TRIGGER trg_mysql_inc_touites AFTER INSERT ON TOUITE
FOR EACH ROW
BEGIN
    UPDATE UTILISATEUR SET nb_touites = nb_touites + 1 WHERE id_user = NEW.id_auteur;
END;
//

CREATE TRIGGER trg_mysql_dec_touites AFTER DELETE ON TOUITE
FOR EACH ROW
BEGIN
    UPDATE UTILISATEUR SET nb_touites = GREATEST(0, nb_touites - 1) WHERE id_user = OLD.id_auteur;
END;
//

-- Mise à jour automatique de la note globale du touite lors d'un vote
CREATE TRIGGER trg_mysql_eval_insert AFTER INSERT ON EVALUER_TOUITE
FOR EACH ROW
BEGIN
    UPDATE TOUITE SET note = note + NEW.valeur WHERE id_touite = NEW.id_touite;
END;
//

CREATE TRIGGER trg_mysql_eval_update AFTER UPDATE ON EVALUER_TOUITE
FOR EACH ROW
BEGIN
    UPDATE TOUITE SET note = note - OLD.valeur + NEW.valeur WHERE id_touite = NEW.id_touite;
END;
//

CREATE TRIGGER trg_mysql_eval_delete AFTER DELETE ON EVALUER_TOUITE
FOR EACH ROW
BEGIN
    UPDATE TOUITE SET note = note - OLD.valeur WHERE id_touite = OLD.id_touite;
END;
//

DELIMITER ;
