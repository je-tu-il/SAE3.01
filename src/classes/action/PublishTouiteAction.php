<?php

namespace iutnc\touiteur\action;

use iutnc\touiteur\repository\TouiteurRepository;
use iutnc\touiteur\exceptions\ValidationException;
use iutnc\touiteur\exceptions\TouiteurException;

/**
 * Action : Publier un nouveau touite avec gestion automatique des tags et upload d'image
 * Cas d'usage : Participer (Fonctionnalités 6, 8, 13)
 */
class PublishTouiteAction extends Action {

    public function execute(): string {
        $repo = new TouiteurRepository();
        $currentUser = $this->getCurrentUser();
        $users = $repo->getAllUsers();

        if ($this->http_method === 'POST') {
            return $this->handlePost($repo);
        }

        return $this->renderForm($users, $currentUser);
    }

    private function handlePost(TouiteurRepository $repo): string {
        $pseudo = trim($_POST['pseudo'] ?? '');
        $texte = trim($_POST['texte'] ?? '');
        $imageDesc = trim($_POST['image_desc'] ?? '');

        if (empty($pseudo)) {
            $this->addFlash('error', "Veuillez renseigner un pseudo pour publier.");
            $this->redirect('publier-touite');
        }

        if (empty($texte)) {
            $this->addFlash('error', "Le texte du touite ne peut pas être vide.");
            $this->redirect('publier-touite');
        }

        if (mb_strlen($texte, 'UTF-8') > 235) {
            $this->addFlash('error', "Le texte dépasse la limite autorisée de 235 caractères.");
            $this->redirect('publier-touite');
        }

        // Mémoriser le pseudo comme utilisateur actif en session
        $this->setCurrentUser($pseudo);

        // Traitement de l'upload d'image (Fonctionnalité 13)
        $imageFileName = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imageFileName = $this->processImageUpload($_FILES['image']);
        }

        try {
            $touiteId = $repo->publishTouite($pseudo, $texte, $imageFileName, $imageDesc ?: null);
            $this->addFlash('success', "Votre touite #{$touiteId} a été publié avec succès !");
            $this->redirect('detail-touite', ['id' => $touiteId]);
        } catch (TouiteurException $e) {
            $this->addFlash('error', $e->getMessage());
            $this->redirect('publier-touite');
        }
        return '';
    }

    /**
     * Traite et sécurise le fichier image envoyé
     */
    private function processImageUpload(array $file): ?string {
        $maxSize = 5 * 1024 * 1024; // 5 Mo max
        if ($file['size'] > $maxSize) {
            $this->addFlash('error', "L\'image est trop volumineuse (maximum 5 Mo).");
            return null;
        }

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowedExtensions, true)) {
            $this->addFlash('error', "Format d'image non supporté. Formats acceptés : JPG, PNG, GIF, WEBP.");
            return null;
        }

        // Vérification du type MIME réel
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($mime, $allowedMimes, true)) {
            $this->addFlash('error', "Le fichier téléchargé n'est pas une image valide.");
            return null;
        }

        // Génération d'un nom de fichier unique et sécurisé (évite écrasement et injection)
        $uniqueName = uniqid('touite_', true) . '.' . $ext;
        $uploadDir = dirname(__DIR__, 3) . '/public/uploads/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $destination = $uploadDir . $uniqueName;
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            return $uniqueName;
        }

        $this->addFlash('error', "Échec de l'enregistrement de l\'image sur le serveur.");
        return null;
    }

    private function renderForm(array $users, ?string $currentUser): string {
        $html = '<div class="form-container touite-compose-card">';
        $html .= '<div class="form-header">';
        $html .= '<h2>Publier un nouveau touite</h2>';
        $html .= '<p class="lead">Partagez votre message avec la communauté Touiteur (235 caractères max, incluez vos #tags).</p>';
        $html .= '</div>';

        $html .= '<form method="POST" action="index.php?action=publier-touite" enctype="multipart/form-data" class="touite-form" id="publish-form">';

        // Sélection ou saisie du pseudo
        $html .= '<div class="form-group">';
        $html .= '<label for="pseudo">Votre nom d\'utilisateur (pseudo) <span class="required">*</span> :</label>';
        $html .= '<div class="input-group-pseudo">';
        $html .= '<input type="text" id="pseudo" name="pseudo" value="' . htmlspecialchars($currentUser ?? '', ENT_QUOTES, 'UTF-8') . '" placeholder="Ex: alice_tech" required maxlength="50" class="form-control">';
        
        if (!empty($users)) {
            $html .= '<select id="quick-user-select" class="form-control select-addon">';
            $html .= '<option value="">-- Choisir parmi les profils existants --</option>';
            foreach ($users as $u) {
                $selected = ($currentUser === $u->getPseudo()) ? 'selected' : '';
                $pseudoVal = htmlspecialchars($u->getPseudo(), ENT_QUOTES, 'UTF-8');
                $html .= "<option value=\"{$pseudoVal}\" {$selected}>@{$pseudoVal} ({$u->getNomComplet()})</option>";
            }
            $html .= '</select>';
        }
        $html .= '</div>';
        $html .= '<small class="form-hint">Vous pouvez saisir votre propre pseudo ou sélectionner un utilisateur existant.</small>';
        $html .= '</div>';

        // Zone de texte du touite
        $html .= '<div class="form-group">';
        $html .= '<label for="texte">Contenu de votre touite <span class="required">*</span> :</label>';
        $html .= '<textarea id="texte" name="texte" rows="4" maxlength="235" placeholder="Quoi de neuf ? Utilisez des #tags pour classifier votre touite..." required class="form-control"></textarea>';
        $html .= '<div class="form-row-helpers">';
        $html .= '<div class="tag-suggestions">';
        $html .= '<span class="tag-suggestions-label">Suggestions de tags :</span>';
        $html .= '<button type="button" class="chip-suggestion" data-tag="#iut">#iut</button>';
        $html .= '<button type="button" class="chip-suggestion" data-tag="#sae">#sae</button>';
        $html .= '<button type="button" class="chip-suggestion" data-tag="#dev">#dev</button>';
        $html .= '<button type="button" class="chip-suggestion" data-tag="#php">#php</button>';
        $html .= '<button type="button" class="chip-suggestion" data-tag="#sécurité">#sécurité</button>';
        $html .= '</div>';
        $html .= '<div class="char-counter"><span id="char-count">0</span> / 235 caractères</div>';
        $html .= '</div>';
        $html .= '</div>';

        // Upload d'image (Fonctionnalité 13)
        $html .= '<div class="form-group file-group">';
        $html .= '<label for="image">Associer une image (optionnel) :</label>';
        $html .= '<input type="file" id="image" name="image" accept="image/png, image/jpeg, image/gif, image/webp" class="form-control-file">';
        $html .= '<small class="form-hint">Formats acceptés : JPG, PNG, GIF, WEBP (Max 5 Mo). L\'image apparaîtra sur la vue détaillée du touite.</small>';
        $html .= '<div id="image-preview-wrapper" class="image-preview-wrapper" style="display:none;">';
        $html .= '<img id="image-preview" src="#" alt="Aperçu avant publication" class="image-preview-thumb">';
        $html .= '<button type="button" id="btn-remove-preview" class="btn-remove-preview" title="Retirer l\'image">&times; Supprimer l\'image</button>';
        $html .= '</div>';
        $html .= '</div>';

        $html .= '<div class="form-group">';
        $html .= '<label for="image_desc">Description de l\'image (texte alternatif) :</label>';
        $html .= '<input type="text" id="image_desc" name="image_desc" placeholder="Courte description de l\'image" maxlength="255" class="form-control">';
        $html .= '</div>';

        // Bouton de soumission
        $html .= '<div class="form-actions">';
        $html .= '<button type="submit" class="btn btn-primary btn-lg" id="btn-submit-touite">Publier mon touite &rarr;</button>';
        $html .= '<a href="index.php?action=liste-touites" class="btn btn-secondary">Annuler</a>';
        $html .= '<span class="shortcut-hint"><kbd>Ctrl</kbd> + <kbd>Entrée</kbd> pour publier directement</span>';
        $html .= '</div>';

        $html .= '</form>';
        $html .= '</div>';

        return $html;
    }
}
