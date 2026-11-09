<?php

/**
 * Point d'entrée de l'application Touiteur.app
 * SAE BUT Informatique S3 - IUT Nancy-Charlemagne
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/src/classes/Autoloader.php';
\iutnc\touiteur\Autoloader::register();

try {
    $dispatcher = new \iutnc\touiteur\dispatcher\Dispatcher();
    $dispatcher->run();
} catch (\Throwable $e) {
    http_response_code(500);
    echo "<h1>Erreur critique de l'application</h1>";
    echo "<p>" . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString(), ENT_QUOTES, 'UTF-8') . "</pre>";
}
