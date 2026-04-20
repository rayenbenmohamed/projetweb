<?php
$pdo = new PDO('mysql:host=127.0.0.1', 'root', '');
echo "Server Version: " . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . "\n";
echo "Server Info: " . $pdo->getAttribute(PDO::ATTR_SERVER_INFO) . "\n";
