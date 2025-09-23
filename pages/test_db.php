<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$dbPath = __DIR__ . '/../data/monarch.db';

try {
    $pdo = new PDO("sqlite:" . $dbPath);
    echo "Connected to monarch.db successfully.";
} catch (PDOException $e) {
    echo "Connection failed: " . htmlspecialchars($e->getMessage());
}