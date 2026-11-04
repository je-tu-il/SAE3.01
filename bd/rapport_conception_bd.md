# Rapport de Conception de Base de Données : Touiteur.app
**SAE BUT Informatique S3 — IUT Nancy-Charlemagne**  
**Ressource :** Conception et Administration de Bases de Données  
**Date :** Novembre 2026  
**Auteurs :** Équipe Étudiante BUT Informatique S3  

---

## 1. Contexte et Objectifs

Touiteur.app est une plateforme d'échange de micromessages textuels appelés **touites**.  
Chaque touite est limité à 235 caractères, est horodaté et associé à un utilisateur unique. Il peut contenir des hashtags (marqués par `#`) et éventuellement une image illustrative.  

Les fonctionnalités sociales permettent aux utilisateurs de :
- Suivre d'autres utilisateurs pour recevoir leurs publications.
- S'abonner à des thématiques ciblées via des hashtags.
- Noter les touites consultés (+1 / -1).
- Consulter un mur personnalisé regroupant les touites de leurs abonnements (utilisateurs et tags).

Ce rapport détaille la démarche formelle de conception de la base de données :
1. Établissement du **dictionnaire des données**.
2. Formalisation des **dépendances fonctionnelles (DF)**.
3. Dérivation du **schéma relationnel en 3e Forme Normale (3NF / BCNF)**.
4. Script **SQL Oracle DDL** de création des tables et contraintes.
5. Implémentation des **déclencheurs PL/SQL Oracle** répondant aux règles de gestion avancées.

---

## 2. Dictionnaire des Données

| Code Donnée | Désignation / Description | Type conceptuel | Domaine / Format | Règle de calcul / Contrainte |
|---|---|---|---|---|
| `id_user` | Identifiant unique de l'utilisateur | Entier | Clé primaire artificielle | > 0 |
| `pseudo` | Nom d'utilisateur unique sur la plateforme | Texte | Alphanumérique (50 car.) | Unique, Non nul |
| `nom` | Nom de famille de l'utilisateur | Texte | Alphabétique (100 car.) | Non nul |
| `prenom` | Prénom de l'utilisateur | Texte | Alphabétique (100 car.) | Non nul |
| `email` | Adresse électronique de l'utilisateur | Texte | Email RFC 5322 (150 car.) | Unique, Non nul |
| `nb_touites` | Nombre total de touites publiés | Entier | Entier >= 0 | Calculé / MAJ par trigger |
| `date_inscription` | Date et heure de création du compte | Date/Heure | Timestamp | `SYSDATE` par défaut |
| `id_touite` | Identifiant unique d'un touite | Entier | Clé primaire artificielle | > 0 |
| `texte_touite` | Contenu textuel du touite | Texte | Max 235 caractères | Longueur <= 235 |
| `date_publication`| Date et heure de publication du touite | Date/Heure | Timestamp | `SYSDATE` par défaut |
| `note_touite` | Score / évaluation globale cumulée du touite | Entier | Entier relatif | Somme des évaluations (+1/-1) |
| `id_image` | Identifiant unique de l'image | Entier | Clé primaire artificielle | > 0 |
| `chemin_image` | Chemin / nom du fichier sur le serveur | Texte | Chemin de fichier (255 car.) | Non nul |
| `desc_image` | Description textuelle / alt de l'image | Texte | Texte libre (255 car.) | Optionnel |
| `id_tag` | Identifiant unique d'un tag | Entier | Clé primaire artificielle | > 0 |
| `libelle_tag` | Mot-clé sans le symbole `#` | Texte | Alphanumérique (50 car.) | Unique, Non nul |
| `desc_tag` | Description thématique du tag | Texte | Texte libre (255 car.) | Optionnel |
| `valeur_note` | Valeur d'une évaluation par un utilisateur | Entier | Entier relatif | `valeur IN (-1, 1)` |
| `date_suivi` | Date de mise en place d'un abonnement utilisateur | Date/Heure | Timestamp | `SYSDATE` par défaut |
| `date_abontag` | Date d'abonnement à un tag | Date/Heure | Timestamp | `SYSDATE` par défaut |
| `date_eval` | Date à laquelle une note a été attribuée | Date/Heure | Timestamp | `SYSDATE` par défaut |

---

## 3. Dépendances Fonctionnelles (DF)

Soit l'ensemble des attributs du système. L'analyse des règles de gestion conduit à la couverture minimale suivante :

### 3.1. Entités principales

- **Utilisateur :**
  - $id\_user \rightarrow pseudo, nom, prenom, email, nb\_touites, date\_inscription$
  - $pseudo \rightarrow id\_user$ (clé candidate alternative)
  - $email \rightarrow id\_user$ (clé candidate alternative)

- **Image :**
  - $id\_image \rightarrow chemin\_image, desc\_image$

- **Touite :**
  - $id\_touite \rightarrow texte\_touite, date\_publication, note\_touite, id\_auteur, id\_image$  
    *(Chaque touite a un auteur unique et au plus une image associée).*

- **Tag :**
  - $id\_tag \rightarrow libelle\_tag, desc\_tag$
  - $libelle\_tag \rightarrow id\_tag$ (clé candidate alternative)

### 3.2. Relations et associations

- **Liaison Touite - Tag (plusieurs tags par touite, plusieurs touites par tag) :**
  - $(id\_touite, id\_tag) \rightarrow \emptyset$

- **Suivi d'utilisateurs (réflexive n:m) :**
  - $(id\_suiveur, id\_suivi) \rightarrow date\_suivi$

- **Abonnement à un tag (n:m) :**
  - $(id\_user, id\_tag) \rightarrow date\_abontag$

- **Évaluation d'un touite (un utilisateur note un touite une seule fois) :**
  - $(id\_user, id\_touite) \rightarrow valeur\_note, date\_eval$

---

## 4. Schéma Relationnel et Normalisation

En appliquant l'algorithme de décomposition et de synthèse relationnelle :

### 4.1. Relations obtenues (Notation Relationnelle)

1. **`UTILISATEUR`** (<u>id_user</u>, pseudo, nom, prenom, email, nb_touites, date_inscription)  
   - *Clés alternatives :* `pseudo`, `email`
2. **`IMAGE`** (<u>id_img</u>, chemin, description)  
3. **`TOUITE`** (<u>id_touite</u>, texte, date_publication, note, #id_auteur, #id_image)  
   - `id_auteur` référence `UTILISATEUR(id_user)` [NOT NULL]
   - `id_image` référence `IMAGE(id_img)` [NULLABLE]
4. **`TAG`** (<u>id_tag</u>, libelle, description)  
   - *Clé alternative :* `libelle`
5. **`TOUITE_TAG`** (<u>#id_touite, #id_tag</u>)  
   - `id_touite` référence `TOUITE(id_touite)`
   - `id_tag` référence `TAG(id_tag)`
6. **`SUIVRE_UTILISATEUR`** (<u>#id_suiveur, #id_suivi</u>, date_suivi)  
   - `id_suiveur` référence `UTILISATEUR(id_user)`
   - `id_suivi` référence `UTILISATEUR(id_user)`
   - *Contrainte :* `id_suiveur != id_suivi`
7. **`ABONNER_TAG`** (<u>#id_user, #id_tag</u>, date_abonnement)  
   - `id_user` référence `UTILISATEUR(id_user)`
   - `id_tag` référence `TAG(id_tag)`
8. **`EVALUER_TOUITE`** (<u>#id_user, #id_touite</u>, valeur, date_eval)  
   - `id_user` référence `UTILISATEUR(id_user)`
   - `id_touite` référence `TOUITE(id_touite)`
   - *Contrainte :* `valeur IN (-1, 1)`

### 4.2. Preuve de la Forme Normale (3NF et BCNF)
- **1ère Forme Normale (1NF) :** Tous les attributs sont atomiques et chaque table possède une clé primaire identifiée.
- **2ème Forme Normale (2NF) :** Tout attribut non-clé dépend pleinement et entièrement de la totalité de la clé primaire (aucune dépendance partielle pour les clés composées).
- **3ème Forme Normale (3NF) :** Il n'existe aucune dépendance transitive entre attributs non-clés ($X \rightarrow Y$ implique que $X$ est une super-clé ou que $Y$ fait partie d'une clé candidate).
- **Forme Normale de Boyce-Codd (BCNF) :** Pour toute dépendance élémentaire non triviale $X \rightarrow A$, $X$ est une sur-clé. Le schéma est donc en **BCNF**.

---

## 5. Scripts SQL Oracle

Le fichier complet de création des relations se trouve dans `bd/oracle_schema.sql`.

---

## 6. Déclencheurs PL/SQL Oracle

Le fichier complet des déclencheurs se trouve dans `bd/oracle_triggers.sql`.

### 6.1. Déclencheur 1 : Mise à jour du compteur de touites
- **Événement :** `AFTER INSERT OR DELETE ON TOUITE FOR EACH ROW`
- **Rôle :** Incrémente `nb_touites` lors de la publication d'un touite et le décrémente en cas de suppression, maintenant la cohérence de l'agrégat sans recalcul lourd.

### 6.2. Déclencheur 2 : Interdiction d'auto-suivi et contrôle d'email
- **Événement :** `BEFORE INSERT OR UPDATE ON SUIVRE_UTILISATEUR FOR EACH ROW`
- **Rôle :** Empêche qu'un utilisateur se suive lui-même (`id_suiveur = id_suivi`), et vérifie par requête que les deux utilisateurs ne partagent pas la même adresse email (détection de multi-comptes / doublons).

### 6.3. Déclencheur 3 : Immutabilité de la paternité d'un touite
- **Événement :** `BEFORE UPDATE OF id_auteur ON TOUITE FOR EACH ROW`
- **Rôle :** Déclenche une exception `RAISE_APPLICATION_ERROR(-20003, ...)` si une tentative de modification de `id_auteur` survient. Une fois publié, un touite est indissociable de son auteur original.

### 6.4. Déclencheur 4 : Limite anti-flood de 50 touites par jour
- **Événement :** `FOR INSERT ON TOUITE COMPOUND TRIGGER`
- **Problème résolu :** En PL/SQL standard, interroger la table `TOUITE` dans un trigger ligne à ligne génère l'erreur de table mutante `ORA-04091`.
- **Solution mise en œuvre :** Utilisation d'un **Compound Trigger** (déclencheur composé) Oracle. Les publications sont mémorisées en phase `BEFORE EACH ROW` dans une table PL/SQL en mémoire, puis vérifiées en phase `AFTER STATEMENT` sans conflit de mutation. Si le quota de 50 touites le jour même (`TRUNC(date_publication) = TRUNC(SYSDATE)`) est dépassé, une exception `-20004` est levée.
