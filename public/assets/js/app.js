/**
 * Touiteur.app - Interactions côté client
 * Projet SAE 3.1 - BUT Informatique Nancy-Charlemagne
 */

document.addEventListener('DOMContentLoaded', () => {

    // 1. Compteur de caractères dynamique pour le touite (limite stricte 235 car.)
    const textarea = document.getElementById('texte');
    const counter = document.getElementById('char-count');
    const submitBtn = document.getElementById('btn-submit-touite');

    if (textarea && counter) {
        const updateCount = () => {
            const len = textarea.value.length;
            counter.textContent = len;

            if (len > 235) {
                counter.style.color = '#dc2626'; // Rouge vif d'alerte
                counter.style.fontWeight = 'bold';
                if (submitBtn) submitBtn.disabled = true;
            } else if (len >= 200) {
                counter.style.color = '#d97706'; // Ambre orange franc bien distinct
                counter.style.fontWeight = 'bold';
                if (submitBtn) submitBtn.disabled = false;
            } else {
                counter.style.color = '#334155'; // Ardoise foncé net (pas blanc)
                counter.style.fontWeight = 'normal';
                if (submitBtn) submitBtn.disabled = false;
            }
        };

        textarea.addEventListener('input', updateCount);
        updateCount();

        // Raccourci clavier : Ctrl + Entrée pour publier
        textarea.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                e.preventDefault();
                const form = document.getElementById('publish-form');
                if (form && textarea.value.trim().length > 0 && textarea.value.length <= 235) {
                    form.submit();
                }
            }
        });
    }

    // 2. Synchronisation du sélecteur rapide de pseudo
    const quickUserSelect = document.getElementById('quick-user-select');
    const pseudoInput = document.getElementById('pseudo');
    if (quickUserSelect && pseudoInput) {
        quickUserSelect.addEventListener('change', () => {
            if (quickUserSelect.value) {
                pseudoInput.value = quickUserSelect.value;
            }
        });
    }

    // 3. Puces de suggestions de tags rapides
    const tagChips = document.querySelectorAll('.chip-suggestion');
    if (tagChips.length > 0 && textarea) {
        tagChips.forEach(chip => {
            chip.addEventListener('click', () => {
                const tag = chip.getAttribute('data-tag');
                if (!tag) return;
                
                const val = textarea.value;
                if (!val.includes(tag)) {
                    textarea.value = val ? (val.trimEnd() + ' ' + tag + ' ') : (tag + ' ');
                    textarea.focus();
                    textarea.dispatchEvent(new Event('input'));
                }
            });
        });
    }

    // 4. Prévisualisation en direct de l'image sélectionnée
    const imageInput = document.getElementById('image');
    const previewWrapper = document.getElementById('image-preview-wrapper');
    const previewImg = document.getElementById('image-preview');
    const removeBtn = document.getElementById('btn-remove-preview');

    if (imageInput && previewWrapper && previewImg) {
        imageInput.addEventListener('change', () => {
            const file = imageInput.files && imageInput.files[0];
            if (file) {
                if (file.size > 5 * 1024 * 1024) {
                    alert('Attention : l\'image dépasse la taille maximale autorisée de 5 Mo.');
                    imageInput.value = '';
                    previewWrapper.style.display = 'none';
                    return;
                }
                const reader = new FileReader();
                reader.onload = (e) => {
                    previewImg.src = e.target.result;
                    previewWrapper.style.display = 'flex';
                };
                reader.readAsDataURL(file);
            } else {
                previewWrapper.style.display = 'none';
            }
        });

        if (removeBtn) {
            removeBtn.addEventListener('click', () => {
                imageInput.value = '';
                previewImg.src = '#';
                previewWrapper.style.display = 'none';
            });
        }
    }

    // 5. Confirmation douce pour les actions de désabonnement
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.btn-unfollow');
        if (btn) {
            if (!confirm('Confirmer le désabonnement ?')) {
                e.preventDefault();
                e.stopPropagation();
            }
        }
    });

    // 6. Disparition fluide des alertes flash après 5 secondes
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach((alert) => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-6px)';
            setTimeout(() => alert.remove(), 400);
        }, 5000);
    });

});
