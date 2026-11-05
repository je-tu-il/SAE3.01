<?php

namespace iutnc\touiteur\repository;

use PDO;
use iutnc\touiteur\exceptions\DatabaseException;

/**
 * Fabrique de connexions PDO (Singleton de connexion)
 */
class ConnectionFactory {
    
    private static ?array $config = null;
    private static ?PDO $pdo = null;

    /**
     * Définit le fichier de configuration INI
     */
    public static function setConfig(string $configFile): void {
        if (!file_exists($configFile)) {
            throw new DatabaseException("Fichier de configuration introuvable : {$configFile}");
        }
        $parsed = parse_ini_file($configFile);
        if ($parsed === false) {
            throw new DatabaseException("Erreur de lecture du fichier INI : {$configFile}");
        }
        self::$config = $parsed;
        self::$pdo = null; // Réinitialise l'instance si nouvelle config
    }

    /**
     * Crée ou retourne la connexion PDO active
     */
    public static function makeConnection(): PDO {
        if (self::$pdo !== null) {
            return self::$pdo;
        }

        if (self::$config === null) {
            // Recherche par défaut de config/db.ini
            $defaultFile = dirname(__DIR__, 3) . '/config/db.ini';
            if (file_exists($defaultFile)) {
                self::setConfig($defaultFile);
            } else {
                throw new DatabaseException("Aucune configuration de base de données fournie.");
            }
        }

        $driver = self::$config['driver'] ?? 'mysql';

        try {
            if ($driver === 'sqlite') {
                $dbPath = self::$config['database'] ?? 'bd/touiteur.sqlite';
                if (!str_starts_with($dbPath, '/') && !str_contains($dbPath, ':')) {
                    // Chemin relatif par rapport à la racine du projet
                    $dbPath = dirname(__DIR__, 3) . '/' . $dbPath;
                }
                self::$pdo = new PDO("sqlite:{$dbPath}");
                self::$pdo->exec("PRAGMA foreign_keys = ON;");
            } else {
                $host = self::$config['host'] ?? 'localhost';
                $port = self::$config['port'] ?? '3306';
                $dbname = self::$config['database'] ?? 'touiteur';
                $charset = self::$config['charset'] ?? 'utf8mb4';
                $user = self::$config['username'] ?? 'root';
                $pass = self::$config['password'] ?? '';

                $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";
                self::$pdo = new PDO($dsn, $user, $pass);
            }

            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            return self::$pdo;
        } catch (\PDOException $e) {
            throw new DatabaseException("Échec de connexion à la base de données : " . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }
}
