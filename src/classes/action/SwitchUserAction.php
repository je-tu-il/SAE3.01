<?php

namespace iutnc\touiteur\action;

use iutnc\touiteur\repository\TouiteurRepository;

/**
 * Action : Changer de profil actif sans authentification requise
 */
class SwitchUserAction extends Action {

    public function execute(): string {
        $pseudo = trim($_POST['pseudo'] ?? $_GET['pseudo'] ?? '');
        $redirect = trim($_POST['redirect'] ?? $_GET['redirect'] ?? 'liste-touites');

        if (!empty($pseudo)) {
            $repo = new TouiteurRepository();
            $user = $repo->getUserByPseudo($pseudo);
            if ($user) {
                $this->setCurrentUser($user->getPseudo());
                $this->addFlash('success', "Vous naviguez maintenant en tant que @{$user->getPseudo()}.");
            } else {
                $this->addFlash('error', "Profil introuvable.");
            }
        }

        if (str_contains($redirect, '&')) {
            $segments = explode('&', $redirect);
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
            $this->redirect($redirect);
        }
        return '';
    }
}
