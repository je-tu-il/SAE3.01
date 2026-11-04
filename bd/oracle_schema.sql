-- =============================================================================
-- SAE BUT Informatique S3 - IUT Nancy-Charlemagne
-- Sujet Base de Données 2026 : Touiteur.app
-- Fichier : oracle_schema.sql
-- Description : Définition du schéma relationnel sous Oracle Database
-- =============================================================================

-- Suppression propre des tables existantes
DROP TABLE EVALUER_TOUITE CASCADE CONSTRAINTS;
DROP TABLE ABONNER_TAG CASCADE CONSTRAINTS;
DROP TABLE SUIVRE_UTILISATEUR CASCADE CONSTRAINTS;
DROP TABLE TOUITE_TAG CASCADE CONSTRAINTS;
DROP TABLE TAG CASCADE CONSTRAINTS;
DROP TABLE TOUITE CASCADE CONSTRAINTS;
DROP TABLE IMAGE CASCADE CONSTRAINTS;
DROP TABLE UTILISATEUR CASCADE CONSTRAINTS;

-- 1. Table UTILISATEUR
CREATE TABLE UTILISATEUR (
    id_user          NUMBER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    pseudo           VARCHAR2(50) NOT NULL,
    nom              VARCHAR2(100) NOT NULL,
    prenom           VARCHAR2(100) NOT NULL,
    email            VARCHAR2(150) NOT NULL,
    nb_touites       NUMBER DEFAULT 0 NOT NULL,
    date_inscription DATE DEFAULT SYSDATE NOT NULL,
    CONSTRAINT unq_user_pseudo UNIQUE (pseudo),
    CONSTRAINT unq_user_email UNIQUE (email),
    CONSTRAINT chk_user_nbtouites CHECK (nb_touites >= 0)
);

-- 2. Table IMAGE
CREATE TABLE IMAGE (
    id_img           NUMBER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    chemin           VARCHAR2(255) NOT NULL,
    description      VARCHAR2(255)
);

-- 3. Table TOUITE
CREATE TABLE TOUITE (
    id_touite        NUMBER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    texte            VARCHAR2(235) NOT NULL,
    date_publication DATE DEFAULT SYSDATE NOT NULL,
    note             NUMBER DEFAULT 0 NOT NULL,
    id_auteur        NUMBER NOT NULL,
    id_image         NUMBER,
    CONSTRAINT fk_touite_auteur FOREIGN KEY (id_auteur) 
        REFERENCES UTILISATEUR(id_user) ON DELETE CASCADE,
    CONSTRAINT fk_touite_image FOREIGN KEY (id_image) 
        REFERENCES IMAGE(id_img) ON DELETE SET NULL,
    CONSTRAINT chk_touite_taille CHECK (LENGTH(texte) <= 235)
);

-- 4. Table TAG
CREATE TABLE TAG (
    id_tag           NUMBER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    libelle          VARCHAR2(50) NOT NULL,
    description      VARCHAR2(255),
    CONSTRAINT unq_tag_libelle UNIQUE (libelle)
);

-- 5. Table d'association TOUITE_TAG (touites contenant des tags)
CREATE TABLE TOUITE_TAG (
    id_touite        NUMBER NOT NULL,
    id_tag           NUMBER NOT NULL,
    CONSTRAINT pk_touite_tag PRIMARY KEY (id_touite, id_tag),
    CONSTRAINT fk_tt_touite FOREIGN KEY (id_touite) 
        REFERENCES TOUITE(id_touite) ON DELETE CASCADE,
    CONSTRAINT fk_tt_tag FOREIGN KEY (id_tag) 
        REFERENCES TAG(id_tag) ON DELETE CASCADE
);

-- 6. Table d'association SUIVRE_UTILISATEUR (abonnements entre utilisateurs)
CREATE TABLE SUIVRE_UTILISATEUR (
    id_suiveur       NUMBER NOT NULL,
    id_suivi         NUMBER NOT NULL,
    date_suivi       DATE DEFAULT SYSDATE NOT NULL,
    CONSTRAINT pk_suivre_user PRIMARY KEY (id_suiveur, id_suivi),
    CONSTRAINT fk_suivre_suiveur FOREIGN KEY (id_suiveur) 
        REFERENCES UTILISATEUR(id_user) ON DELETE CASCADE,
    CONSTRAINT fk_suivre_suivi FOREIGN KEY (id_suivi) 
        REFERENCES UTILISATEUR(id_user) ON DELETE CASCADE,
    CONSTRAINT chk_auto_suivi CHECK (id_suiveur != id_suivi)
);

-- 7. Table d'association ABONNER_TAG (abonnements des utilisateurs aux tags)
CREATE TABLE ABONNER_TAG (
    id_user          NUMBER NOT NULL,
    id_tag           NUMBER NOT NULL,
    date_abonnement  DATE DEFAULT SYSDATE NOT NULL,
    CONSTRAINT pk_abonner_tag PRIMARY KEY (id_user, id_tag),
    CONSTRAINT fk_abontag_user FOREIGN KEY (id_user) 
        REFERENCES UTILISATEUR(id_user) ON DELETE CASCADE,
    CONSTRAINT fk_abontag_tag FOREIGN KEY (id_tag) 
        REFERENCES TAG(id_tag) ON DELETE CASCADE
);

-- 8. Table d'association EVALUER_TOUITE (notes / votes sur les touites)
CREATE TABLE EVALUER_TOUITE (
    id_user          NUMBER NOT NULL,
    id_touite        NUMBER NOT NULL,
    valeur           NUMBER(1) NOT NULL,
    date_eval        DATE DEFAULT SYSDATE NOT NULL,
    CONSTRAINT pk_evaluer_touite PRIMARY KEY (id_user, id_touite),
    CONSTRAINT fk_eval_user FOREIGN KEY (id_user) 
        REFERENCES UTILISATEUR(id_user) ON DELETE CASCADE,
    CONSTRAINT fk_eval_touite FOREIGN KEY (id_touite) 
        REFERENCES TOUITE(id_touite) ON DELETE CASCADE,
    CONSTRAINT chk_valeur_note CHECK (valeur IN (-1, 1))
);

-- Index pour optimiser les recherches fréquentes
CREATE INDEX idx_touite_date ON TOUITE(date_publication DESC);
CREATE INDEX idx_touite_auteur ON TOUITE(id_auteur);
CREATE INDEX idx_suivre_suivi ON SUIVRE_UTILISATEUR(id_suivi);
CREATE INDEX idx_touitetag_tag ON TOUITE_TAG(id_tag);
