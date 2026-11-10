<?php

namespace iutnc\touiteur\action;

use iutnc\touiteur\repository\TouiteurRepository;
use iutnc\touiteur\render\UserRenderer;

/**
 * Action : Espace Narcissique (Followers et score moyen)
 * Cas d'usage : Socialiser (Fonctionnalité 12)
 */
class NarcissisticUserAction extends Action {

    public function execute(): string {
        $currentUser = $this->getCurrentUser();
        $repo = new TouiteurRepository();

        if ($currentUser === null) {
            $this->addFlash('error', "Veuillez sélectionner un profil utilisateur pour accéder à votre espace narcissique.");
            $this->redirect('mon-mur');
        }

        $user = $repo->getUserByPseudo($currentUser);
        if (!$user) {
            $this->addFlash('error', "Profil introuvable.");
            $this->redirect('liste-touites');
        }

        $followers = $repo->getUserFollowers($currentUser);
        $avgScore = $repo->getUserAverageScore($currentUser);
        $totalTouites = $user->getNbTouites();

        $escapedPseudo = htmlspecialchars($currentUser, ENT_QUOTES, 'UTF-8');
        $escapedNom = htmlspecialchars($user->getNomComplet(), ENT_QUOTES, 'UTF-8');

        $html = '<div class="page-header">';
        $html .= "<h2>Espace Narcissique de @{$escapedPseudo}</h2>";
        $html .= '<p class="lead">Consultez votre impact et votre notoriété sur Touiteur.app.</p>';
        $html .= '</div>';

        // Cartes de statistiques
        $html .= '<div class="stats-grid">';
        
        $html .= '<div class="stat-card">';
        $html .= '<div class="stat-number">' . count($followers) . '</div>';
        $html .= '<div class="stat-label">Nombre d\'abonnés</div>';
        $html .= '</div>';

        $html .= '<div class="stat-card stat-card-highlight">';
        $scorePrefix = ($avgScore > 0) ? '+' : '';
        $html .= "<div class=\"stat-number\">{$avgScore} / 5</div>";
        $html .= '<div class="stat-label">Note moyenne des publications</div>';
        $html .= '</div>';

        $html .= '<div class="stat-card">';
        $html .= "<div class=\"stat-number\">{$totalTouites}</div>";
        $html .= '<div class="stat-label">Touites publiés</div>';
        $html .= '</div>';

        $html .= '</div>';

        // Liste des suiveurs (followers)
        $html .= '<div class="followers-section">';
        $html .= '<h3>Qui vous suit ?</h3>';

        if (empty($followers)) {
            $html .= '<div class="empty-state">';
            $html .= '<p>Aucun utilisateur ne vous suit pour le moment. Publiez des touites intéressants avec des #tags pour gagner en visibilité !</p>';
            $html .= '</div>';
        } else {
            $html .= '<div class="users-grid">';
            foreach ($followers as $follower) {
                $userRenderer = new UserRenderer($follower);
                $html .= $userRenderer->render();
            }
            $html .= '</div>';
        }
        $html .= '</div>';

        return $html;
    }
}
