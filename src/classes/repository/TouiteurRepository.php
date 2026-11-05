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
}
