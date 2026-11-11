<?php

namespace iutnc\touiteur\action;

use iutnc\touiteur\repository\TouiteurRepository;

/**
 * Action Back-Office : Tendances des tags (mots-clés les plus mentionnés)
 * Cas d'usage : Rentabiliser (Fonctionnalité 15)
 */
class AdminTrendsAction extends Action {

    public function execute(): string {
        $repo = new TouiteurRepository();

        // Par défaut 'asc' selon l'énoncé du sujet, avec toggle 'desc'
        $order = strtolower($_GET['order'] ?? 'asc') === 'desc' ? 'DESC' : 'ASC';
        $trends = $repo->getMostMentionedTags(50, $order);

        $otherOrder = ($order === 'ASC') ? 'desc' : 'asc';
        $otherLabel = ($order === 'ASC') ? 'Décroissant &darr;' : 'Croissant &uarr;';
        $currentLabel = ($order === 'ASC') ? 'Ordre croissant (selon sujet)' : 'Ordre décroissant';

        $html = '<div class="admin-section-header">';
        $html .= '<div>';
        $html .= '<h2>Statistiques : Tendances et Mots-clés</h2>';
        $html .= '<p class="lead">Analyse de la popularité des #tags et thématiques discutées sur la plateforme.</p>';
        $html .= '</div>';
        $html .= '<div class="sort-controls">';
        $html .= "<span>Tri actif : <strong>{$currentLabel}</strong> &mdash; </span>";
        $html .= "<a href=\"admin.php?action=tendances&order={$otherOrder}\" class=\"btn btn-secondary btn-sm\">Trier en {$otherLabel}</a>";
        $html .= '</div>';
        $html .= '</div>';

        if (empty($trends)) {
            $html .= '<div class="empty-state"><p>Aucun tag répertorié pour le moment.</p></div>';
            return $html;
        }

        $html .= '<div class="table-responsive">';
        $html .= '<table class="admin-table">';
        $html .= '<thead>';
        $html .= '<tr>';
        $html .= '<th>Rang</th>';
        $html .= '<th>Hashtag</th>';
        $html .= '<th>Description</th>';
        $html .= '<th>Nombre de mentions</th>';
        $html .= '<th>Explorer</th>';
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';

        $rank = 1;
        foreach ($trends as $row) {
            $tag = $row['tag'];
            $mentions = $row['nb_mentions'];
            $libelle = htmlspecialchars($tag->getLibelle(), ENT_QUOTES, 'UTF-8');
            $desc = htmlspecialchars($tag->getDescription() ?? 'Aucune description', ENT_QUOTES, 'UTF-8');
            $tagUrl = urlencode($tag->getLibelle());

            $html .= '<tr>';
            $html .= "<td><span class=\"badge-rank\">#{$rank}</span></td>";
            $html .= "<td><span class=\"tag-pill\">#{$libelle}</span></td>";
            $html .= "<td>{$desc}</td>";
            $html .= "<td><strong>{$mentions}</strong> mention(s)</td>";
            $html .= "<td><a href=\"index.php?action=touites-tag&tag={$tagUrl}\" target=\"_blank\" class=\"btn btn-sm btn-outline\">Voir touites &rarr;</a></td>";
            $html .= '</tr>';
            $rank++;
        }

        $html .= '</tbody>';
        $html .= '</table>';
        $html .= '</div>';

        return $html;
    }
}
