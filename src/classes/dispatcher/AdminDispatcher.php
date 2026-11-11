<?php

namespace iutnc\touiteur\dispatcher;

use iutnc\touiteur\action\AdminInfluencersAction;
use iutnc\touiteur\action\AdminTrendsAction;

/**
 * Dispatcher indépendant pour le Back-Office (Fonctionnalité 16)
 */
class AdminDispatcher {

    private string $actionName;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->actionName = $_GET['action'] ?? 'influenceurs';
    }

    public function run(): void {
        $action = match ($this->actionName) {
            'influenceurs', 'influencers' => new AdminInfluencersAction(),
            'tendances', 'trends'         => new AdminTrendsAction(),
            default                       => new AdminInfluencersAction(),
        };

        $contentHtml = $action->execute();
        $this->renderPage($contentHtml);
    }

    private function renderPage(string $content): void {
        $activeAction = $this->actionName;
        $navInfluencersActive = in_array($activeAction, ['influenceurs', 'influencers'], true) ? 'active' : '';
        $navTrendsActive = in_array($activeAction, ['tendances', 'trends'], true) ? 'active' : '';

        echo <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration Touiteur — Influenceurs et Tendances</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="admin-body">
    <header class="admin-header">
        <div class="header-inner container">
            <div class="brand">
                <a href="admin.php" class="brand-link">
                    
                    <span class="brand-name">Touiteur <span class="brand-sub">Administration</span></span>
                </a>
            </div>

            <nav class="main-nav admin-nav">
                <a href="admin.php?action=influenceurs" class="nav-link {$navInfluencersActive}">Influenceurs</a>
                <a href="admin.php?action=tendances" class="nav-link {$navTrendsActive}">Tendances</a>
            </nav>

            <div class="admin-top-actions">
                <a href="index.php" class="btn btn-outline btn-sm">&larr; Retour à l'application Touiteur.app</a>
            </div>
        </div>
    </header>

    <main class="main-content container">
        <div class="page-header">
            <h2>Espace d'administration</h2>
            <p class="lead">Consultation des métriques d'influence et analyse des tendances de tags.</p>
        </div>
        {$content}
    </main>

    <footer class="main-footer">
        <div class="container footer-inner">
            <p>&copy; 2026 Touiteur.app &bull; Espace d'administration &bull; BUT Informatique S3</p>
            <p class="footer-links">
                <a href="index.php?action=liste-touites">&larr; Retour à l'application</a> &bull;
                <a href="index.php?action=mentions-legales">Mentions légales & RGPD</a>
            </p>
        </div>
    </footer>
</body>
</html>
HTML;
    }
}
