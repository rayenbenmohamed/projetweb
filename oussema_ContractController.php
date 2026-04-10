<?php

namespace App\Controller;

use App\Entity\Contract;
use App\Service\ContratService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/contract')]
class ContractController extends AbstractController
{
    private ContratService $contratService;

    public function __construct(ContratService $contratService)
    {
        $this->contratService = $contratService;
    }

    #[Route('/', name: 'app_contract_index')]
    public function index(): Response
    {
        $contracts = $this->contratService->search();

        return $this->render('contract/index.html.twig', [
            'contracts' => $contracts,
        ]);
    }

    #[Route('/{id}', name: 'app_contract_show')]
    public function show(Contract $contract): Response
    {
        return $this->render('contract/show.html.twig', [
            'contract' => $contract,
        ]);
    }
}
