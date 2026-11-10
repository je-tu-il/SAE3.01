<?php

namespace iutnc\touiteur\action;

use iutnc\touiteur\repository\TouiteurRepository;
use iutnc\touiteur\render\TouiteRenderer;
use iutnc\touiteur\render\Renderer;

/**
 * Action : Afficher un touite en détail
 * Cas d'usage : Naviguer (Fonctionnalité 2)
 */
class DisplayTouiteDetailAction extends Action {

    public function execute(): string {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            $this->addFlash('error', "Identifiant de touite invalide.");
            $this->redirect('liste-touites');
        }

        $repo = new TouiteurRepository();
        $touite = $repo->getTouiteById($id);

        if (!$touite) {
            $this->addFlash('error', "Le touite demandé n'existe pas ou a été supprimé.");
            $this->redirect('liste-touites');
        }

        $currentUser = $this->getCurrentUser();
        $userRating = null;
        $isFollowingAuthor = null;

        if ($currentUser !== null) {
            $userRating = $repo->hasUserRated($id, $currentUser);
            $isFollowingAuthor = $repo->isFollowingUser($currentUser, $touite->getAuteurPseudo());
        }

        $renderer = new TouiteRenderer($touite, $currentUser, $userRating, $isFollowingAuthor);

        $html = '<div class="page-navigation-top">';
        $html .= '<a href="index.php?action=liste-touites" class="btn btn-secondary btn-sm">&larr; Retour à la liste des touites</a>';
        $html .= '</div>';

        $html .= $renderer->render(Renderer::DETAIL);

        return $html;
    }
}
