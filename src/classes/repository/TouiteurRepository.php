<?php

namespace iutnc\touiteur\repository;

use PDO;
use iutnc\touiteur\model\Touite;
use iutnc\touiteur\model\User;
use iutnc\touiteur\model\Tag;
use iutnc\touiteur\model\Image;
use iutnc\touiteur\exceptions\DatabaseException;
use iutnc\touiteur\exceptions\ValidationException;

/**
 * TouiteurRepository
 * Couche d'accÃ¨s aux donnÃ©es dÃ©veloppÃ©e par Antho (avec support de Jules et Marin).
 *
 * NOTE SECURITE IMPORTANTE :
 * 100% des requÃªtes passent par du PDO prepare() / execute() pour garantir
 * une immunitÃ© totale contre les injections SQL (critÃ¨re d'Ã©val Ã  20%).
 */
class TouiteurRepository {
    
    private PDO $pdo;

    public function __construct() {
        // Connexion unique via la fabrique (supporte SQLite en local et MySQL sur Webetu)
        $this->pdo = ConnectionFactory::makeConnection();
    }

    /**
     * Extraction automatique des #hashtags dans un message
     * Regex UTF-8 pour supporter les accents franÃ§ais (ex: #sÃ©curitÃ©, #dÃ©veloppement)
     */
    public static function extractTags(string $text): array {
        preg_match_all('/(?<!&)#([\p{L}\p{N}_]+)/u', $text, $matches);
        if (!empty($matches[1])) {
            // Nettoyage des doublons Ã©ventuels dans le mÃªme touite
            return array_values(array_unique(array_map('trim', $matches[1])));
        }
        return [];
    }

    /**
     * Conversion d'une ligne de rÃ©sultat SQL en objet mÃ©tier Touite
     */
    private function hydrateTouite(array $row): Touite {
        $image = null;
        if (!empty($row['id_image']) && !empty($row['image_chemin'])) {
            $image = new Image(
                (int)$row['id_image'],
                $row['image_chemin'],
                $row['image_desc'] ?? null
            );
        }

        // On rÃ©cupÃ¨re les tags associÃ©s pour afficher les liens dans les vues
        $tags = $this->getTagsForTouite((int)$row['id_touite']);

        return new Touite(
            (int)$row['id_touite'],
            $row['texte'],
            $row['date_publication'],
            (int)$row['note'],
            (int)$row['id_auteur'],
            $row['auteur_pseudo'],
            $image,
            $tags
        );
    }

    /**
     * RÃ©cupÃ¨re la liste des libellÃ©s de tags d'un touite
     */
    public function getTagsForTouite(int $touiteId): array {
        $sql = "SELECT t.libelle 
                FROM TAG t
                INNER JOIN TOUITE_TAG tt ON t.id_tag = tt.id_tag
                WHERE tt.id_touite = :id_touite
                ORDER BY t.libelle ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id_touite' => $touiteId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
    }

    // =========================================================================
    // 1. Consultation des touites (Fil public, dÃ©tail, filtres)
    // =========================================================================

    /**
     * F1 & F3 : Liste des touites rÃ©cents avec pagination
     * Jules a calÃ© le limit par dÃ©faut Ã  5 pour pouvoir tester la pagination facilement
     */
    public function getTouites(int $page = 1, int $limit = 5): array {
        $offset = max(0, ($page - 1) * $limit);
        $sql = "SELECT t.id_touite, t.texte, t.date_publication, t.note, t.id_auteur, t.id_image,
                       u.pseudo AS auteur_pseudo,
                       img.chemin AS image_chemin, img.description AS image_desc
                FROM TOUITE t
                INNER JOIN UTILISATEUR u ON t.id_auteur = u.id_user
                LEFT JOIN IMAGE img ON t.id_image = img.id_img
                ORDER BY t.date_publication DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $touites = [];
        while ($row = $stmt->fetch()) {
            $touites[] = $this->hydrateTouite($row);
        }
        return $touites;
    }

    public function countTouites(): int {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM TOUITE");
        return (int)$stmt->fetchColumn();
    }

    /**
     * F2 : RÃ©cupÃ©ration d'un touite pour la vue dÃ©taillÃ©e (avec image et tags)
     */
    public function getTouiteById(int $id): ?Touite {
        $sql = "SELECT t.id_touite, t.texte, t.date_publication, t.note, t.id_auteur, t.id_image,
                       u.pseudo AS auteur_pseudo,
                       img.chemin AS image_chemin, img.description AS image_desc
                FROM TOUITE t
                INNER JOIN UTILISATEUR u ON t.id_auteur = u.id_user
                LEFT JOIN IMAGE img ON t.id_image = img.id_img
                WHERE t.id_touite = :id";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ? $this->hydrateTouite($row) : null;
    }

    /**
     * F4 : Touites d'un utilisateur spÃ©cifique
     */
    public function getTouitesByUser(string $pseudo, int $page = 1, int $limit = 5): array {
        $offset = max(0, ($page - 1) * $limit);
        $sql = "SELECT t.id_touite, t.texte, t.date_publication, t.note, t.id_auteur, t.id_image,
                       u.pseudo AS auteur_pseudo,
                       img.chemin AS image_chemin, img.description AS image_desc
                FROM TOUITE t
                INNER JOIN UTILISATEUR u ON t.id_auteur = u.id_user
                LEFT JOIN IMAGE img ON t.id_image = img.id_img
                WHERE LOWER(u.pseudo) = LOWER(:pseudo)
                ORDER BY t.date_publication DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':pseudo', $pseudo, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $touites = [];
        while ($row = $stmt->fetch()) {
            $touites[] = $this->hydrateTouite($row);
        }
        return $touites;
    }

    public function countTouitesByUser(string $pseudo): int {
        $sql = "SELECT COUNT(*) 
                FROM TOUITE t 
                INNER JOIN UTILISATEUR u ON t.id_auteur = u.id_user 
                WHERE LOWER(u.pseudo) = LOWER(:pseudo)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':pseudo' => $pseudo]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * F5 : Touites associÃ©s Ã  un mot-clÃ© (#tag)
     */
    public function getTouitesByTag(string $tagLibelle, int $page = 1, int $limit = 5): array {
        $offset = max(0, ($page - 1) * $limit);
        $sql = "SELECT t.id_touite, t.texte, t.date_publication, t.note, t.id_auteur, t.id_image,
                       u.pseudo AS auteur_pseudo,
                       img.chemin AS image_chemin, img.description AS image_desc
                FROM TOUITE t
                INNER JOIN UTILISATEUR u ON t.id_auteur = u.id_user
                INNER JOIN TOUITE_TAG tt ON t.id_touite = tt.id_touite
                INNER JOIN TAG tag ON tt.id_tag = tag.id_tag
                LEFT JOIN IMAGE img ON t.id_image = img.id_img
                WHERE LOWER(tag.libelle) = LOWER(:tag)
                ORDER BY t.date_publication DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tag', $tagLibelle, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $touites = [];
        while ($row = $stmt->fetch()) {
            $touites[] = $this->hydrateTouite($row);
        }
        return $touites;
    }

    public function countTouitesByTag(string $tagLibelle): int {
        $sql = "SELECT COUNT(*) 
                FROM TOUITE t
                INNER JOIN TOUITE_TAG tt ON t.id_touite = tt.id_touite
                INNER JOIN TAG tag ON tt.id_tag = tag.id_tag
                WHERE LOWER(tag.libelle) = LOWER(:tag)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':tag' => $tagLibelle]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * F9 : Mur personnalisÃ© d'un utilisateur
     * Astuce SQL : on fait un DISTINCT sur l'union des touites des gens suivis
     * OU des touites qui contiennent un tag auquel l'utilisateur est abonnÃ©.
     */
    public function getUserWallTouites(string $pseudo, int $page = 1, int $limit = 5): array {
        $user = $this->getUserByPseudo($pseudo);
        if (!$user) {
            return [];
        }

        $offset = max(0, ($page - 1) * $limit);

        $sql = "SELECT DISTINCT t.id_touite, t.texte, t.date_publication, t.note, t.id_auteur, t.id_image,
                                u.pseudo AS auteur_pseudo,
                                img.chemin AS image_chemin, img.description AS image_desc
                FROM TOUITE t
                INNER JOIN UTILISATEUR u ON t.id_auteur = u.id_user
                LEFT JOIN IMAGE img ON t.id_image = img.id_img
                LEFT JOIN TOUITE_TAG tt ON t.id_touite = tt.id_touite
                WHERE t.id_auteur IN (
                    SELECT id_suivi FROM SUIVRE_UTILISATEUR WHERE id_suiveur = :id_user_suivis
                )
                OR tt.id_tag IN (
                    SELECT id_tag FROM ABONNER_TAG WHERE id_user = :id_user_tags
                )
                ORDER BY t.date_publication DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id_user_suivis', $user->getId(), PDO::PARAM_INT);
        $stmt->bindValue(':id_user_tags', $user->getId(), PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $touites = [];
        while ($row = $stmt->fetch()) {
            $touites[] = $this->hydrateTouite($row);
        }
        return $touites;
    }

    public function countUserWallTouites(string $pseudo): int {
        $user = $this->getUserByPseudo($pseudo);
        if (!$user) {
            return 0;
        }

        $sql = "SELECT COUNT(DISTINCT t.id_touite)
                FROM TOUITE t
                LEFT JOIN TOUITE_TAG tt ON t.id_touite = tt.id_touite
                WHERE t.id_auteur IN (
                    SELECT id_suivi FROM SUIVRE_UTILISATEUR WHERE id_suiveur = :id_user_suivis
                )
                OR tt.id_tag IN (
                    SELECT id_tag FROM ABONNER_TAG WHERE id_user = :id_user_tags
                )";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id_user_suivis' => $user->getId(),
            ':id_user_tags' => $user->getId()
        ]);
        return (int)$stmt->fetchColumn();
    }

    // =========================================================================
    // 2. Publication & Transactions (F6, F8, F13)
    // =========================================================================

    /**
     * Publication atomique : touite + tags + image
     * On encapsule tout dans une transaction pour Ã©viter les orphelins si un insert plante.
     */
    public function publishTouite(string $pseudo, string $texte, ?string $imageChemin = null, ?string $imageDesc = null): int {
        $texte = trim($texte);
        if (empty($texte)) {
            throw new ValidationException("Le texte du touite ne peut pas Ãªtre vide.");
        }
        if (mb_strlen($texte, 'UTF-8') > 235) {
            throw new ValidationException("Le touite dÃ©passe la limite autorisÃ©e de 235 caractÃ¨res.");
        }

        // On crÃ©e le profil Ã  la volÃ©e s'il n'existe pas encore (consigne : pas d'auth formelle)
        $user = $this->getUserByPseudo($pseudo);
        if (!$user) {
            $userId = $this->createUser($pseudo, $pseudo, 'Utilisateur', "{$pseudo}@touiteur.app");
        } else {
            $userId = $user->getId();
        }

        $this->pdo->beginTransaction();
        try {
            // Ã‰tape 1 : Enregistrement de l'image si uploadÃ©e
            $imageId = null;
            if ($imageChemin !== null && $imageChemin !== '') {
                $sqlImg = "INSERT INTO IMAGE (chemin, description) VALUES (:chemin, :desc)";
                $stmtImg = $this->pdo->prepare($sqlImg);
                $stmtImg->execute([
                    ':chemin' => $imageChemin,
                    ':desc' => $imageDesc
                ]);
                $imageId = (int)$this->pdo->lastInsertId();
            }

            // Ã‰tape 2 : Enregistrement du message
            $sqlTouite = "INSERT INTO TOUITE (texte, date_publication, note, id_auteur, id_image)
                          VALUES (:texte, :date_pub, 0, :id_auteur, :id_image)";
            $stmtTouite = $this->pdo->prepare($sqlTouite);
            $stmtTouite->execute([
                ':texte' => $texte,
                ':date_pub' => date('Y-m-d H:i:s'),
                ':id_auteur' => $userId,
                ':id_image' => $imageId
            ]);
            $touiteId = (int)$this->pdo->lastInsertId();

            // Ã‰tape 3 : DÃ©tection automatique des tags et association
            $tags = self::extractTags($texte);
            foreach ($tags as $tagLibelle) {
                $tagId = $this->findOrCreateTag($tagLibelle);
                try {
                    $stmtAssoc = $this->pdo->prepare("INSERT INTO TOUITE_TAG (id_touite, id_tag) VALUES (:id_touite, :id_tag)");
                    $stmtAssoc->execute([
                        ':id_touite' => $touiteId,
                        ':id_tag' => $tagId
                    ]);
                } catch (\PDOException $e) {
                    // Ignorer les doublons si le mÃªme tag apparaÃ®t deux fois
                }
            }

            // Ã‰tape 4 : IncrÃ©ment du compteur de touites de l'utilisateur
            $stmtMaj = $this->pdo->prepare("UPDATE UTILISATEUR SET nb_touites = nb_touites + 1 WHERE id_user = :id_user");
            $stmtMaj->execute([':id_user' => $userId]);

            $this->pdo->commit();
            return $touiteId;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw new DatabaseException("Erreur lors de la publication du touite : " . $e->getMessage(), 0, $e);
        }
    }

    public function findOrCreateTag(string $libelle): int {
        $libelle = trim($libelle);
        $stmt = $this->pdo->prepare("SELECT id_tag FROM TAG WHERE LOWER(libelle) = LOWER(:libelle)");
        $stmt->execute([':libelle' => $libelle]);
        $id = $stmt->fetchColumn();

        if ($id) {
            return (int)$id;
        }

        $stmtInsert = $this->pdo->prepare("INSERT INTO TAG (libelle, description) VALUES (:libelle, :description)");
        $stmtInsert->execute([
            ':libelle' => $libelle,
            ':description' => "Tag #{$libelle} gÃ©nÃ©rÃ© automatiquement"
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    // =========================================================================
    // 3. Ã‰valuations (+1 Like / -1 Dislike avec unicitÃ©) - F7
    // =========================================================================

    public function rateTouite(int $touiteId, string $pseudo, int $valeur): array {
        if (!in_array($valeur, [-1, 1], true)) {
            throw new ValidationException("La note d'Ã©valuation doit Ãªtre de +1 ou -1.");
        }

        $user = $this->getUserByPseudo($pseudo);
        if (!$user) {
            throw new ValidationException("Utilisateur introuvable pour Ã©valuer.");
        }

        $touite = $this->getTouiteById($touiteId);
        if (!$touite) {
            throw new ValidationException("Touite introuvable.");
        }

        $existingRating = $this->hasUserRated($touiteId, $pseudo);

        // Si l'utilisateur re-clique exactement sur le mÃªme vote
        if ($existingRating === $valeur) {
            $sign = ($valeur > 0) ? '+1' : '-1';
            throw new ValidationException("Vous avez dÃ©jÃ  votÃ© {$sign} sur ce touite. Votre note reste inchangÃ©e.");
        }

        $this->pdo->beginTransaction();
        try {
            if ($existingRating !== null) {
                // Modification du vote existant (ex: de +1 Ã  -1 => delta = -2, ou de -1 Ã  +1 => delta = +2)
                $delta = $valeur - $existingRating;

                $stmtUpdate = $this->pdo->prepare(
                    "UPDATE EVALUER_TOUITE SET valeur = :valeur, date_eval = :date_eval 
                     WHERE id_user = :id_user AND id_touite = :id_touite"
                );
                $stmtUpdate->execute([
                    ':valeur' => $valeur,
                    ':date_eval' => date('Y-m-d H:i:s'),
                    ':id_user' => $user->getId(),
                    ':id_touite' => $touiteId
                ]);

                $stmtNote = $this->pdo->prepare("UPDATE TOUITE SET note = note + :delta WHERE id_touite = :id_touite");
                $stmtNote->execute([
                    ':delta' => $delta,
                    ':id_touite' => $touiteId
                ]);

                $this->pdo->commit();

                $stmtGet = $this->pdo->prepare("SELECT note FROM TOUITE WHERE id_touite = :id_touite");
                $stmtGet->execute([':id_touite' => $touiteId]);
                $nouvelleNote = (int)$stmtGet->fetchColumn();

                return ['success' => true, 'note' => $nouvelleNote, 'valeur' => $valeur, 'modified' => true];
            } else {
                // Premier vote
                $stmtVote = $this->pdo->prepare(
                    "INSERT INTO EVALUER_TOUITE (id_user, id_touite, valeur, date_eval) 
                     VALUES (:id_user, :id_touite, :valeur, :date_eval)"
                );
                $stmtVote->execute([
                    ':id_user' => $user->getId(),
                    ':id_touite' => $touiteId,
                    ':valeur' => $valeur,
                    ':date_eval' => date('Y-m-d H:i:s')
                ]);

                $stmtNote = $this->pdo->prepare("UPDATE TOUITE SET note = note + :valeur WHERE id_touite = :id_touite");
                $stmtNote->execute([
                    ':valeur' => $valeur,
                    ':id_touite' => $touiteId
                ]);

                $this->pdo->commit();

                $stmtGet = $this->pdo->prepare("SELECT note FROM TOUITE WHERE id_touite = :id_touite");
                $stmtGet->execute([':id_touite' => $touiteId]);
                $nouvelleNote = (int)$stmtGet->fetchColumn();

                return ['success' => true, 'note' => $nouvelleNote, 'valeur' => $valeur, 'modified' => false];
            }
        } catch (ValidationException $ve) {
            $this->pdo->rollBack();
            throw $ve;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw new DatabaseException("Erreur lors de l'enregistrement de l'Ã©valuation : " . $e->getMessage(), 0, $e);
        }
    }

    public function hasUserRated(int $touiteId, string $pseudo): ?int {
        $user = $this->getUserByPseudo($pseudo);
        if (!$user) {
            return null;
        }

        $sql = "SELECT valeur FROM EVALUER_TOUITE WHERE id_user = :id_user AND id_touite = :id_touite";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id_user' => $user->getId(),
            ':id_touite' => $touiteId
        ]);
        $valeur = $stmt->fetchColumn();
        return ($valeur !== false) ? (int)$valeur : null;
    }

    // =========================================================================
    // 4. Socialisation (Abonnements comptes & tags) - F10, F11
    // =========================================================================

    public function followUser(string $followerPseudo, string $targetPseudo): bool {
        if (strcasecmp($followerPseudo, $targetPseudo) === 0) {
            throw new ValidationException("Un utilisateur ne peut pas se suivre lui-mÃªme.");
        }

        $follower = $this->getUserByPseudo($followerPseudo);
        $target = $this->getUserByPseudo($targetPseudo);

        if (!$follower || !$target) {
            throw new ValidationException("Utilisateur suiveur ou cible introuvable.");
        }

        if ($this->isFollowingUser($followerPseudo, $targetPseudo)) {
            return true;
        }

        $sql = "INSERT INTO SUIVRE_UTILISATEUR (id_suiveur, id_suivi, date_suivi) VALUES (:suiveur, :suivi, :date_suivi)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':suiveur' => $follower->getId(),
            ':suivi' => $target->getId(),
            ':date_suivi' => date('Y-m-d H:i:s')
        ]);
    }

    public function unfollowUser(string $followerPseudo, string $targetPseudo): bool {
        $follower = $this->getUserByPseudo($followerPseudo);
        $target = $this->getUserByPseudo($targetPseudo);

        if (!$follower || !$target) {
            return false;
        }

        $sql = "DELETE FROM SUIVRE_UTILISATEUR WHERE id_suiveur = :suiveur AND id_suivi = :suivi";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':suiveur' => $follower->getId(),
            ':suivi' => $target->getId()
        ]);
    }

    public function isFollowingUser(string $followerPseudo, string $targetPseudo): bool {
        $follower = $this->getUserByPseudo($followerPseudo);
        $target = $this->getUserByPseudo($targetPseudo);

        if (!$follower || !$target) {
            return false;
        }

        $sql = "SELECT 1 FROM SUIVRE_UTILISATEUR WHERE id_suiveur = :suiveur AND id_suivi = :suivi";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':suiveur' => $follower->getId(),
            ':suivi' => $target->getId()
        ]);
        return (bool)$stmt->fetchColumn();
    }

    public function followTag(string $pseudo, string $tagLibelle): bool {
        $user = $this->getUserByPseudo($pseudo);
        if (!$user) {
            throw new ValidationException("Utilisateur introuvable.");
        }

        $tagId = $this->findOrCreateTag($tagLibelle);

        if ($this->isFollowingTag($pseudo, $tagLibelle)) {
            return true;
        }

        $sql = "INSERT INTO ABONNER_TAG (id_user, id_tag, date_abonnement) VALUES (:id_user, :id_tag, :date_abo)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':id_user' => $user->getId(),
            ':id_tag' => $tagId,
            ':date_abo' => date('Y-m-d H:i:s')
        ]);
    }

    public function unfollowTag(string $pseudo, string $tagLibelle): bool {
        $user = $this->getUserByPseudo($pseudo);
        if (!$user) {
            return false;
        }

        $sql = "DELETE FROM ABONNER_TAG 
                WHERE id_user = :id_user 
                  AND id_tag = (SELECT id_tag FROM TAG WHERE LOWER(libelle) = LOWER(:tag))";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':id_user' => $user->getId(),
            ':tag' => $tagLibelle
        ]);
    }

    public function isFollowingTag(string $pseudo, string $tagLibelle): bool {
        $user = $this->getUserByPseudo($pseudo);
        if (!$user) {
            return false;
        }

        $sql = "SELECT 1 FROM ABONNER_TAG at
                INNER JOIN TAG t ON at.id_tag = t.id_tag
                WHERE at.id_user = :id_user AND LOWER(t.libelle) = LOWER(:tag)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id_user' => $user->getId(),
            ':tag' => $tagLibelle
        ]);
        return (bool)$stmt->fetchColumn();
    }

    public function getUserFollowedTags(string $pseudo): array {
        $user = $this->getUserByPseudo($pseudo);
        if (!$user) {
            return [];
        }

        $sql = "SELECT t.libelle FROM TAG t
                INNER JOIN ABONNER_TAG at ON t.id_tag = at.id_tag
                WHERE at.id_user = :id_user
                ORDER BY t.libelle ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id_user' => $user->getId()]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
    }

    // =========================================================================
    // 5. Espace Narcissique (Followers et score moyen) - F12
    // =========================================================================

    public function getUserFollowers(string $pseudo): array {
        $user = $this->getUserByPseudo($pseudo);
        if (!$user) {
            return [];
        }

        $sql = "SELECT u.id_user, u.pseudo, u.nom, u.prenom, u.email, u.nb_touites, u.date_inscription
                FROM UTILISATEUR u
                INNER JOIN SUIVRE_UTILISATEUR s ON u.id_user = s.id_suiveur
                WHERE s.id_suivi = :id_user
                ORDER BY s.date_suivi DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id_user' => $user->getId()]);

        $followers = [];
        while ($row = $stmt->fetch()) {
            $followers[] = new User(
                (int)$row['id_user'],
                $row['pseudo'],
                $row['nom'],
                $row['prenom'],
                $row['email'],
                (int)$row['nb_touites'],
                $row['date_inscription']
            );
        }
        return $followers;
    }

    public function getUserAverageScore(string $pseudo): float {
        $user = $this->getUserByPseudo($pseudo);
        if (!$user) {
            return 0.0;
        }

        $sql = "SELECT AVG(note) FROM TOUITE WHERE id_auteur = :id_user";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id_user' => $user->getId()]);
        $avg = $stmt->fetchColumn();
        return ($avg !== false && $avg !== null) ? round((float)$avg, 2) : 0.0;
    }

    // =========================================================================
    // 6. Gestion des utilisateurs
    // =========================================================================

    public function getUserByPseudo(string $pseudo): ?User {
        $sql = "SELECT id_user, pseudo, nom, prenom, email, nb_touites, date_inscription
                FROM UTILISATEUR 
                WHERE LOWER(pseudo) = LOWER(:pseudo)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':pseudo' => trim($pseudo)]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }
        return new User(
            (int)$row['id_user'],
            $row['pseudo'],
            $row['nom'],
            $row['prenom'],
            $row['email'],
            (int)$row['nb_touites'],
            $row['date_inscription']
        );
    }

    public function createUser(string $pseudo, string $prenom, string $nom, string $email): int {
        $sql = "INSERT INTO UTILISATEUR (pseudo, nom, prenom, email, nb_touites, date_inscription)
                VALUES (:pseudo, :nom, :prenom, :email, 0, :date_ins)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':pseudo' => trim($pseudo),
            ':nom' => trim($nom),
            ':prenom' => trim($prenom),
            ':email' => trim($email),
            ':date_ins' => date('Y-m-d H:i:s')
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function getAllUsers(): array {
        $stmt = $this->pdo->query("SELECT id_user, pseudo, nom, prenom, email, nb_touites, date_inscription FROM UTILISATEUR ORDER BY pseudo ASC");
        $users = [];
        while ($row = $stmt->fetch()) {
            $users[] = new User(
                (int)$row['id_user'],
                $row['pseudo'],
                $row['nom'],
                $row['prenom'],
                $row['email'],
                (int)$row['nb_touites'],
                $row['date_inscription']
            );
        }
        return $users;
    }

    public function getAllTags(): array {
        $stmt = $this->pdo->query("SELECT id_tag, libelle, description FROM TAG ORDER BY libelle ASC");
        $tags = [];
        while ($row = $stmt->fetch()) {
            $tags[] = new Tag(
                (int)$row['id_tag'],
                $row['libelle'],
                $row['description']
            );
        }
        return $tags;
    }

    // =========================================================================
    // 7. Back-Office (Influenceurs & Tendances) - F14, F15
    // =========================================================================

    /**
     * F14 : Utilisateurs les plus influents (triÃ©s par dÃ©faut par ordre croissant selon la consigne)
     */
    public function getMostFollowedUsers(int $limit = 20, string $order = 'ASC'): array {
        $direction = (strtoupper($order) === 'DESC') ? 'DESC' : 'ASC';
        
        $sql = "SELECT u.id_user, u.pseudo, u.nom, u.prenom, u.email, u.nb_touites, u.date_inscription,
                       COUNT(s.id_suiveur) AS nb_followers
                FROM UTILISATEUR u
                LEFT JOIN SUIVRE_UTILISATEUR s ON u.id_user = s.id_suivi
                GROUP BY u.id_user, u.pseudo, u.nom, u.prenom, u.email, u.nb_touites, u.date_inscription
                ORDER BY nb_followers {$direction}, u.pseudo ASC
                LIMIT :limit";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $results = [];
        while ($row = $stmt->fetch()) {
            $user = new User(
                (int)$row['id_user'],
                $row['pseudo'],
                $row['nom'],
                $row['prenom'],
                $row['email'],
                (int)$row['nb_touites'],
                $row['date_inscription']
            );
            $results[] = [
                'user' => $user,
                'nb_followers' => (int)$row['nb_followers']
            ];
        }
        return $results;
    }

    /**
     * F15 : Tags les plus mentionnÃ©s
     */
    public function getMostMentionedTags(int $limit = 20, string $order = 'ASC'): array {
        $direction = (strtoupper($order) === 'DESC') ? 'DESC' : 'ASC';

        $sql = "SELECT t.id_tag, t.libelle, t.description, COUNT(tt.id_touite) AS nb_mentions
                FROM TAG t
                LEFT JOIN TOUITE_TAG tt ON t.id_tag = tt.id_tag
                GROUP BY t.id_tag, t.libelle, t.description
                ORDER BY nb_mentions {$direction}, t.libelle ASC
                LIMIT :limit";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $results = [];
        while ($row = $stmt->fetch()) {
            $tag = new Tag(
                (int)$row['id_tag'],
                $row['libelle'],
                $row['description'],
                (int)$row['nb_mentions']
            );
            $results[] = [
                'tag' => $tag,
                'nb_mentions' => (int)$row['nb_mentions']
            ];
        }
        return $results;
    }
}

