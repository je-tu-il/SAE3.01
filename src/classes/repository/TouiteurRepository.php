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
 * TouiteurRepository - Etape 07 (Marin)
 * Couche d'accÃ¨s aux donnÃ©es : consultation des touites et pagination
 */
class TouiteurRepository {
    
    private PDO $pdo;

    public function __construct() {
        $this->pdo = ConnectionFactory::makeConnection();
    }

    private function hydrateTouite(array $row): Touite {
        $image = null;
        if (!empty($row['id_image']) && !empty($row['image_chemin'])) {
            $image = new Image(
                (int)$row['id_image'],
                $row['image_chemin'],
                $row['image_desc'] ?? null
            );
        }
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

    public function getUserByPseudo(string $pseudo): ?User {
        $stmt = $this->pdo->prepare("SELECT * FROM UTILISATEUR WHERE pseudo = :p");
        $stmt->execute([':p' => $pseudo]);
        $r = $stmt->fetch();
        if (!$r) return null;
        return new User(
            (int)$r['id_user'],
            $r['pseudo'],
            $r['nom'] ?? '',
            $r['prenom'] ?? '',
            $r['email'] ?? '',
            (int)($r['nb_touites'] ?? 0),
            $r['date_inscription'] ?? ''
        );
    }

    public function getAllUsers(): array {
        $stmt = $this->pdo->query("SELECT * FROM UTILISATEUR ORDER BY pseudo ASC");
        $users = [];
        while ($r = $stmt->fetch()) {
            $users[] = new User(
                (int)$r['id_user'],
                $r['pseudo'],
                $r['nom'] ?? '',
                $r['prenom'] ?? '',
                $r['email'] ?? '',
                (int)($r['nb_touites'] ?? 0),
                $r['date_inscription'] ?? ''
            );
        }
        return $users;
    }

    // =========================================================================
    // Filtres de recherche par auteur et par tag (Ilyass) - F4 & F5
    // =========================================================================

    public function getTouitesByUser(string $pseudo, int $page = 1, int $limit = 5): array {
        $offset = max(0, ($page - 1) * $limit);
        $sql = "SELECT t.id_touite, t.texte, t.date_publication, t.note, t.id_auteur, t.id_image,
                       u.pseudo AS auteur_pseudo,
                       img.chemin AS image_chemin, img.description AS image_desc
                FROM TOUITE t
                INNER JOIN UTILISATEUR u ON t.id_auteur = u.id_user
                LEFT JOIN IMAGE img ON t.id_image = img.id_img
                WHERE u.pseudo = :pseudo
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
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM TOUITE t INNER JOIN UTILISATEUR u ON t.id_auteur = u.id_user WHERE u.pseudo = :pseudo");
        $stmt->execute([':pseudo' => $pseudo]);
        return (int)$stmt->fetchColumn();
    }

    public function getTouitesByTag(string $tagLibelle, int $page = 1, int $limit = 5): array {
        $offset = max(0, ($page - 1) * $limit);
        $cleanTag = ltrim($tagLibelle, '#');

        $sql = "SELECT t.id_touite, t.texte, t.date_publication, t.note, t.id_auteur, t.id_image,
                       u.pseudo AS auteur_pseudo,
                       img.chemin AS image_chemin, img.description AS image_desc
                FROM TOUITE t
                INNER JOIN UTILISATEUR u ON t.id_auteur = u.id_user
                INNER JOIN TOUITE_TAG tt ON t.id_touite = tt.id_touite
                INNER JOIN TAG tag ON tt.id_tag = tag.id_tag
                LEFT JOIN IMAGE img ON t.id_image = img.id_img
                WHERE tag.libelle = :tag
                ORDER BY t.date_publication DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tag', $cleanTag, PDO::PARAM_STR);
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
        $cleanTag = ltrim($tagLibelle, '#');
        $sql = "SELECT COUNT(*) 
                FROM TOUITE t
                INNER JOIN TOUITE_TAG tt ON t.id_touite = tt.id_touite
                INNER JOIN TAG tag ON tt.id_tag = tag.id_tag
                WHERE tag.libelle = :tag";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':tag' => $cleanTag]);
        return (int)$stmt->fetchColumn();
    }

    // =========================================================================
    // Publication de touite, tags regex et images (Jules) - F6, F8 & F13
    // =========================================================================

    public static function extractTags(string $text): array {
        preg_match_all('/(?<!&)#([\p{L}\p{N}_]+)/u', $text, $matches);
        if (!empty($matches[1])) {
            return array_values(array_unique(array_map('trim', $matches[1])));
        }
        return [];
    }

    public function publishTouite(string $pseudo, string $texte, ?string $imageChemin = null, ?string $imageDesc = null): int {
        $texte = trim($texte);
        if (empty($texte)) {
            throw new ValidationException("Le texte du touite ne peut pas Ãªtre vide.");
        }
        if (mb_strlen($texte, 'UTF-8') > 235) {
            throw new ValidationException("Le touite dÃ©passe la limite autorisÃ©e de 235 caractÃ¨res.");
        }

        $user = $this->getUserByPseudo($pseudo);
        if (!$user) {
            $this->createUser($pseudo, $pseudo, 'Utilisateur', $pseudo . '@touiteur.local');
            $user = $this->getUserByPseudo($pseudo);
        }

        $idImage = null;
        if (!empty($imageChemin)) {
            $stmtImg = $this->pdo->prepare("INSERT INTO IMAGE (chemin, description) VALUES (:ch, :desc)");
            $stmtImg->execute([
                ':ch' => $imageChemin,
                ':desc' => $imageDesc ?: 'Image attachÃ©e au touite'
            ]);
            $idImage = (int)$this->pdo->lastInsertId();
        }

        $now = date('Y-m-d H:i:s');
        $stmt = $this->pdo->prepare("INSERT INTO TOUITE (texte, date_publication, note, id_auteur, id_image) VALUES (:txt, :dt, 0, :auteur, :img)");
        $stmt->execute([
            ':txt' => $texte,
            ':dt' => $now,
            ':auteur' => $user->getId(),
            ':img' => $idImage
        ]);

        $touiteId = (int)$this->pdo->lastInsertId();

        $tags = self::extractTags($texte);
        foreach ($tags as $tagLibelle) {
            $tagId = $this->findOrCreateTag($tagLibelle);
            $stmtLink = $this->pdo->prepare("INSERT OR IGNORE INTO TOUITE_TAG (id_touite, id_tag) VALUES (:id_t, :id_tag)");
            $stmtLink->execute([':id_t' => $touiteId, ':id_tag' => $tagId]);
        }

        $this->pdo->prepare("UPDATE UTILISATEUR SET nb_touites = nb_touites + 1 WHERE id_user = :id")->execute([':id' => $user->getId()]);

        return $touiteId;
    }

    public function findOrCreateTag(string $libelle): int {
        $clean = ltrim(trim($libelle), '#');
        $stmt = $this->pdo->prepare("SELECT id_tag FROM TAG WHERE libelle = :lib");
        $stmt->execute([':lib' => $clean]);
        $id = $stmt->fetchColumn();
        if ($id) return (int)$id;

        $ins = $this->pdo->prepare("INSERT INTO TAG (libelle, description) VALUES (:lib, :desc)");
        $ins->execute([':lib' => $clean, ':desc' => "Publications sur le thÃ¨me #" . $clean]);
        return (int)$this->pdo->lastInsertId();
    }

    public function createUser(string $pseudo, string $prenom, string $nom, string $email): int {
        $now = date('Y-m-d H:i:s');
        $stmt = $this->pdo->prepare("INSERT INTO UTILISATEUR (pseudo, nom, prenom, email, nb_touites, date_inscription) VALUES (:p, :n, :pr, :e, 0, :dt)");
        $stmt->execute([':p' => $pseudo, ':n' => $nom, ':pr' => $prenom, ':e' => $email, ':dt' => $now]);
        return (int)$this->pdo->lastInsertId();
    }

    // =========================================================================
    // Ã‰valuations (+1 Like / -1 Dislike avec unicitÃ©) - Antho - F7
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
        if ($existingRating !== null) {
            if ($existingRating === $valeur) {
                $label = ($valeur > 0) ? '+1 (J\'aime)' : '-1 (Je n\'aime pas)';
                throw new ValidationException("Vous avez dÃ©jÃ  votÃ© {$label} pour ce touite. Votre note a bien Ã©tÃ© prise en compte !");
            }
            $delta = $valeur - $existingRating;
            $upd = $this->pdo->prepare("UPDATE EVALUER SET valeur = :val WHERE id_touite = :id_t AND id_user = :id_u");
            $upd->execute([':val' => $valeur, ':id_t' => $touiteId, ':id_u' => $user->getId()]);
            $this->pdo->prepare("UPDATE TOUITE SET note = note + :delta WHERE id_touite = :id_t")->execute([':delta' => $delta, ':id_t' => $touiteId]);
            return ['status' => 'success', 'modified' => true, 'new_score' => $touite->getNote() + $delta];
        }

        $stmt = $this->pdo->prepare("INSERT INTO EVALUER (id_touite, id_user, valeur) VALUES (:id_t, :id_u, :val)");
        $stmt->execute([':id_t' => $touiteId, ':id_u' => $user->getId(), ':val' => $valeur]);
        $this->pdo->prepare("UPDATE TOUITE SET note = note + :val WHERE id_touite = :id_t")->execute([':val' => $valeur, ':id_t' => $touiteId]);

        return ['status' => 'success', 'modified' => false, 'new_score' => $touite->getNote() + $valeur];
    }

    public function hasUserRated(int $touiteId, string $pseudo): ?int {
        $user = $this->getUserByPseudo($pseudo);
        if (!$user) return null;
        $stmt = $this->pdo->prepare("SELECT valeur FROM EVALUER WHERE id_touite = :id_t AND id_user = :id_u");
        $stmt->execute([':id_t' => $touiteId, ':id_u' => $user->getId()]);
        $val = $stmt->fetchColumn();
        return ($val !== false) ? (int)$val : null;
    }
}
