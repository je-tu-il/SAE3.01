# Document de Rendu — SAE 3.1 : Développer une Application Web Sécurisée
**Plateforme : Touiteur.app**  
**BUT Informatique S3 — IUT Nancy-Charlemagne**  
**Année universitaire :** 2026-2027  

---

## 1. Informations Générales

- **Formation :** BUT Informatique — Semestre 3 (IUT Nancy-Charlemagne)
- **Membres du groupe (4 étudiants) :**
  1. **Jules** (Lead Web & Architecture : Dispatcher, Modèles, Navigation F1-F5, Intégration CSS/UX)
  2. **Anthony** (Base de Données : Modélisation 3NF/BCNF, Schémas Oracle/MySQL, Triggers PL/SQL, Repository PDO)
  3. **Ilyass** (Développeur Social & Médias : Publication F6, Évaluation F7, Mur & Abonnements F9-F12, Upload images F13)
  4. **Marin** (Back-Office & Sécurité : Application indépendante F16, Influenceurs F14, Tendances F15, Tests automatisés & Cryptographie)
- **URL du dépôt Git public :** [https://github.com/je-tu-il/SAE3.01](https://github.com/je-tu-il/SAE3.01)
- **URL de l'application sur Webetu :** [https://webetu.iutnc.univ-lorraine.fr/~e71373u/SAE3.01-main/public/index.php](https://webetu.iutnc.univ-lorraine.fr/~e71373u/SAE3.01-main/public/index.php)
- **URL du Back-Office sur Webetu :** [https://webetu.iutnc.univ-lorraine.fr/~e71373u/SAE3.01-main/public/admin.php](https://webetu.iutnc.univ-lorraine.fr/~e71373u/SAE3.01-main/public/admin.php)

---

## 2. Données Utiles pour le Test de l'Application

Conformément au sujet, **aucune authentification par mot de passe n'est exigée**.  
La plateforme dispose d'un **sélecteur de profil instantané** dans la barre de navigation permettant de basculer en un clic entre les comptes ci-dessous pour tester les interactions sociales (suivi, notation, mur, espace narcissique) :

| Pseudo | Nom Complet | Adresse Email | Nombre de touites | Rôle / Intérêt pour le test |
|---|---|---|---|---|
| `alice_tech` | Alice Dupont | `alice.dupont@univ-lorraine.fr` | 4 | Créatrice la plus suivie (4 followers, mur riche) |
| `bob_dev` | Bob Martin | `bob.martin@univ-lorraine.fr` | 3 | Auteur de touites avec tags et images associées |
| `charlie_cyber` | Charlie Bernard | `charlie.bernard@univ-lorraine.fr` | 3 | Spécialiste sécurité et cryptographie |
| `diana_ia` | Diana Thomas | `diana.thomas@univ-lorraine.fr` | 2 | Thématiques Intelligence Artificielle |
| `eva_design` | Eva Robert | `eva.robert@univ-lorraine.fr` | 1 | Thématiques design et ergonomie |

---

## 3. Tableau de Bord des Fonctionnalités Réalisées

Toutes les **16 fonctionnalités** demandées dans le sujet ont été intégralement implémentées, testées et validées.

| Réf. | Cas d'usage | Fonctionnalité | Type | Statut | Contribution |
|:---:|---|---|:---:|:---:|---|
| **F1** | Naviguer | **Afficher la liste des touites :** Ordre chronologique inverse, version courte. | Base | **100% Opérationnel** | Jules & Marin |
| **F2** | Naviguer | **Afficher un touite en détail :** Texte complet, score, date, auteur cliquable, tags et image associée. | Base | **100% Opérationnel** | Jules & Marin |
| **F3** | Naviguer | **Paginer la liste des touites :** Navigation page par page, précédent / suivant, limitation paramétrable. | Étendue | **100% Opérationnel** | Jules |
| **F4** | Naviguer | **Afficher les touites d'une personne :** Filtrage par auteur lors du clic sur son pseudo. | Base | **100% Opérationnel** | Jules |
| **F5** | Naviguer | **Afficher les touites d'un tag :** Filtrage par hashtag lors du clic sur un `#tag`. | Base | **100% Opérationnel** | Jules & Anthony |
| **F6** | Participer | **Publier un touite :** Rédaction limitée strictement à 235 caractères avec pseudo de l'auteur. | Base | **100% Opérationnel** | Ilyass |
| **F7** | Participer | **Évaluer un touite :** Votes (+1 like / -1 dislike), restriction stricte à un seul vote par utilisateur et par touite. | Étendue | **100% Opérationnel** | Anthony & Ilyass |
| **F8** | Participer | **Gestion des tags :** Extraction automatique par expression régulière des `#tags` lors de la publication et enregistrement en base. | Base | **100% Opérationnel** | Anthony |
| **F9** | Socialiser | **Mur d'un utilisateur :** Page d'accueil personnalisée agrégeant les touites des utilisateurs suivis et des tags abonnés. | Base | **100% Opérationnel** | Jules & Anthony |
| **F10** | Socialiser | **Suivre des utilisateurs :** Bouton suivre / ne plus suivre sur le profil ou la consultation d'un touite. | Base | **100% Opérationnel** | Jules & Ilyass |
| **F11** | Socialiser | **S'abonner à un tag :** Formulaire sur le mur ou bouton d'abonnement sur la page d'un tag. | Base | **100% Opérationnel** | Jules & Anthony |
| **F12** | Socialiser | **Utilisateur narcissique :** Espace affichant la liste de tous ses followers et le score moyen de ses publications. | Étendue | **100% Opérationnel** | Anthony & Marin |
| **F13** | Participer | **Associer une image à un touite :** Téléversement sécurisé de fichier image, affichage exclusif sur la vue détaillée. | Étendue | **100% Opérationnel** | Ilyass & Marin |
| **F14** | Rentabiliser | **Les influenceurs :** Back-office listant les utilisateurs les plus suivis, ordonnancés par nombre d'abonnés. | Base | **100% Opérationnel** | Ilyass & Marin |
| **F15** | Rentabiliser | **Les tendances :** Back-office listant les tags les plus mentionnés avec leur fréquence. | Étendue | **100% Opérationnel** | Anthony & Ilyass |
| **F16** | Rentabiliser | **Le back-office :** Application indépendante (`admin.php`) avec son propre dispatcher (`AdminDispatcher`). | Étendue | **100% Opérationnel** | Jules & Marin |

---

## 4. Architecture et Patrons de Conception Appliqués

1. **Autoloading PSR-4 :**
   - Classe `iutnc\touiteur\Autoloader` avec `spl_autoload_register`, isolant les espaces de noms sous `src/classes/`.
2. **Patron Dispatcher :**
   - `Dispatcher` (application publique `index.php`) et `AdminDispatcher` (back-office `admin.php`).
   - Mécanisme de gabarit global (`renderPage`) avec gestion des messages flash et sélecteur d'utilisateur actif.
3. **Patron Single Action :**
   - Chaque fonctionnalité est encapsulée dans une classe dédiée héritant de `Action` (ex. `PublishTouiteAction`, `UserWallAction`, `DisplayTouiteDetailAction`, etc.).
4. **Patron Repository (Accès aux données) :**
   - `TouiteurRepository` et `ConnectionFactory` : centralisation de l'accès à la base de données.
   - Support bivalent transparent : **MySQL / MariaDB** (cible Webetu) et **SQLite** (développement local autonome).
5. **Sécurité Cryptographique et Injections :**
   - **Protection anti-injection SQL :** 100% des requêtes passent par des requêtes préparées PDO (`prepare()` et `execute()`). Aucune variable utilisateur n'est injectée par concaténation.
   - **Protection anti-XSS :** Utilisation systématique de `htmlspecialchars($var, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')` sur toutes les données textuelles affichées.
   - **Sécurisation des uploads d'images :** Validation de taille (5 Mo max), validation de l'extension, validation du type MIME réel (`image/png`, `image/jpeg`, etc.), et renommage avec identifiant unique (`uniqid()`) pour proscrire tout écrasement ou traversée de répertoire.

---

## 5. Partie Base de Données (SAE BD)

Les livrables de la SAE Base de Données sont regroupés dans le dossier `bd/` :
- `rapport_conception_bd.md` : Rapport complet comprenant le dictionnaire des données, l'ensemble des dépendances fonctionnelles, le schéma relationnel en 3NF/BCNF et les justifications théoriques.
- `oracle_schema.sql` : Script DDL Oracle complet avec tables, contraintes d'intégrité et clés primaires/étrangères.
- `oracle_triggers.sql` : Script PL/SQL des 4 déclencheurs :
  1. `trg_maj_nb_touites` : Mise à jour automatique du compteur `nb_touites`.
  2. `trg_verif_suivre_user` : Interdiction d'auto-suivi et vérification d'email identique.
  3. `trg_immuable_auteur_touite` : Immutabilité de la paternité d'un touite.
  4. `trg_limite_touites_jour` : Compound Trigger Oracle limitant à 50 touites par jour (sans erreur de table mutante ORA-04091).
- `mysql_schema.sql` & `seed_data.sql` : Scripts opérationnels pour le déploiement sur Webetu (MariaDB).
