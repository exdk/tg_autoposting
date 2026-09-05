<?php

class Database {
    private PDO $pdo;

    public function __construct($config) {
        $this->pdo = new PDO($config['dsn'], $config['user'], $config['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
    }

    public function insertNews($title, $summary, $hash) {
        $stmt = $this->pdo->prepare("INSERT INTO pars (title, summary, hash) VALUES (?, ?, ?)");
        $stmt->execute([$title, $summary, $hash]);
    }

    public function newsExists($hash): bool {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM pars WHERE hash = ?");
        $stmt->execute([$hash]);
        return $stmt->fetchColumn() > 0;
    }

    public function getRecentNews($limit = 50): array {
        $stmt = $this->pdo->query("SELECT summary FROM pars ORDER BY id DESC LIMIT $limit");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
