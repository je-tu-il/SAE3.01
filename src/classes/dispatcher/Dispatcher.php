<?php

namespace iutnc\touiteur\dispatcher;

use iutnc\touiteur\action\Action;
use iutnc\touiteur\action\DisplayTouitesAction;
use iutnc\touiteur\action\DisplayTouiteDetailAction;
use iutnc\touiteur\action\DisplayUserTouitesAction;
use iutnc\touiteur\action\DisplayTagTouitesAction;
use iutnc\touiteur\action\PublishTouiteAction;
use iutnc\touiteur\action\RateTouiteAction;
use iutnc\touiteur\action\UserWallAction;
use iutnc\touiteur\action\FollowUserAction;
use iutnc\touiteur\action\FollowTagAction;
use iutnc\touiteur\action\NarcissisticUserAction;
use iutnc\touiteur\action\SwitchUserAction;
use iutnc\touiteur\action\LegalNoticeAction;
use iutnc\touiteur\repository\TouiteurRepository;

/**
 * Dispatcher principal de Touiteur.app
 */
class Dispatcher {

    private string $actionName;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->actionName = $_GET['action'] ?? 'liste-touites';
    }

    /**
     * Traite la requête et affiche la page complète
     */
    public function run(): void {
        $action = match ($this->actionName) {
            // Noms de pages en français (avec aliases anglais pour compatibilité)
            'liste-touites', 'list-touites'               => new DisplayTouitesAction(),
            'detail-touite', 'touite-detail'              => new DisplayTouiteDetailAction(),
            'touites-utilisateur', 'user-touites'         => new DisplayUserTouitesAction(),
            'touites-tag', 'tag-touites'                  => new DisplayTagTouitesAction(),
            'publier-touite', 'publish-touite'            => new PublishTouiteAction(),
            'evaluer-touite', 'rate-touite'               => new RateTouiteAction(),
            'mon-mur', 'user-wall', 'home', 'wall'        => new UserWallAction(),
            'suivre-utilisateur', 'follow-user'           => new FollowUserAction(),
            'suivre-tag', 'follow-tag'                    => new FollowTagAction(),
            'espace-narcissique', 'narcissistic', 'profil'=> new NarcissisticUserAction(),
            'changer-utilisateur', 'switch-user'          => new SwitchUserAction(),
            'mentions-legales', 'rgpd', 'cookies'         => new LegalNoticeAction(),
            default                                       => new DisplayTouitesAction(),
        };

        $contentHtml = $action->execute();
        $this->renderPage($contentHtml);
    }

    /**
     * Enrobe le contenu dans le gabarit HTML global
     */
    private function renderPage(string $content): void {
        $currentUser = $_SESSION['current_user'] ?? null;
        $flashMessages = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);

        // Récupérer les utilisateurs pour le sélecteur rapide de profil dans la navbar
        $userPickerOptions = '';
        try {
            $repo = new TouiteurRepository();
            $allUsers = $repo->getAllUsers();
            foreach ($allUsers as $u) {
                $sel = ($currentUser === $u->getPseudo()) ? 'selected' : '';
                $pseudoVal = htmlspecialchars($u->getPseudo(), ENT_QUOTES, 'UTF-8');
                $userPickerOptions .= "<option value=\"{$pseudoVal}\" {$sel}>@{$pseudoVal}</option>";
            }
        } catch (\Exception $e) {
            // Ignorer si la BD n'est pas encore prête
        }

        $activeAction = $this->actionName;
        $navFeedActive = in_array($activeAction, ['liste-touites', 'list-touites'], true) ? 'active' : '';
        $navWallActive = in_array($activeAction, ['mon-mur', 'user-wall', 'home', 'wall'], true) ? 'active' : '';
        $navPublishActive = in_array($activeAction, ['publier-touite', 'publish-touite'], true) ? 'active' : '';
        $navNarcissisticActive = in_array($activeAction, ['espace-narcissique', 'narcissistic', 'profil'], true) ? 'active' : '';

        // Construction des alertes flash
        $flashHtml = '';
        if (!empty($flashMessages)) {
            $flashHtml .= '<div class="flash-messages-container">';
            foreach ($flashMessages as $type => $messages) {
                $alertClass = ($type === 'error') ? 'alert-danger' : 'alert-success';
                foreach ($messages as $msg) {
                    $escapedMsg = htmlspecialchars($msg, ENT_QUOTES, 'UTF-8');
                    $flashHtml .= "<div class=\"alert {$alertClass}\">{$escapedMsg}</div>";
                }
            }
            $flashHtml .= '</div>';
        }

        $currentUserDisplay = $currentUser 
            ? '<span class="current-user-pill">Connecté : <strong>@' . htmlspecialchars($currentUser, ENT_QUOTES, 'UTF-8') . '</strong></span>'
            : '<span class="current-user-pill text-muted">Aucun profil actif</span>';

        echo <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Touiteur.app — Plateforme de micromessages sécurisée</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="main-header">
        <div class="header-inner container">
            <div class="brand">
                <a href="index.php" class="brand-link">
                    
                    <span class="brand-name">Touiteur<span class="brand-dot">.app</span></span>
                </a>
            </div>

            <nav class="main-nav">
                <a href="index.php?action=liste-touites" class="nav-link {$navFeedActive}">Fil public</a>
                <a href="index.php?action=mon-mur" class="nav-link {$navWallActive}">Mon Mur</a>
                <a href="index.php?action=publier-touite" class="nav-link btn-nav-publish {$navPublishActive}">+ Publier un touite</a>
                <a href="index.php?action=espace-narcissique" class="nav-link {$navNarcissisticActive}">Espace Narcissique</a>
            </nav>

            <div class="user-switcher-bar">
                {$currentUserDisplay}
                <form method="POST" action="index.php?action=changer-utilisateur" class="quick-switch-form">
                    <input type="hidden" name="redirect" value="{$activeAction}">
                    <select name="pseudo" onchange="this.form.submit()" class="user-select-nav" title="Changer d'utilisateur">
                        <option value="">Changer de compte...</option>
                        {$userPickerOptions}
                    </select>
                </form>
                <a href="admin.php" class="btn btn-outline btn-sm" title="Accéder à l'espace d'administration">Administration &rarr;</a>
            </div>
        </div>
    </header>

    <main class="main-content container">
        {$flashHtml}
        {$content}
    </main>

    <footer class="main-footer">
        <div class="container footer-inner">
            <p>&copy; 2026 Touiteur.app &bull; SAE Développer une application web sécurisée &bull; BUT Informatique S3 IUT Nancy-Charlemagne</p>
            <p class="footer-links">
                <a href="index.php?action=liste-touites">Accueil</a> &bull;
                <a href="index.php?action=mentions-legales">Mentions légales & RGPD</a> &bull;
                <a href="index.php?action=cookies">Cookies & Confidentialité</a> &bull;
                <a href="admin.php">Administration</a>
            </p>
        </div>
    </footer>

    <script src="assets/js/app.js"></script>
</body>
</html>
HTML;
    }
}
