<?php

namespace iutnc\touiteur\action;

use iutnc\touiteur\repository\TouiteurRepository;
use iutnc\touiteur\render\TouiteRenderer;
use iutnc\touiteur\render\Renderer;

/**
 * Action : Afficher la liste des touites récents avec pagination
 * Cas d'usage : Naviguer (Fonctionnalités 1 et 3)
 */
class DisplayTouitesAction extends Action {

    public function execute(): string {
        $repo = new TouiteurRepository();

        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = 5;

        $totalTouites = $repo->countTouites();
        $totalPages = max(1, (int)ceil($totalTouites / $limit));
        if ($page > $totalPages && $totalPages > 0) {
            $page = $totalPages;
        }

        $touites = $repo->getTouites($page, $limit);
        $currentUser = $this->getCurrentUser();

        $html = '<div class="page-header">';
        $html .= '<h2>Fil d\'actualité des touites</h2>';
        $html .= '<p class="lead">Découvrez les dernières publications de la communauté en temps réel.</p>';
        $html .= '</div>';

        if (empty($touites)) {
            $html .= '<div class="empty-state">';
            $html .= '<p>Aucun touite n\'a encore été publié sur la plateforme.</p>';
            $html .= '<a href="index.php?action=publier-touite" class="btn btn-primary">Publier le premier touite !</a>';
            $html .= '</div>';
            return $html;
        }

        $html .= '<div class="touites-feed">';
        foreach ($touites as $touite) {
            $renderer = new TouiteRenderer($touite, $currentUser);
            $html .= $renderer->render(Renderer::COMPACT);
        }
        $html .= '</div>';

        // Pagination (Fonctionnalité 3)
        $html .= $this->renderPagination($page, $totalPages);

        return $html;
    }

    private function renderPagination(int $currentPage, int $totalPages): string {
        if ($totalPages <= 1) {
            return '';
        }

        $html = '<nav class="pagination-container" aria-label="Pagination des touites">';
        $html .= '<ul class="pagination">';

        // Bouton Précédent
        if ($currentPage > 1) {
            $prevPage = $currentPage - 1;
            $html .= "<li class=\"page-item\"><a href=\"index.php?action=liste-touites&page={$prevPage}\" class=\"page-link\">&laquo; Précédent</a></li>";
        } else {
            $html .= '<li class="page-item disabled"><span class="page-link">&laquo; Précédent</span></li>';
        }

        // Indicateur de page
        $html .= "<li class=\"page-item active\"><span class=\"page-link\">Page {$currentPage} sur {$totalPages}</span></li>";

        // Bouton Suivant
        if ($currentPage < $totalPages) {
            $nextPage = $currentPage + 1;
            $html .= "<li class=\"page-item\"><a href=\"index.php?action=liste-touites&page={$nextPage}\" class=\"page-link\">Suivant &raquo;</a></li>";
        } else {
            $html .= '<li class="page-item disabled"><span class="page-link">Suivant &raquo;</span></li>';
        }

        $html .= '</ul>';
        $html .= '</nav>';

        return $html;
    }
}
