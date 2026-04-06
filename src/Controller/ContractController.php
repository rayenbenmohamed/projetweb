<?php

namespace App\Controller;

use App\Entity\Contract;
use App\Form\ContractType;
use App\Repository\ContractRepository;
use App\Repository\TypeContratRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/contract')]
class ContractController extends AbstractController
{
    #[Route('/', name: 'app_contract_index', methods: ['GET'])]
    public function index(Request $request, ContractRepository $contractRepository, TypeContratRepository $typeContratRepository): Response
    {
        $search = $request->query->get('search');
        $status = $request->query->get('status');
        $typeId = $request->query->get('type') ?: null;
        
        $page = (int) $request->query->get('page', 1);
        $limit = 5; 
        $offset = ($page - 1) * $limit;
        
        $totalContracts = $contractRepository->countByFilters($search, $status, $typeId);
        $totalPages = ceil($totalContracts / $limit);

        return $this->render('contract/index.html.twig', [
            'contracts' => $contractRepository->findByFilters($search, $status, $typeId, $limit, $offset),
            'types' => $typeContratRepository->findAll(),
            'current_search' => $search,
            'current_status' => $status,
            'current_type' => $typeId,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_contracts' => $totalContracts,
        ]);
    }

    #[Route('/new', name: 'app_contract_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $contract = new Contract();
        $form = $this->createForm(ContractType::class, $contract);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Logic handled in Entity::setSalary for net salary calculation
            $entityManager->persist($contract);
            $entityManager->flush();

            $this->addFlash('success', 'Contrat créé avec succès.');
            return $this->redirectToRoute('app_contract_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('contract/new.html.twig', [
            'contract' => $contract,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_contract_show', methods: ['GET'])]
    public function show(Contract $contract): Response
    {
        return $this->render('contract/show.html.twig', [
            'contract' => $contract,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_contract_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Contract $contract, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ContractType::class, $contract);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Contrat modifié avec succès.');
            return $this->redirectToRoute('app_contract_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('contract/edit.html.twig', [
            'contract' => $contract,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_contract_delete', methods: ['POST'])]
    public function delete(Request $request, Contract $contract, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$contract->getId(), $request->request->get('_token'))) {
            $entityManager->remove($contract);
            $entityManager->flush();
            $this->addFlash('success', 'Contrat supprimé.');
        }

        return $this->redirectToRoute('app_contract_index', [], Response::HTTP_SEE_OTHER);
    }
}
