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

## Organisation du developpement

Le projet se deroule sur deux semaines :
- **Semaine 1 (30h PHP & Base de donnees) :**
  - Conception de la base de donnees (schemas relationnels Oracle/MySQL, triggers PL/SQL, rapport de conception relationnelle).
  - Modeles metier (User, Touite, Tag, Image) et hierarchie des exceptions.
  - Couche d'acces aux donnees (ConnectionFactory singleton PDO, Repository avec methodes F1 a F15).
  - Tests automatises CLI validant le backend.
- **Semaine 2 (6h Web Front-end) :**
  - Dispatcher frontal et controleurs Single Action.
  - Moteurs de rendu HTML securises anti-XSS.
  - Back-office d'administration independant (influenceurs et tendances).
  - Ergonomie, CSS responsive et finalisation.

---

## Structure initiale du projet

```
SAE_3.1/
├── src/classes/
│   └── Autoloader.php   # Autoloader conforme PSR-4
├── .gitignore
└── README.md
```

---

## Prerequis

- PHP 8.2 ou superieur
