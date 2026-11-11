<?php

/**
 * Point d'entrée de l'application indépendante Back-Office de Touiteur.app
 * SAE BUT Informatique S3 - IUT Nancy-Charlemagne
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/src/classes/Autoloader.php';
\iutnc\touiteur\Autoloader::register();

try {
    $dispatcher = new \iutnc\touiteur\dispatcher\AdminDispatcher();
    $dispatcher->run();
} catch (\Throwable $e) {
    http_response_code(500);
    echo "<h1>Erreur critique du Back-Office</h1>";
    echo "<p>" . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString(), ENT_QUOTES, 'UTF-8') . "</pre>";
}
