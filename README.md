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

## Bilan de la Semaine 1 (30h PHP & Base de donnees)

La premiere semaine de developpement backend est achevee. L'ensemble de la logique metier et de la persistance est fonctionnel et valide par tests automatises :

1. **Base de donnees relationnelle** :
   - Schemas relationnels Oracle et MySQL en 3NF/BCNF
   - Triggers PL/SQL Oracle pour l'integrite et les contraintes metier
   - Donnees de tests coherentes et realistes
2. **Couche Metier** :
   - Entites `User`, `Touite`, `Tag`, `Image` avec assainissement des donnees
   - Hierarchie des exceptions metier
3. **Couche Repository (methodes F1 a F15)** :
   - Consultation des touites et pagination
   - Filtrage par auteur et par tag
   - Publication de touite, extraction regex des hashtags et liaisons images
   - Evaluations (+1/-1) et contrainte stricte d'unicite du vote
   - Abonnements aux utilisateurs et aux tags, mur personnalise
   - Espace narcissique (liste des suiveurs, score moyen des publications)
   - Requetes de rentabilite : classement des influenceurs et tendances des hashtags
4. **Validation par tests automatises** :
   - Suite de 20 tests automatisés couvrant l'ensemble des cas nominaux et d'erreur

---

## Structure du projet a la fin de la Semaine 1

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
│       ├── ConnectionFactory.php
│       └── TouiteurRepository.php
├── tests/
│   └── test_repository.php
├── .gitignore
└── README.md
```

---

## Execution des tests automatises

Pour lancer la suite de tests PHP CLI :
```bash
php tests/test_repository.php
```
Tous les tests doivent afficher `[OK]`.
