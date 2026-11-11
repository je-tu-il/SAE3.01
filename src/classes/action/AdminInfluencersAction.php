<?php

namespace iutnc\touiteur\action;

use iutnc\touiteur\repository\TouiteurRepository;

/**
 * Action Back-Office : Liste des influenceurs (utilisateurs les plus suivis)
 * Cas d'usage : Rentabiliser (Fonctionnalité 14)
 */
class AdminInfluencersAction extends Action {

    public function execute(): string {
        $repo = new TouiteurRepository();

        // Par défaut 'asc' selon l'énoncé du sujet, avec toggle 'desc' possible
        $order = strtolower($_GET['order'] ?? 'asc') === 'desc' ? 'DESC' : 'ASC';
        $influencers = $repo->getMostFollowedUsers(50, $order);

        $otherOrder = ($order === 'ASC') ? 'desc' : 'asc';
        $otherLabel = ($order === 'ASC') ? 'Décroissant &darr;' : 'Croissant &uarr;';
        $currentLabel = ($order === 'ASC') ? 'Ordre croissant (selon sujet)' : 'Ordre décroissant';

        $html = '<div class="admin-section-header">';
        $html .= '<div>';
        $html .= '<h2>Statistiques : Utilisateurs les plus influents</h2>';
        $html .= '<p class="lead">Classement des créateurs selon leur nombre d\'abonnés (données monétisables).</p>';
        $html .= '</div>';
        $html .= '<div class="sort-controls">';
        $html .= "<span>Tri actif : <strong>{$currentLabel}</strong> &mdash; </span>";
        $html .= "<a href=\"admin.php?action=influenceurs&order={$otherOrder}\" class=\"btn btn-secondary btn-sm\">Trier en {$otherLabel}</a>";
        $html .= '</div>';
        $html .= '</div>';

        if (empty($influencers)) {
            $html .= '<div class="empty-state"><p>Aucun utilisateur répertorié.</p></div>';
            return $html;
        }

        $html .= '<div class="table-responsive">';
        $html .= '<table class="admin-table">';
        $html .= '<thead>';
        $html .= '<tr>';
        $html .= '<th>Rang</th>';
        $html .= '<th>Utilisateur</th>';
        $html .= '<th>Nom & Prénom</th>';
        $html .= '<th>Email</th>';
        $html .= '<th>Touites publiés</th>';
        $html .= '<th>Nombre de suiveurs</th>';
        $html .= '<th>Profil</th>';
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';

        $rank = 1;
        foreach ($influencers as $row) {
            $user = $row['user'];
            $followers = $row['nb_followers'];
            $pseudo = htmlspecialchars($user->getPseudo(), ENT_QUOTES, 'UTF-8');
            $nom = htmlspecialchars($user->getNomComplet(), ENT_QUOTES, 'UTF-8');
            $email = htmlspecialchars($user->getEmail(), ENT_QUOTES, 'UTF-8');
            $nbTouites = $user->getNbTouites();
            $pseudoUrl = urlencode($user->getPseudo());

            $html .= '<tr>';
            $html .= "<td><span class=\"badge-rank\">#{$rank}</span></td>";
            $html .= "<td><strong>@{$pseudo}</strong></td>";
            $html .= "<td>{$nom}</td>";
            $html .= "<td><code>{$email}</code></td>";
            $html .= "<td>{$nbTouites}</td>";
            $html .= "<td><strong>{$followers}</strong> suiveur(s)</td>";
            $html .= "<td><a href=\"index.php?action=touites-utilisateur&author={$pseudoUrl}\" target=\"_blank\" class=\"btn btn-sm btn-outline\">Voir touites &rarr;</a></td>";
            $html .= '</tr>';
            $rank++;
        }

        $html .= '</tbody>';
        $html .= '</table>';
        $html .= '</div>';

        return $html;
    }
}
