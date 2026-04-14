<?php

namespace App\Service;

use App\Entity\Contract;
use App\Repository\ContractRepository;
use Doctrine\ORM\EntityManagerInterface;

class ContratService
{
    private EntityManagerInterface $entityManager;
    private ContractRepository $contractRepository;

    public function __construct(EntityManagerInterface $entityManager, ContractRepository $contractRepository)
    {
        $this->entityManager = $entityManager;
        $this->contractRepository = $contractRepository;
    }

    public function save(Contract $contract): void
    {
        // Ported logic: Calculate net salary if not set
        if ($contract->getSalary() !== null) {
            $contract->setSalary($contract->getSalary()); // This triggers the setter logic for net salary
        }

        $this->entityManager->persist($contract);
        $this->entityManager->flush();
    }

    public function delete(Contract $contract): void
    {
        $this->entityManager->remove($contract);
        $this->entityManager->flush();
    }

    /**
     * @return Contract[]
     */
    public function search(array $criteria = [], int $limit = 10, int $offset = 0): array
    {
        return $this->contractRepository->findBy($criteria, ['id' => 'DESC'], $limit, $offset);
    }
}
