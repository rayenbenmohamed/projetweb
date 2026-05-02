<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=devjava1', 'root', '');

// Fix orphaned type_contrat
$pdo->exec("UPDATE contract 
            LEFT JOIN type_contrat ON contract.type_contrat_id = type_contrat.id 
            SET contract.type_contrat_id = NULL 
            WHERE type_contrat.id IS NULL AND contract.type_contrat_id IS NOT NULL");

// If job_offre is orphaned, since it's not nullable in the entity, we might need a fallback or delete the contract.
// Let's check if there are any orphaned contracts for job_offre first:
$stmt = $pdo->query("SELECT id FROM contract 
                     LEFT JOIN job_offre ON contract.job_offre_id = job_offre.id 
                     WHERE job_offre.id IS NULL AND contract.job_offre_id IS NOT NULL");
$orphaned_jobs = $stmt->fetchAll();

if (count($orphaned_jobs) > 0) {
    // Delete contracts with orphaned job offers because job offers are required
    $pdo->exec("DELETE contract FROM contract 
                LEFT JOIN job_offre ON contract.job_offre_id = job_offre.id 
                WHERE job_offre.id IS NULL");
}

echo "Database cleaned of orphaned references.";
