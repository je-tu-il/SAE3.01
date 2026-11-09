<?php

namespace iutnc\touiteur\render;

use iutnc\touiteur\model\User;

class UserRenderer implements Renderer {
    
    private User $user;
    private ?bool $isFollowing;
    private ?float $averageScore;

    public function __construct(User $user, ?bool $isFollowing = null, ?float $averageScore = null) {
        $this->user = $user;
        $this->isFollowing = $isFollowing;
        $this->averageScore = $averageScore;
    }

    public function render(int $selector = self::COMPACT): string {
        $pseudo = htmlspecialchars($this->user->getPseudo(), ENT_QUOTES, 'UTF-8');
        $nom = htmlspecialchars($this->user->getNomComplet(), ENT_QUOTES, 'UTF-8');
        $nbTouites = $this->user->getNbTouites();
        $pseudoUrl = urlencode($this->user->getPseudo());
        $initial = htmlspecialchars(mb_strtoupper(mb_substr($this->user->getPseudo(), 0, 1, 'UTF-8'), 'UTF-8'), ENT_QUOTES, 'UTF-8');

        $scoreHtml = '';
        if ($this->averageScore !== null) {
            $scoreHtml = "<span class=\"badge badge-info\">Score : {$this->averageScore} / 5</span>";
        }

        return <<<HTML
        <div class="user-card">
            <div class="user-card-header">
                <div class="avatar-circle">{$initial}</div>
                <div class="user-info">
                    <a href="index.php?action=touites-utilisateur&author={$pseudoUrl}" class="user-pseudo">@{$pseudo}</a>
                    <div class="user-fullname">{$nom}</div>
                </div>
            </div>
            <div class="user-stats">
                <span class="badge">{$nbTouites} touites</span>
                {$scoreHtml}
            </div>
        </div>
HTML;
    }
}
