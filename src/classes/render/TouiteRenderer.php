<?php

namespace iutnc\touiteur\render;

use iutnc\touiteur\model\Touite;
use iutnc\touiteur\repository\TouiteurRepository;

class TouiteRenderer implements Renderer {

    private Touite $touite;
    private ?string $currentUser;
    private ?int $userRating;
    private ?bool $isFollowingAuthor;

    public function __construct(
        Touite $touite, 
        ?string $currentUser = null, 
        ?int $userRating = null,
        ?bool $isFollowingAuthor = null
    ) {
        $this->touite = $touite;
        $this->currentUser = $currentUser;
        $this->userRating = $userRating;
        $this->isFollowingAuthor = $isFollowingAuthor;
    }

    /**
     * Transforme les #tags en liens cliquables sécurisés
     */
    public static function formatTextWithTags(string $text): string {
        $escaped = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        return preg_replace_callback('/(?<!&)#([\p{L}\p{N}_]+)/u', function($matches) {
            $tag = $matches[1];
            $urlTag = urlencode($tag);
            return "<a href=\"index.php?action=touites-tag&tag={$urlTag}\" class=\"touite-tag-link\">#{$tag}</a>";
        }, $escaped);
    }

    public function render(int $selector = self::COMPACT): string {
        return ($selector === self::DETAIL) ? $this->renderDetail() : $this->renderCompact();
    }

    /**
     * Rendu compact pour les listes (fonctionnalité 1)
     */
    private function renderCompact(): string {
        $id = $this->touite->getId();
        $author = htmlspecialchars($this->touite->getAuteurPseudo(), ENT_QUOTES, 'UTF-8');
        $authorUrl = urlencode($this->touite->getAuteurPseudo());
        $initial = htmlspecialchars(mb_strtoupper(mb_substr($this->touite->getAuteurPseudo(), 0, 1, 'UTF-8'), 'UTF-8'), ENT_QUOTES, 'UTF-8');
        $date = htmlspecialchars(date('d/m/Y à H:i', strtotime($this->touite->getDatePublication())), ENT_QUOTES, 'UTF-8');
        $note = $this->touite->getNote();
        $formattedText = self::formatTextWithTags($this->touite->getTexte());
        $hasImageBadge = $this->touite->getImage() ? '<span class="badge badge-image">Avec image</span>' : '';

        $scoreClass = ($note > 0) ? 'score-positive' : (($note < 0) ? 'score-negative' : 'score-neutral');

        $initial = htmlspecialchars(mb_strtoupper(mb_substr($this->touite->getAuteurPseudo(), 0, 1, 'UTF-8'), 'UTF-8'), ENT_QUOTES, 'UTF-8');

        return <<<HTML
        <article class="touite-card touite-compact" id="touite-{$id}">
            <header class="touite-header">
                <div class="touite-author-meta">
                    <div class="avatar-circle">{$initial}</div>
                    <div class="touite-meta">
                        <a href="index.php?action=touites-utilisateur&author={$authorUrl}" class="touite-author">@{$author}</a>
                        <time class="touite-date" datetime="{$this->touite->getDatePublication()}">{$date}</time>
                    </div>
                </div>
                {$hasImageBadge}
            </header>
            
            <div class="touite-body">
                <p class="touite-text">{$formattedText}</p>
            </div>

            <footer class="touite-footer">
                <div class="touite-score {$scoreClass}">
                    <span class="score-label">Score :</span>
                    <strong class="score-value">{$note}</strong>
                </div>
                <div class="touite-actions">
                    <a href="index.php?action=detail-touite&id={$id}" class="btn btn-secondary btn-sm">Consulter le détail &rarr;</a>
                </div>
            </footer>
        </article>
HTML;
    }

    /**
     * Rendu détaillé avec toutes les informations, image et interactions (fonctionnalités 2, 7, 10, 13)
     */
    private function renderDetail(): string {
        $id = $this->touite->getId();
        $initial = htmlspecialchars(mb_strtoupper(mb_substr($this->touite->getAuteurPseudo(), 0, 1, 'UTF-8'), 'UTF-8'), ENT_QUOTES, 'UTF-8');
        $author = htmlspecialchars($this->touite->getAuteurPseudo(), ENT_QUOTES, 'UTF-8');
        $authorUrl = urlencode($this->touite->getAuteurPseudo());
        $date = htmlspecialchars(date('d/m/Y à H:i', strtotime($this->touite->getDatePublication())), ENT_QUOTES, 'UTF-8');
        $note = $this->touite->getNote();
        $formattedText = self::formatTextWithTags($this->touite->getTexte());

        $scoreClass = ($note > 0) ? 'score-positive' : (($note < 0) ? 'score-negative' : 'score-neutral');

        // Affichage de l'image si présente (exigence : uniquement en vue détail, pas en liste)
        $imageHtml = '';
        if ($image = $this->touite->getImage()) {
            $chemin = htmlspecialchars($image->getChemin(), ENT_QUOTES, 'UTF-8');
            $desc = htmlspecialchars($image->getDescription() ?? "Image associée au touite #{$id}", ENT_QUOTES, 'UTF-8');
            $imageHtml = <<<HTML
            <div class="touite-media">
                <img src="uploads/{$chemin}" alt="{$desc}" class="touite-image" loading="lazy">
                <p class="image-caption">{$desc}</p>
            </div>
HTML;
        }

        // Bouton de suivi de l'auteur (si connecté et pas son propre touite)
        $followButtonHtml = '';
        if ($this->currentUser !== null && strcasecmp($this->currentUser, $this->touite->getAuteurPseudo()) !== 0) {
            if ($this->isFollowingAuthor) {
                $followButtonHtml = <<<HTML
                <form method="POST" action="index.php?action=suivre-utilisateur" class="inline-form">
                    <input type="hidden" name="target" value="{$author}">
                    <input type="hidden" name="op" value="unfollow">
                    <input type="hidden" name="redirect" value="detail-touite&id={$id}">
                    <button type="submit" class="btn btn-danger btn-sm btn-unfollow">Ne plus suivre @{$author}</button>
                </form>
HTML;
            } else {
                $followButtonHtml = <<<HTML
                <form method="POST" action="index.php?action=suivre-utilisateur" class="inline-form">
                    <input type="hidden" name="target" value="{$author}">
                    <input type="hidden" name="op" value="follow">
                    <input type="hidden" name="redirect" value="detail-touite&id={$id}">
                    <button type="submit" class="btn btn-primary btn-sm">+ Suivre @{$author}</button>
                </form>
HTML;
            }
        }

        // Section d'évaluation (Like / Dislike) - Boutons toujours visibles pour permettre de changer ou tester
        $ratingSectionHtml = '';
        if ($this->currentUser !== null) {
            $likeActiveClass = ($this->userRating === 1) ? 'active-voted' : '';
            $likeLabel = ($this->userRating === 1) ? 'Aimé (+1)' : '+1 J\'aime';
            $dislikeActiveClass = ($this->userRating === -1) ? 'active-voted' : '';
            $dislikeLabel = ($this->userRating === -1) ? 'Désapprouvé (-1)' : '-1 Je n\'aime pas';

            $ratingSectionHtml = <<<HTML
            <div class="rate-buttons">
                <form method="POST" action="index.php?action=evaluer-touite" class="inline-form">
                    <input type="hidden" name="id" value="{$id}">
                    <input type="hidden" name="valeur" value="1">
                    <button type="submit" class="btn btn-like btn-sm {$likeActiveClass}" title="J'aime ce touite">{$likeLabel}</button>
                </form>
                <form method="POST" action="index.php?action=evaluer-touite" class="inline-form">
                    <input type="hidden" name="id" value="{$id}">
                    <input type="hidden" name="valeur" value="-1">
                    <button type="submit" class="btn btn-dislike btn-sm {$dislikeActiveClass}" title="Je n'aime pas ce touite">{$dislikeLabel}</button>
                </form>
            </div>
HTML;
        } else {
            $ratingSectionHtml = "<em class=\"text-muted\">Sélectionnez un profil pour évaluer ce touite.</em>";
        }

        // Liste des tags
        $tagsHtml = '';
        if (!empty($this->touite->getTags())) {
            $tagsList = array_map(function($t) {
                $escapedTag = htmlspecialchars($t, ENT_QUOTES, 'UTF-8');
                $urlTag = urlencode($t);
                return "<a href=\"index.php?action=touites-tag&tag={$urlTag}\" class=\"tag-pill\">#{$escapedTag}</a>";
            }, $this->touite->getTags());
            $tagsHtml = '<div class="touite-tags-list">' . implode(' ', $tagsList) . '</div>';
        }

        return <<<HTML
        <article class="touite-card touite-detail" id="touite-{$id}">
            <header class="touite-header">
                <div class="touite-author-card">
                    <div class="avatar-circle">{$initial}</div>
                    <div>
                        <a href="index.php?action=touites-utilisateur&author={$authorUrl}" class="touite-author-name">@{$author}</a>
                        <div class="touite-date">Publié le {$date}</div>
                    </div>
                </div>
                <div class="touite-header-actions">
                    {$followButtonHtml}
                </div>
            </header>

            <div class="touite-body">
                <p class="touite-text-large">{$formattedText}</p>
                {$imageHtml}
                {$tagsHtml}
            </div>

            <footer class="touite-footer detail-footer">
                <div class="score-container">
                    <span class="score-label">Score d'appréciation :</span>
                    <span class="score-pill {$scoreClass}">{$note}</span>
                </div>
                <div class="rating-controls">
                    {$ratingSectionHtml}
                </div>
            </footer>
        </article>
HTML;
    }
}
