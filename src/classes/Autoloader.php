<?php

namespace iutnc\touiteur;

/**
 * Autoloader conforme PSR-4 pour le namespace iutnc\touiteur
 */
class Autoloader {
    
    /**
     * Enregistre l'autoloader auprès de SPL
     */
    public static function register(): void {
        spl_autoload_register([__CLASS__, 'autoload']);
    }

    /**
     * Charge la classe demandée
     *
     * @param string $className Nom complet avec namespace (ex: iutnc\touiteur\action\Action)
     */
    public static function autoload(string $className): void {
        $prefix = 'iutnc\\touiteur\\';
        $baseDir = __DIR__ . '/';

        // Ne traiter que les classes du namespace racine iutnc\touiteur
        if (strncmp($prefix, $className, strlen($prefix)) !== 0) {
            return;
        }

        // Récupérer le chemin relatif après le préfixe
        $relativeClass = substr($className, strlen($prefix));

        // Remplacer les séparateurs de namespace par des séparateurs de répertoires
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

        if (file_exists($file)) {
            require_once $file;
        }
    }
}
