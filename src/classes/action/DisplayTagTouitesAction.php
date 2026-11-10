<?php

namespace iutnc\touiteur\action;

use iutnc\touiteur\repository\TouiteurRepository;
use iutnc\touiteur\render\TouiteRenderer;
use iutnc\touiteur\render\Renderer;

/**
 * Action : Afficher les touites associés à un tag donné
 * Cas d'usage : Naviguer (Fonctionnalité 5) et Socialiser (Fonctionnalité 11)
 */
class DisplayTagTouitesAction extends Action {

    public function execute(): string {
        $tag = trim($_GET['tag'] ?? '');
        $tag = ltrim($tag, '#'); // Nettoyer si le # a été transmis

        if (empty($tag)) {
            $this->addFlash('error', "Tag non spécifié.");
            $this->redirect('liste-touites');
        }

        $repo = new TouiteurRepository();
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = 5;

        $total = $repo->countTouitesByTag($tag);
        $totalPages = max(1, (int)ceil($total / $limit));
        if ($page > $totalPages && $totalPages > 0) {
            $page = $totalPages;
        }

        $touites = $repo->getTouitesByTag($tag, $page, $limit);
        $currentUser = $this->getCurrentUser();

        $isFollowingTag = false;
        if ($currentUser !== null) {
            $isFollowingTag = $repo->isFollowingTag($currentUser, $tag);
        }

        $escapedTag = htmlspecialchars($tag, ENT_QUOTES, 'UTF-8');

        $html = '<div class="page-navigation-top">';
        $html .= '<a href="index.php?action=liste-touites" class="btn btn-secondary btn-sm">&larr; Retour au fil principal</a>';
        $html .= '</div>';

        $html .= '<div class="profile-header-card tag-header-card">';
        $html .= '<div class="profile-avatar tag-avatar">#</div>';
        $html .= '<div class="profile-info">';
        $html .= "<h2>#{$escapedTag}</h2>";
        $html .= "<p class=\"text-muted\">{$total} publication(s) mentionnant ce mot-clé</p>";
        $html .= '</div>';

        if ($currentUser !== null) {
            $html .= '<div class="profile-actions">';
            if ($isFollowingTag) {
                $html .= <<<HTML
                <form method="POST" action="index.php?action=suivre-tag" class="inline-form">
                    <input type="hidden" name="tag" value="{$escapedTag}">
                    <input type="hidden" name="op" value="unfollow">
                    <input type="hidden" name="redirect" value="touites-tag&tag={$escapedTag}">
                    <button type="submit" class="btn btn-outline">Se désabonner de #{$escapedTag}</button>
                </form>
HTML;
            } else {
                $html .= <<<HTML
                <form method="POST" action="index.php?action=suivre-tag" class="inline-form">
                    <input type="hidden" name="tag" value="{$escapedTag}">
                    <input type="hidden" name="op" value="follow">
                    <input type="hidden" name="redirect" value="touites-tag&tag={$escapedTag}">
                    <button type="submit" class="btn btn-primary">+ S'abonner à #{$escapedTag}</button>
                </form>
HTML;
            }
            $html .= '</div>';
        }
        $html .= '</div>';

        if (empty($touites)) {
            $html .= '<div class="empty-state">';
            
            $html .= '<h3>Aucun touite pour le moment</h3>';
            $html .= "<p>Aucun touite ne mentionne encore le tag #{$escapedTag}. Soyez le premier à publier avec ce tag !</p>";
            $html .= '<a href="index.php?action=publier-touite" class="btn btn-primary" style="margin-top:12px; display:inline-block;">+ Publier un touite avec ce tag</a>';
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
                $html .= "<li class=\"page-item\"><a href=\"index.php?action=touites-tag&tag={$escapedTag}&page={$prev}\" class=\"page-link\">&laquo; Précédent</a></li>";
            }
            $html .= "<li class=\"page-item active\"><span class=\"page-link\">Page {$page} sur {$totalPages}</span></li>";
            if ($page < $totalPages) {
                $next = $page + 1;
                $html .= "<li class=\"page-item\"><a href=\"index.php?action=touites-tag&tag={$escapedTag}&page={$next}\" class=\"page-link\">Suivant &raquo;</a></li>";
            }
            $html .= '</ul></nav>';
        }

        return $html;
    }
}
