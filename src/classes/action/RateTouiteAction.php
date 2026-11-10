<?php

namespace iutnc\touiteur\action;

use iutnc\touiteur\repository\TouiteurRepository;
use iutnc\touiteur\exceptions\ValidationException;
use iutnc\touiteur\exceptions\DatabaseException;

/**
 * Action : Évaluer un touite (+1 like / -1 dislike)
 * Cas d'usage : Participer (Fonctionnalité 7)
 */
class RateTouiteAction extends Action {

    public function execute(): string {
        if ($this->http_method !== 'POST') {
            $this->redirect('liste-touites');
        }

        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $valeur = isset($_POST['valeur']) ? (int)$_POST['valeur'] : 0;

        $currentUser = $this->getCurrentUser();
        if ($currentUser === null) {
            $this->addFlash('error', "Vous devez être identifié pour évaluer un touite.");
            $this->redirect('detail-touite', ['id' => $id]);
        }

        if (!in_array($valeur, [-1, 1], true)) {
            $this->addFlash('error', "Valeur d'évaluation invalide.");
            $this->redirect('detail-touite', ['id' => $id]);
        }

        $repo = new TouiteurRepository();
        try {
            $result = $repo->rateTouite($id, $currentUser, $valeur);
            $sign = ($valeur > 0) ? '+1 (J\'aime)' : '-1 (Je n\'aime pas)';
            if (!empty($result['modified'])) {
                $this->addFlash('success', "Votre évaluation a été modifiée en {$sign} !");
            } else {
                $this->addFlash('success', "Votre vote {$sign} a été enregistré avec succès !");
            }
        } catch (ValidationException | DatabaseException $e) {
            $msg = $e->getMessage();
            if (str_contains($msg, 'déjà voté') || str_contains($msg, 'déjà évalué')) {
                $this->addFlash('warning', $msg);
            } else {
                $this->addFlash('error', $msg);
            }
        }

        $this->redirect('detail-touite', ['id' => $id]);
        return '';
    }
}
