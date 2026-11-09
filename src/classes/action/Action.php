<?php

namespace iutnc\touiteur\action;

/**
 * Classe abstraite de base pour le patron Single Action
 */
abstract class Action {

    protected string $http_method;

    public function __construct() {
        $this->http_method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Exécute l'action et retourne le fragment HTML de contenu
     */
    abstract public function execute(): string;

    /**
     * Retourne le pseudo de l'utilisateur actif en session (ou null)
     */
    protected function getCurrentUser(): ?string {
        return $_SESSION['current_user'] ?? null;
    }

    /**
     * Définit l'utilisateur actif en session
     */
    protected function setCurrentUser(string $pseudo): void {
        $_SESSION['current_user'] = trim($pseudo);
    }

    /**
     * Ajoute un message flash en session
     */
    protected function addFlash(string $type, string $message): void {
        $_SESSION['flash'][$type][] = $message;
    }

    /**
     * Effectue une redirection HTTP propre
     */
    protected function redirect(string $action, array $params = []): void {
        $query = array_merge(['action' => $action], $params);
        $url = 'index.php?' . http_build_query($query);
        header("Location: {$url}");
        exit();
    }
}
