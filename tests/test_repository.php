<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/src/classes/Autoloader.php';
\iutnc\touiteur\Autoloader::register();

use iutnc\touiteur\repository\TouiteurRepository;
use iutnc\touiteur\repository\ConnectionFactory;
use iutnc\touiteur\exceptions\ValidationException;

echo "=== Démarrage de la suite de tests automatisés Touiteur.app ===\n\n";

$testsPassed = 0;
$testsFailed = 0;

function assertTest(bool $condition, string $title): void {
    global $testsPassed, $testsFailed;
    if ($condition) {
        echo "[OK] {$title}\n";
        $testsPassed++;
    } else {
        echo "[ECHEC] {$title}\n";
        $testsFailed++;
    }
}

try {
    // 0. Réinitialisation de la base SQLite pour garantir l'idempotence des tests
    $pdo = ConnectionFactory::makeConnection();
    $sqlSeed = file_get_contents(dirname(__DIR__) . '/bd/seed_sqlite.sql');
    $pdo->exec($sqlSeed);

    $repo = new TouiteurRepository();

    // Test 1 : Lecture des touites et pagination
    $touites = $repo->getTouites(1, 5);
    assertTest(count($touites) === 5, "Pagination : 5 touites retournés en page 1");
    assertTest($touites[0]->getDatePublication() >= $touites[1]->getDatePublication(), "Tri chronologique inverse respecté");

    // Test 2 : Extraction des tags statique
    $extracted = TouiteurRepository::extractTags("Super projet #sae avec #php et #Securite !");
    assertTest($extracted === ['sae', 'php', 'Securite'], "Extraction automatique des #tags");

    // Test 3 : Récupération d'un touite par identifiant
    $t1 = $repo->getTouiteById(1);
    assertTest($t1 !== null && $t1->getAuteurPseudo() === 'alice_tech', "Lecture détaillée du touite #1");
    assertTest(!empty($t1->getTags()), "Présence de tags associés sur le touite #1");

    // Test 4 : Publication d'un nouveau touite
    $initialTouitesCount = $repo->countTouites();
    $newId = $repo->publishTouite('bob_dev', 'Test unitaire automatisé avec #test_tag et #php !', null, null);
    $afterTouitesCount = $repo->countTouites();
    assertTest($afterTouitesCount === $initialTouitesCount + 1, "Publication d'un touite enregistrée en base");

    $publishedTouite = $repo->getTouiteById($newId);
    assertTest(in_array('test_tag', $publishedTouite->getTags(), true), "Association automatique du tag #test_tag au nouveau touite");

    // Test 5 : Rejet d'un touite > 235 caractères
    $tooLongText = str_repeat("A", 236);
    try {
        $repo->publishTouite('bob_dev', $tooLongText);
        assertTest(false, "Rejet d'un touite excédant 235 caractères");
    } catch (ValidationException $e) {
        assertTest(true, "Rejet d'un touite excédant 235 caractères (Exception attrapée : " . $e->getMessage() . ")");
    }

    // Test 6 : Évaluation d'un touite (+1 / -1) et unicité du vote
    $oldNote = $repo->getTouiteById(2)->getNote();
    $rateResult = $repo->rateTouite(2, 'eva_design', 1);
    assertTest($rateResult['note'] === $oldNote + 1, "Évaluation +1 d'un touite");

    // Tentative de second vote par eva_design sur le touite 2 -> Doit échouer
    try {
        $repo->rateTouite(2, 'eva_design', 1);
        assertTest(false, "Unicité du vote : interdiction d'évaluer deux fois le même touite");
    } catch (ValidationException $e) {
        assertTest(true, "Unicité du vote : interdiction d'évaluer deux fois (Exception attrapée)");
    }

    // Test 7 : Abonnements utilisateurs (Suivre / Ne plus suivre)
    $repo->unfollowUser('eva_design', 'bob_dev');
    assertTest(!$repo->isFollowingUser('eva_design', 'bob_dev'), "Désabonnement utilisateur");

    $repo->followUser('eva_design', 'bob_dev');
    assertTest($repo->isFollowingUser('eva_design', 'bob_dev'), "Abonnement utilisateur");

    // Interdiction d'auto-suivi
    try {
        $repo->followUser('eva_design', 'eva_design');
        assertTest(false, "Interdiction d'auto-suivi");
    } catch (ValidationException $e) {
        assertTest(true, "Interdiction d'auto-suivi validée");
    }

    // Test 8 : Abonnements aux tags
    $repo->followTag('eva_design', 'crypto');
    assertTest($repo->isFollowingTag('eva_design', 'crypto'), "Abonnement au tag #crypto");
    $repo->unfollowTag('eva_design', 'crypto');
    assertTest(!$repo->isFollowingTag('eva_design', 'crypto'), "Désabonnement du tag #crypto");

    // Test 9 : Mur de l'utilisateur
    $wallTouites = $repo->getUserWallTouites('alice_tech', 1, 10);
    assertTest(count($wallTouites) > 0, "Génération du mur personnalisé de l'utilisateur");

    // Test 10 : Espace narcissique (followers et score moyen)
    $followers = $repo->getUserFollowers('alice_tech');
    assertTest(count($followers) >= 4, "Espace narcissique : liste des suiveurs d'Alice");
    $avgScore = $repo->getUserAverageScore('alice_tech');
    assertTest(is_float($avgScore), "Calcul du score moyen des touites : {$avgScore}");

    // Test 11 : Back-office Influenceurs et Tendances
    $influencers = $repo->getMostFollowedUsers(10, 'ASC');
    assertTest(!empty($influencers), "Back-office : liste des utilisateurs ordonnés par suiveurs");

    $trends = $repo->getMostMentionedTags(10, 'ASC');
    assertTest(!empty($trends), "Back-office : liste des tags ordonnés par mentions");

} catch (\Throwable $e) {
    echo "\n[ERREUR CRITIQUE PENDANT LES TESTS] " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    $testsFailed++;
}

echo "\n======================================================\n";
echo "Résultats : {$testsPassed} test(s) réussi(s), {$testsFailed} échec(s).\n";
echo "======================================================\n";

if ($testsFailed > 0) {
    exit(1);
}
