<?php

use App\Kernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

require __DIR__.'/vendor/autoload.php';

$kernel = new Kernel($_SERVER['APP_ENV'] ?? 'dev', (bool) ($_SERVER['APP_DEBUG'] ?? true));
$kernel->boot();

$container = $kernel->getContainer();
$connection = $container->get('doctrine.dbal.default_connection');

$sqls = [
    "CREATE TABLE IF NOT EXISTS type_contrat (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4",
    "ALTER TABLE contract ADD COLUMN IF NOT EXISTS type_contrat_id INT DEFAULT NULL",
    "ALTER TABLE contract ADD COLUMN IF NOT EXISTS job_offre_id INT NOT NULL",
    "ALTER TABLE contract ADD CONSTRAINT FK_E98F2859520D03A FOREIGN KEY IF NOT EXISTS (type_contrat_id) REFERENCES type_contrat (id) ON DELETE SET NULL",
    "ALTER TABLE contract ADD CONSTRAINT FK_E98F28592B8FF521 FOREIGN KEY IF NOT EXISTS (job_offre_id) REFERENCES job_offre (id) ON DELETE CASCADE",
    "CREATE INDEX IF NOT EXISTS IDX_E98F2859520D03A ON contract (type_contrat_id)",
    "CREATE INDEX IF NOT EXISTS IDX_E98F28592B8FF521 ON contract (job_offre_id)"
];

foreach ($sqls as $sql) {
    try {
        echo "Executing: $sql\n";
        $connection->executeStatement($sql);
        echo "Success.\n";
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
