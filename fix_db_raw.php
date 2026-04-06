<?php

$host = '127.0.0.1';
$user = 'root';
$pass = '';
$db = 'devjava';

$mysqli = new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_error) {
    die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
}

$sqls = [
    "CREATE TABLE IF NOT EXISTS type_contrat (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4",
    "ALTER TABLE contract ADD COLUMN type_contrat_id INT DEFAULT NULL",
    "ALTER TABLE contract ADD COLUMN job_offre_id INT NOT NULL",
    "ALTER TABLE contract ADD CONSTRAINT FK_E98F2859520D03A FOREIGN KEY (type_contrat_id) REFERENCES type_contrat (id) ON DELETE SET NULL",
    "ALTER TABLE contract ADD CONSTRAINT FK_E98F28592B8FF521 FOREIGN KEY (job_offre_id) REFERENCES job_offre (id) ON DELETE CASCADE",
    "CREATE INDEX IDX_E98F2859520D03A ON contract (type_contrat_id)",
    "CREATE INDEX IDX_E98F28592B8FF521 ON contract (job_offre_id)"
];

foreach ($sqls as $sql) {
    echo "Executing: $sql\n";
    if ($mysqli->query($sql)) {
        echo "Success.\n";
    } else {
        echo "Error: (" . $mysqli->errno . ") " . $mysqli->error . "\n";
    }
}

$mysqli->close();
