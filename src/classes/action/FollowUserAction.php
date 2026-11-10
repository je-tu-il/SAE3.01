<?php

namespace iutnc\touiteur\action;

use iutnc\touiteur\repository\TouiteurRepository;
use iutnc\touiteur\exceptions\ValidationException;
use iutnc\touiteur\exceptions\DatabaseException;

/**
 * Action : Suivre ou ne plus suivre un utilisateur
 * Cas d'usage : Socialiser (Fonctionnalité 10)
 */
class FollowUserAction extends Action {

    public function execute(): string {
        if ($this->http_method !== 'POST') {
            $this->redirect('liste-touites');
        }

        $target = trim($_POST['target'] ?? '');
        $op = trim($_POST['op'] ?? 'follow');
        $redirectAction = trim($_POST['redirect'] ?? 'liste-touites');

        $currentUser = $this->getCurrentUser();
        if ($currentUser === null) {
            $this->addFlash('error', "Vous devez être identifié pour suivre un utilisateur.");
            $this->redirect('liste-touites');
        }

        $repo = new TouiteurRepository();
        try {
            if ($op === 'unfollow') {
                $repo->unfollowUser($currentUser, $target);
                $this->addFlash('success', "Vous ne suivez plus @{$target}.");
            } else {
                $repo->followUser($currentUser, $target);
                $this->addFlash('success', "Vous suivez désormais @{$target} !");
            }
        } catch (ValidationException | DatabaseException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        // Décodage propre de la redirection (évite bug parse_str sur premier argument sans valeur)
        if (str_contains($redirectAction, '&')) {
            $segments = explode('&', $redirectAction);
            $action = array_shift($segments) ?: 'liste-touites';
            $params = [];
            foreach ($segments as $seg) {
                if (str_contains($seg, '=')) {
                    [$k, $v] = explode('=', $seg, 2);
                    $params[$k] = $v;
                }
            }
            $this->redirect($action, $params);
        } else {
            $this->redirect($redirectAction);
        }
        return '';
    }
}
