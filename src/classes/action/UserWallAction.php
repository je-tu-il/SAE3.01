<?php

namespace iutnc\touiteur\action;

use iutnc\touiteur\repository\TouiteurRepository;
use iutnc\touiteur\render\TouiteRenderer;
use iutnc\touiteur\render\Renderer;

/**
 * Action : Afficher le mur personnalisé d'un utilisateur (Abonnements d'utilisateurs et de tags)
 * Cas d'usage : Socialiser (Fonctionnalité 9 et 11)
 */
class UserWallAction extends Action {

    public function execute(): string {
        $repo = new TouiteurRepository();
        $currentUser = $this->getCurrentUser();

        // Si l'utilisateur n'est pas encore identifié en session, proposer un sélecteur
        if ($currentUser === null) {
            return $this->renderUserSelector($repo);
        }

        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = 5;

        $total = $repo->countUserWallTouites($currentUser);
        $totalPages = max(1, (int)ceil($total / $limit));
        if ($page > $totalPages && $totalPages > 0) {
            $page = $totalPages;
        }

        $touites = $repo->getUserWallTouites($currentUser, $page, $limit);
        $followedTags = $repo->getUserFollowedTags($currentUser);

        $escapedUser = htmlspecialchars($currentUser, ENT_QUOTES, 'UTF-8');

        $html = '<div class="wall-header-card">';
        $html .= '<div class="wall-welcome">';
        $html .= "<h2>Mur personnel de @{$escapedUser}</h2>";
        $html .= '<p class="lead">Publications de vos créateurs suivis et de vos thématiques favorites.</p>';
        $html .= '</div>';

        // Boîte d'abonnement rapide à un tag (Fonctionnalité 11)
        $html .= '<div class="wall-tag-box">';
        $html .= '<h4>S\'abonner à un #tag</h4>';
        $html .= '<form method="POST" action="index.php?action=suivre-tag" class="tag-subscription-form">';
        $html .= '<input type="text" name="tag" placeholder="Ex: dev, php, web..." required class="form-control form-control-sm">';
        $html .= '<input type="hidden" name="op" value="follow">';
        $html .= '<input type="hidden" name="redirect" value="mon-mur">';
        $html .= '<button type="submit" class="btn btn-primary btn-sm">+ S\'abonner</button>';
        $html .= '</form>';

        if (!empty($followedTags)) {
            $html .= '<div class="followed-tags-list">';
            $html .= '<span class="tag-label">Tags suivis :</span> ';
            foreach ($followedTags as $t) {
                $escapedT = htmlspecialchars($t, ENT_QUOTES, 'UTF-8');
                $urlT = urlencode($t);
                $html .= "<span class=\"tag-chip\"><a href=\"index.php?action=touites-tag&tag={$urlT}\">#{$escapedT}</a>";
                $html .= "<form method=\"POST\" action=\"index.php?action=suivre-tag\" class=\"inline-chip-form\">";
                $html .= "<input type=\"hidden\" name=\"tag\" value=\"{$escapedT}\">";
                $html .= "<input type=\"hidden\" name=\"op\" value=\"unfollow\">";
                $html .= "<input type=\"hidden\" name=\"redirect\" value=\"mon-mur\">";
                $html .= "<button type=\"submit\" class=\"btn-chip-remove\" title=\"Se désabonner\">&times;</button>";
                $html .= "</form></span> ";
            }
            $html .= '</div>';
        }
        $html .= '</div>';
        $html .= '</div>';

        if (empty($touites)) {
            $html .= '<div class="empty-state">';
            
            $html .= '<h3>Aucun touite pour le moment</h3>';
            $html .= '<p>Votre fil est vide. Suivez d\'autres profils ou des hashtags pour composer votre mur !</p>';
            $html .= '<a href="index.php?action=publier-touite" class="btn btn-primary" style="margin-top:12px; display:inline-block;">+ Publier le premier touite</a>';
            $html .= '</div>';
            return $html;
        }

        $html .= '<div class="touites-feed">';
        foreach ($touites as $touite) {
            $renderer = new TouiteRenderer($touite, $currentUser);
            $html .= $renderer->render(Renderer::COMPACT);
        }
        $html .= '</div>';

        // Pagination
        if ($totalPages > 1) {
            $html .= '<nav class="pagination-container">';
            $html .= '<ul class="pagination">';
            if ($page > 1) {
                $prev = $page - 1;
                $html .= "<li class=\"page-item\"><a href=\"index.php?action=mon-mur&page={$prev}\" class=\"page-link\">&laquo; Précédent</a></li>";
            }
            $html .= "<li class=\"page-item active\"><span class=\"page-link\">Page {$page} sur {$totalPages}</span></li>";
            if ($page < $totalPages) {
                $next = $page + 1;
                $html .= "<li class=\"page-item\"><a href=\"index.php?action=mon-mur&page={$next}\" class=\"page-link\">Suivant &raquo;</a></li>";
            }
            $html .= '</ul></nav>';
        }

        return $html;
    }

    private function renderUserSelector(TouiteurRepository $repo): string {
        $users = $repo->getAllUsers();
        $html = '<div class="selector-card">';
        $html .= '<h2>Bienvenue sur votre mur personnel</h2>';
        $html .= '<p>Touiteur ne nécessite pas de mot de passe. Choisissez simplement sous quel profil vous souhaitez naviguer :</p>';
        $html .= '<div class="user-picker-grid">';
        foreach ($users as $u) {
            $escapedPseudo = htmlspecialchars($u->getPseudo(), ENT_QUOTES, 'UTF-8');
            $html .= <<<HTML
            <form method="POST" action="index.php?action=changer-utilisateur" class="user-select-form">
                <input type="hidden" name="pseudo" value="{$escapedPseudo}">
                <input type="hidden" name="redirect" value="mon-mur">
                <button type="submit" class="btn btn-outline btn-block">
                    <strong>@{$escapedPseudo}</strong><br>
                    <small>{$u->getNomComplet()}</small>
                </button>
            </form>
HTML;
        }
        $html .= '</div>';
        $html .= '</div>';
        return $html;
    }
}
