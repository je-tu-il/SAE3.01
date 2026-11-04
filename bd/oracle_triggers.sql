-- =============================================================================
-- SAE BUT Informatique S3 - IUT Nancy-Charlemagne
-- Sujet Base de Données : Touiteur.app
-- Fichier : oracle_triggers.sql
-- Auteur : Antho (avec relecture Marin & Jules)
-- =============================================================================

-- -----------------------------------------------------------------------------
-- Trigger 1 (Antho) : MAJ automatique du compteur nb_touites d'un profil
-- Quand un touite est inséré ou supprimé, on évite un COUNT(*) lourd en mettant
-- directement à jour la colonne nb_touites dans la table UTILISATEUR.
-- -----------------------------------------------------------------------------
CREATE OR REPLACE TRIGGER trg_maj_nb_touites
AFTER INSERT OR DELETE ON TOUITE
FOR EACH ROW
BEGIN
    IF INSERTING THEN
        -- On incrémente le compteur de l'auteur
        UPDATE UTILISATEUR
        SET nb_touites = nb_touites + 1
        WHERE id_user = :NEW.id_auteur;
    ELSIF DELETING THEN
        -- Décrémentation sans passer en négatif si jamais
        UPDATE UTILISATEUR
        SET nb_touites = GREATEST(0, nb_touites - 1)
        WHERE id_user = :OLD.id_auteur;
    END IF;
END;
/

-- -----------------------------------------------------------------------------
-- Trigger 2 (Antho) : Contrôle d'auto-suivi et vérification d'email
-- Règles du sujet :
-- 1) Impossible de se suivre soi-même (id_suiveur != id_suivi)
-- 2) Impossible de suivre un utilisateur qui partage le même email (multi-compte)
-- -----------------------------------------------------------------------------
CREATE OR REPLACE TRIGGER trg_verif_suivre_user
BEFORE INSERT OR UPDATE ON SUIVRE_UTILISATEUR
FOR EACH ROW
DECLARE
    v_email_suiveur UTILISATEUR.email%TYPE;
    v_email_suivi   UTILISATEUR.email%TYPE;
BEGIN
    -- Test basique sur les IDs
    IF :NEW.id_suiveur = :NEW.id_suivi THEN
        RAISE_APPLICATION_ERROR(-20001, 'Erreur : un utilisateur ne peut pas se suivre lui-meme.');
    END IF;

    -- On va chercher les emails des deux personnes pour comparer
    SELECT email INTO v_email_suiveur 
    FROM UTILISATEUR 
    WHERE id_user = :NEW.id_suiveur;

    SELECT email INTO v_email_suivi 
    FROM UTILISATEUR 
    WHERE id_user = :NEW.id_suivi;

    -- Comparaison insensible à la casse
    IF LOWER(TRIM(v_email_suiveur)) = LOWER(TRIM(v_email_suivi)) THEN
        RAISE_APPLICATION_ERROR(-20002, 'Erreur : interdiction de suivre un compte possedant la meme adresse email.');
    END IF;
EXCEPTION
    WHEN NO_DATA_FOUND THEN
        RAISE_APPLICATION_ERROR(-20005, 'Erreur : utilisateur introuvable dans la base.');
END;
/

-- -----------------------------------------------------------------------------
-- Trigger 3 (Antho) : Paternité d'un touite immuable
-- Une fois publié, personne ne peut transférer son touite à un autre utilisateur.
-- -----------------------------------------------------------------------------
CREATE OR REPLACE TRIGGER trg_immuable_auteur_touite
BEFORE UPDATE OF id_auteur ON TOUITE
FOR EACH ROW
BEGIN
    IF :NEW.id_auteur != :OLD.id_auteur THEN
        RAISE_APPLICATION_ERROR(-20003, 'Erreur : l''auteur initial d''un touite ne peut pas etre modifie.');
    END IF;
END;
/

-- -----------------------------------------------------------------------------
-- Trigger 4 (Antho & Marin) : Quota anti-flood de 50 touites max par jour
-- IMPORTANT : Dans un trigger FOR EACH ROW standard, faire un SELECT COUNT(*) sur
-- la table TOUITE déclenche l'erreur Oracle ORA-04091 (table mutante) !
-- Pour contourner proprement le problème comme vu en cours, on passe par un
-- COMPOUND TRIGGER : on stocke les IDs en BEFORE EACH ROW et on check en AFTER STATEMENT.
-- -----------------------------------------------------------------------------
CREATE OR REPLACE TRIGGER trg_limite_touites_jour
FOR INSERT ON TOUITE
COMPOUND TRIGGER

    -- Tableau temporaire en mémoire de session
    TYPE t_record_auteur IS RECORD (
        id_auteur NUMBER,
        date_pub  DATE
    );
    TYPE t_tab_auteurs IS TABLE OF t_record_auteur INDEX BY PLS_INTEGER;
    v_auteurs t_tab_auteurs;
    v_count   NUMBER;

    -- Etape A : on vide la collection avant l'ordre SQL
    BEFORE STATEMENT IS
    BEGIN
        v_auteurs.DELETE;
    END BEFORE STATEMENT;

    -- Etape B : pour chaque ligne qui arrive, on enregistre qui publie
    BEFORE EACH ROW IS
    BEGIN
        v_auteurs(v_auteurs.COUNT + 1).id_auteur := :NEW.id_auteur;
        v_auteurs(v_auteurs.COUNT).date_pub      := NVL(:NEW.date_publication, SYSDATE);
    END BEFORE EACH ROW;

    -- Etape C : une fois l'insertion finie, la table n'est plus mutante, on peut compter !
    AFTER STATEMENT IS
    BEGIN
        FOR i IN 1 .. v_auteurs.COUNT LOOP
            SELECT COUNT(*) INTO v_count
            FROM TOUITE
            WHERE id_auteur = v_auteurs(i).id_auteur
              AND TRUNC(date_publication) = TRUNC(v_auteurs(i).date_pub);

            IF v_count > 50 THEN
                RAISE_APPLICATION_ERROR(-20004, 'Erreur : quota journalier depasse (limite a 50 touites/jour).');
            END IF;
        END LOOP;
    END AFTER STATEMENT;

END trg_limite_touites_jour;
/
