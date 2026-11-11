<?php

namespace iutnc\touiteur\action;

/**
 * Action : Mentions légales, Politique de confidentialité et Gestion des Cookies (RGPD)
 */
class LegalNoticeAction extends Action {

    public function execute(): string {
        $html = '<div class="legal-container">';
        
        $html .= '<div class="page-header">';
        $html .= '<h2>Mentions légales & Conformité RGPD</h2>';
        $html .= '<p class="lead">Transparence, gestion des données personnelles et cadre légal de Touiteur.app.</p>';
        $html .= '</div>';

        $html .= '<div class="legal-card">';
        $html .= '<h3>1. Éditeur du site</h3>';
        $html .= '<p>Le site <strong>Touiteur.app</strong> est développé dans le cadre de la ressource SAE 3.1 (Développer une application web sécurisée) du <strong>BUT Informatique (S3)</strong> à l\'<strong>IUT Nancy-Charlemagne</strong> — Université de Lorraine.</p>';
        $html .= '<p><strong>Équipe de réalisation :</strong> Jules, Anthony Gabillet, Ilyass Roussi et Marin.</p>';
        $html .= '</div>';

        $html .= '<div class="legal-card">';
        $html .= '<h3>2. Hébergement de l\'application</h3>';
        $html .= '<p>L\'application est hébergée sur les infrastructures universitaires :</p>';
        $html .= '<p><strong>Serveur :</strong> Plateforme Webetu &bull; Université de Lorraine &bull; 34 Cours Léopold, 54000 Nancy &bull; France.</p>';
        $html .= '</div>';

        $html .= '<div class="legal-card">';
        $html .= '<h3>3. Données personnelles & Minimisation (RGPD Art. 5.1.c)</h3>';
        $html .= '<p>Conformément au Règlement Général sur la Protection des Données (RGPD), nous appliquons le principe de <strong>minimisation stricte des données</strong> :</p>';
        $html .= '<ul>';
        $html .= '<li><strong>Données collectées :</strong> Nom d\'utilisateur (pseudo), nom complet, adresse e-mail universitaire, touites, tags et images associés.</li>';
        $html .= '<li><strong>Finalité :</strong> Fonctionnement exclusif du réseau social pédagogique (publication, fil d\'actualité, abonnements, statistiques d\'influence).</li>';
        $html .= '<li><strong>Sécurité des données :</strong> 100% des requêtes passent par des requêtes préparées PDO (protection anti-injection SQL). Les contenus affichés sont neutralisés contre les attaques XSS.</li>';
        $html .= '<li><strong>Aucun partage tiers :</strong> Aucune donnée n\'est cédée, vendue ou transmise à des acteurs tiers ou régies publicitaires.</li>';
        $html .= '<li><strong>Droits utilisateurs :</strong> Vous disposez d\'un droit d\'accès, de modification et d\'effacement de vos données sur simple demande auprès de l\'équipe.</li>';
        $html .= '</ul>';
        $html .= '</div>';

        $html .= '<div class="legal-card">';
        $html .= '<h3>4. Politique des Cookies (Réglementation CNIL & ePrivacy)</h3>';
        $html .= '<p><strong>Pourquoi n\'y a-t-il pas de bandeau intrusif de cookies sur Touiteur.app ?</strong></p>';
        $html .= '<p>Conformément aux délibérations de la <strong>CNIL</strong> et à l\'article 82 de la loi Informatique et Libertés, les traceurs <em>strictement nécessaires</em> à la fourniture d\'un service expressément demandé par l\'utilisateur sont <strong>exemptés de consentement préalable</strong>.</p>';
        $html .= '<p>Touiteur.app utilise uniquement un <strong>cookie technique de session (PHPSESSID)</strong> ayant pour rôle unique de :</p>';
        $html .= '<ul>';
        $html .= '<li>Mémoriser votre identifiant actif entre deux pages (sélecteur de profil) ;</li>';
        $html .= '<li>Afficher les messages d\'information temporaires (messages flash de confirmation de publication, vote, etc.).</li>';
        $html .= '</ul>';
        $html .= '<p>Aucun cookie publicitaire, aucun tracker de profilage, aucun cookie tiers n\'est déposé sur votre terminal.</p>';
        $html .= '</div>';

        $html .= '<div class="legal-card">';
        $html .= '<h3>5. Conditions d\'utilisation & Droit de rétractation</h3>';
        $html .= '<p>Touiteur.app est un projet d\'études académique sans vocation commerciale. Aucun paiement ni transaction monétaire n\'est effectué sur la plateforme. Le délai légal de rétractation de 14 jours (Code de la consommation art. L. 221-18) propre au commerce en ligne ne s\'applique donc pas, mais tout utilisateur peut demander l\'effacement immédiat de l\'ensemble de ses publications et de son profil.</p>';
        $html .= '</div>';

        $html .= '<div class="legal-actions">';
        $html .= '<a href="index.php?action=liste-touites" class="btn btn-secondary">&larr; Retour au fil public</a>';
        $html .= '</div>';

        $html .= '</div>';

        return $html;
    }
}
