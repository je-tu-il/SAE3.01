<?php

namespace iutnc\touiteur\action;

use iutnc\touiteur\repository\TouiteurRepository;
use iutnc\touiteur\render\TouiteRenderer;
use iutnc\touiteur\render\Renderer;

/**
 * Action : Afficher les touites d'une personne donnée
 * Cas d'usage : Naviguer (Fonctionnalité 4)
 */
class DisplayUserTouitesAction extends Action {

    public function execute(): string {
        $author = trim($_GET['author'] ?? '');
        if (empty($author)) {
            $this->addFlash('error', "Auteur non spécifié.");
            $this->redirect('liste-touites');
        }

        $repo = new TouiteurRepository();
        $targetUser = $repo->getUserByPseudo($author);
        if (!$targetUser) {
            $this->addFlash('error', "L'utilisateur @{$author} n'existe pas.");
            $this->redirect('liste-touites');
        }

        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = 5;

        $total = $repo->countTouitesByUser($author);
        $totalPages = max(1, (int)ceil($total / $limit));
        if ($page > $totalPages && $totalPages > 0) {
            $page = $totalPages;
        }

        $touites = $repo->getTouitesByUser($author, $page, $limit);
        $currentUser = $this->getCurrentUser();

        // Vérifier si l'utilisateur connecté suit cet auteur
        $isFollowing = false;
        if ($currentUser !== null && strcasecmp($currentUser, $author) !== 0) {
            $isFollowing = $repo->isFollowingUser($currentUser, $author);
        }

        $escapedAuthor = htmlspecialchars($author, ENT_QUOTES, 'UTF-8');
        $escapedNom = htmlspecialchars($targetUser->getNomComplet(), ENT_QUOTES, 'UTF-8');

        $html = '<div class="page-navigation-top">';
        $html .= '<a href="index.php?action=liste-touites" class="btn btn-secondary btn-sm">&larr; Retour au fil principal</a>';
        $html .= '</div>';

        $html .= '<div class="profile-header-card">';
        $initial = htmlspecialchars(mb_strtoupper(mb_substr($author, 0, 1, 'UTF-8'), 'UTF-8'), ENT_QUOTES, 'UTF-8');
        $html .= "<div class=\"profile-avatar\">{$initial}</div>";
        $html .= '<div class="profile-info">';
        $html .= "<h2>@{$escapedAuthor}</h2>";
        $html .= "<p class=\"text-muted\">{$escapedNom} &bull; {$total} publication(s)</p>";
        $html .= '</div>';

        if ($currentUser !== null && strcasecmp($currentUser, $author) !== 0) {
            $html .= '<div class="profile-actions">';
            if ($isFollowing) {
                $html .= <<<HTML
                <form method="POST" action="index.php?action=suivre-utilisateur" class="inline-form">
                    <input type="hidden" name="target" value="{$escapedAuthor}">
                    <input type="hidden" name="op" value="unfollow">
                    <input type="hidden" name="redirect" value="touites-utilisateur&author={$escapedAuthor}">
                    <button type="submit" class="btn btn-danger btn-unfollow">Ne plus suivre @{$escapedAuthor}</button>
                </form>
HTML;
            } else {
                $html .= <<<HTML
                <form method="POST" action="index.php?action=suivre-utilisateur" class="inline-form">
                    <input type="hidden" name="target" value="{$escapedAuthor}">
                    <input type="hidden" name="op" value="follow">
                    <input type="hidden" name="redirect" value="touites-utilisateur&author={$escapedAuthor}">
                    <button type="submit" class="btn btn-primary">+ Suivre @{$escapedAuthor}</button>
                </form>
HTML;
            }
            $html .= '</div>';
        }
        $html .= '</div>';

        if (empty($touites)) {
            $html .= '<div class="empty-state">';
            $html .= "<p>@{$escapedAuthor} n'a encore publié aucun touite.</p>";
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
                $html .= "<li class=\"page-item\"><a href=\"index.php?action=touites-utilisateur&author={$escapedAuthor}&page={$prev}\" class=\"page-link\">&laquo; Précédent</a></li>";
            }
            $html .= "<li class=\"page-item active\"><span class=\"page-link\">Page {$page} sur {$totalPages}</span></li>";
            if ($page < $totalPages) {
                $next = $page + 1;
                $html .= "<li class=\"page-item\"><a href=\"index.php?action=touites-utilisateur&author={$escapedAuthor}&page={$next}\" class=\"page-link\">Suivant &raquo;</a></li>";
            }
            $html .= '</ul></nav>';
        }

        return $html;
    }
}
