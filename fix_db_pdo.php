<?php

$host = '127.0.0.1';
$user = 'root';
$pass = '';
$db = 'devjava';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sqls = [
        "CREATE TABLE IF NOT EXISTS type_contrat (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4",
        "ALTER TABLE contract MODIFY job_offre_id INT NULL", // Make it nullable if it already exists
        "ALTER TABLE contract ADD COLUMN IF NOT EXISTS type_contrat_id INT DEFAULT NULL",
        "ALTER TABLE contract ADD COLUMN IF NOT EXISTS job_offre_id INT NULL", // Backup if ADD is needed
        "ALTER TABLE contract ADD CONSTRAINT FK_E98F2859520D03A FOREIGN KEY (type_contrat_id) REFERENCES type_contrat (id) ON DELETE SET NULL",
        "ALTER TABLE contract ADD CONSTRAINT FK_E98F28592B8FF521 FOREIGN KEY (job_offre_id) REFERENCES job_offre (id) ON DELETE CASCADE",
        "CREATE INDEX IF NOT EXISTS IDX_E98F2859520D03A ON contract (type_contrat_id)",
        "CREATE INDEX IF NOT EXISTS IDX_E98F28592B8FF521 ON contract (job_offre_id)"
    ];

    foreach ($sqls as $sql) {
        echo "Executing: $sql\n";
        try {
            $pdo->exec($sql);
            echo "Success.\n";
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage() . "\n";
        }
    }

} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
