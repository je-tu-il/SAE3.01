# SAE 3.01 - Developpement d'une application web (Touiteur)
**Plateforme : Touiteur.app**  
**BUT Informatique S3 - IUT Nancy-Charlemagne (2026-2027)**  

---

## Presentation du projet

Ce projet a pour objectif de concevoir et de realiser une application web de micromessagerie inspiree de Twitter, nommee **Touiteur**.

Le developpement s'appuie sur une demarche stricte en PHP Objet, sans framework externe, en suivant les patrons de conception et les principes d'architecture logicielle vus en cours :
- Architecture MVC orientee Single Action et Dispatcher frontal
- Patron Repository avec requetes preparees PDO (securite SQL)
- Autoloader PSR-4
- Conception de la base de donnees relationnelle (Oracle et MySQL)

---

## Membres de l'equipe

- Jules (Lead dev, architecture et coordination)
- Anthony Gabillet (@GabilletAnthony)
- Marin (@MarinFKT)
- Ilyass Roussi (@IlyassRS3)

---

## Avancement du projet (Semaine 1 - Base de donnees & Modeles)

Les elements suivants sont mis en place :
1. **Base de donnees relationnelle** :
   - Schéma relationnel DDL Oracle Database (`bd/oracle_schema.sql`)
   - Déclencheurs PL/SQL Oracle (`bd/oracle_triggers.sql`)
   - Schéma relationnel MySQL / SQLite (`bd/mysql_schema.sql`, `bd/seed_sqlite.sql`)
   - Données de test riches (`bd/seed_data.sql`)
   - Rapport de conception relationnelle (`bd/rapport_conception_bd.md`)
2. **Entites metier** :
   - `User`, `Touite`, `Tag`, `Image` dans `src/classes/model/`
3. **Exceptions metier** :
   - `TouiteurException`, `DatabaseException`, `ValidationException` dans `src/classes/exceptions/`
4. **Acces aux donnees** :
   - Fabrique de connexion singleton PDO dans `src/classes/repository/ConnectionFactory.php`
   - Fichier de configuration dans `config/db.ini`

---

## Structure actuelle du projet

```
SAE_3.1/
├── bd/
│   ├── oracle_schema.sql
│   ├── oracle_triggers.sql
│   ├── mysql_schema.sql
│   ├── seed_data.sql
│   ├── seed_sqlite.sql
│   └── rapport_conception_bd.md
├── config/
│   ├── db.ini.example
│   └── db.ini
├── src/classes/
│   ├── Autoloader.php
│   ├── exceptions/
│   ├── model/
│   └── repository/
│       └── ConnectionFactory.php
├── .gitignore
└── README.md
```

---

## Configuration de la base de donnees

La connexion se configure dans le fichier `config/db.ini` :
```ini
driver = sqlite
database = bd/touiteur.sqlite
```
Pour utiliser MySQL ou Oracle, modifier les paramètres correspondants dans `config/db.ini`.
