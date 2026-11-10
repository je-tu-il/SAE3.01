<?php

namespace iutnc\touiteur\action;

use iutnc\touiteur\repository\TouiteurRepository;
use iutnc\touiteur\exceptions\ValidationException;
use iutnc\touiteur\exceptions\DatabaseException;

/**
 * Action : S'abonner ou se désabonner d'un tag
 * Cas d'usage : Socialiser (Fonctionnalité 11)
 */
class FollowTagAction extends Action {

    public function execute(): string {
        if ($this->http_method !== 'POST') {
            $this->redirect('liste-touites');
        }

        $tag = trim($_POST['tag'] ?? '');
        $tag = ltrim($tag, '#');
        $op = trim($_POST['op'] ?? 'follow');
        $redirectAction = trim($_POST['redirect'] ?? 'mon-mur');

        $currentUser = $this->getCurrentUser();
        if ($currentUser === null) {
            $this->addFlash('error', "Vous devez être identifié pour vous abonner à un tag.");
            $this->redirect('liste-touites');
        }

        if (empty($tag)) {
            $this->addFlash('error', "Le nom du tag est requis.");
            $this->redirect('mon-mur');
        }

        $repo = new TouiteurRepository();
        try {
            if ($op === 'unfollow') {
                $repo->unfollowTag($currentUser, $tag);
                $this->addFlash('success', "Désabonnement du tag #{$tag} effectué.");
            } else {
                $repo->followTag($currentUser, $tag);
                $this->addFlash('success', "Vous êtes maintenant abonné au tag #{$tag} !");
            }
        } catch (ValidationException | DatabaseException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        if (str_contains($redirectAction, '&')) {
            $segments = explode('&', $redirectAction);
            $action = array_shift($segments) ?: 'mon-mur';
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
