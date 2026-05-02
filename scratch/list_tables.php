<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=devjava1', 'root', '');
$tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
print_r($tables);
