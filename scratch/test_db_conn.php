<?php
try {
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=devjava1", "root", "");
    echo "Connection successful!\n";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
}
