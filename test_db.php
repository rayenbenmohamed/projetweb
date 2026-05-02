<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=devjava1', 'root', '');
$stmt = $pdo->query('SELECT * FROM type_contrat');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
