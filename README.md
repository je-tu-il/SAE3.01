# SAE 3.01 - Developper une Application Web Securisee
**Plateforme : Touiteur.app**  
**BUT Informatique S3 - IUT Nancy-Charlemagne (2026-2027)**  

---

## Presentation du Projet

Touiteur.app est une application web de micromessagerie inspiree de Twitter, developpee en **PHP Oriente Objet** strict, respectant l'architecture logicielle et les bonnes pratiques enseignees en BUT Informatique S3 a l'IUT Nancy-Charlemagne :
- Autoloader PSR-4 sans dependance externe
- Patron d'architecture Dispatcher
- Patron de conception Single Action
- Patron Repository avec requetes preparees PDO (protection contre les injections SQL)
- Patron Renderer separant la logique metier de la generation HTML (protection contre les failles XSS)
- Base de donnees relationnelle concue en 3NF/BCNF avec declencheurs Oracle PL/SQL et MySQL

---

## Membres de l'equipe

- Jules (Lead dev, architecture et coordination)
- Anthony Gabillet (@GabilletAnthony)
- Marin (@MarinFKT)
- Ilyass Roussi (@IlyassRS3)

---

## Fonctionnalites Implementees (16/16)

### A. Naviguer sur Touiteur.app
1. **Liste des touites recents** : Affichage par ordre chronologique inverse en version courte.
2. **Detail d'un touite** : Affichage complet avec texte integral, score, date, auteur, hashtags et image eventuelle.
3. **Pagination dynamique** : Navigation page par page avec controles precedent / suivant.
4. **Touites par utilisateur** : Filtrage de toutes les publications d'un auteur lors d'un clic sur son identifiant.
5. **Touites par tag** : Filtrage thematique lors d'un clic sur un hashtag `#tag`.

### B. Participer a Touiteur.app
6. **Publication d'un touite** : Redaction avec nom d'utilisateur et contrainte stricte a 235 caracteres.
7. **Evaluation d'un touite** : Systeme de vote (+1 / -1) avec regle stricte d'un seul vote par utilisateur et par touite.
8. **Extraction automatique des tags** : Detection des `#tags` dans le corps du message et association automatique en base de donnees.
9. **Association d'images** : Televersement securise de visuels (JPEG, PNG, GIF, WEBP jusqu'a 5 Mo), verifies par type MIME et affiches exclusivement en vue detaillee.

### C. Socialiser avec Touiteur.app
10. **Le Mur personnel** : Page d'accueil personnalisee agregeant les touites des auteurs suivis et des hashtags favoris.
11. **Abonnement a des auteurs** : Suivi et desabonnement d'utilisateurs en un clic.
12. **Abonnement a des tags** : Suivi de thematiques ciblees via le mur ou depuis une page de tag.
13. **Espace Narcissique** : Consultation de la liste de ses suiveurs et calcul du score moyen de ses publications.

### D. Rentabiliser Touiteur.app (Back-Office Independant)
14. **Les Influenceurs** : Classement des createurs les plus suivis de la plateforme.
15. **Les Tendances** : Statistiques des tags les plus populaires avec frequence d'utilisation.
16. **Application Back-Office independante** : Interface d'administration isolee (`admin.php`) fonctionnant avec son propre dispatcher (`AdminDispatcher`).

---

## Structure du Repertoire

```
SAE_3.1/
├── bd/
│   ├── rapport_conception_bd.md   # Rapport complet SAE Base de Donnees
│   ├── oracle_schema.sql          # Schema relationnel DDL Oracle Database
│   ├── oracle_triggers.sql        # Declencheurs PL/SQL Oracle
│   ├── mysql_schema.sql           # Schema pour MySQL / MariaDB
│   ├── seed_data.sql              # Donnees de test realistes pour MySQL
│   ├── seed_sqlite.sql            # Schema et donnees de test pour SQLite
│   └── touiteur.sqlite            # Base SQLite prete a l'emploi
├── config/
│   ├── db.ini.example             # Exemple de configuration
│   └── db.ini                     # Configuration active locale (SQLite par defaut)
├── public/
│   ├── index.php                  # Point d'entree public de Touiteur.app
│   ├── admin.php                  # Point d'entree du Back-Office d'administration
│   ├── assets/
│   │   ├── css/style.css          # Feuille de styles sobre et responsive
│   │   └── js/app.js              # Compteur dynamique de caracteres et ergonomie
│   └── uploads/                   # Repertoire de stockage securise des images
├── src/classes/
│   ├── Autoloader.php             # Autoloader PSR-4
│   ├── action/                    # Classes de controle Single Action
│   ├── dispatcher/                # Dispatchers (Front public et Back-Office)
│   ├── exceptions/                # Exceptions metier et techniques
│   ├── model/                     # Modeles metier (Touite, User, Tag, Image)
│   ├── render/                    # Classes de rendu HTML (Renderer)
│   └── repository/                # Acces aux donnees et ConnectionFactory (PDO)
├── tests/
│   └── test_repository.php        # Suite de 20 tests automatises PHP CLI
├── README.md                      # Documentation technique du projet
└── RENDU.md                       # Document de synthese pour le depot Arche
```

---

## Demarrage Rapide en Local

### 1. Prerequis
- PHP 8.2 ou superieur avec extension `pdo_sqlite` (ou `pdo_mysql`).

### 2. Lancer le serveur web PHP
Depuis la racine du projet, executer :
```bash
php -S localhost:8000 -t public
```

### 3. Acceder a l'application
- Application publique : http://localhost:8000/index.php
- Back-Office Administrateur : http://localhost:8000/admin.php

### 4. Lancer les tests automatises
```bash
php tests/test_repository.php
```
