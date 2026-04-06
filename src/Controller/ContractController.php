<?php

namespace App\Controller;

use App\Entity\Contract;
use App\Service\ContratService;
use App\Repository\UserRepository;
use App\Repository\TypeContratRepository;
use App\Repository\JobOffreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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

    #[Route('/new', name: 'app_contract_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request, 
        UserRepository $userRepo, 
        TypeContratRepository $typeRepo, 
        JobOffreRepository $jobRepo
    ): Response {
        $contract = new Contract();
        
        if ($request->isMethod('POST')) {
            $contract->setStartDate(new \DateTime($request->request->get('start_date')));
            if ($request->request->get('end_date')) {
                $contract->setEndDate(new \DateTime($request->request->get('end_date')));
            }
            $contract->setSalary((int) $request->request->get('salary'));
            $contract->setStatus($request->request->get('status', 'En Attente'));
            $contract->setIsSigned($request->request->get('is_signed') === 'on');
            if ($request->request->get('signed_at')) {
                $contract->setSignedAt(new \DateTime($request->request->get('signed_at')));
            }
            
            $contract->setTypeContrat($typeRepo->find($request->request->get('type_contrat_id')));
            $contract->setCandidate($userRepo->find($request->request->get('candidate_id')));
            $contract->setRecruiter($userRepo->find($request->request->get('recruiter_id')));
            $contract->setJobOffre($jobRepo->find($request->request->get('job_offre_id')));

            $this->contratService->save($contract);

            return $this->redirectToRoute('app_contract_index');
        }

        return $this->render('contract/new.html.twig', [
            'candidates' => $userRepo->findBy(['role' => 'ROLE_CANDIDAT']),
            'recruiters' => $userRepo->findBy(['role' => 'ROLE_RECRUITER']),
            'types' => $typeRepo->findAll(),
            'offres' => $jobRepo->findAll(),
            'contract' => $contract,
        ]);
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

    #[Route('/{id}/edit', name: 'app_contract_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request, 
        Contract $contract,
        UserRepository $userRepo, 
        TypeContratRepository $typeRepo, 
        JobOffreRepository $jobRepo
    ): Response {
        if ($request->isMethod('POST')) {
            $contract->setStartDate(new \DateTime($request->request->get('start_date')));
            if ($request->request->get('end_date')) {
                $contract->setEndDate(new \DateTime($request->request->get('end_date')));
            }
            $contract->setSalary((int) $request->request->get('salary'));
            $contract->setStatus($request->request->get('status'));
            $contract->setIsSigned($request->request->get('is_signed') === 'on');
            if ($request->request->get('signed_at')) {
                $contract->setSignedAt(new \DateTime($request->request->get('signed_at')));
            } else {
                $contract->setSignedAt(null);
            }
            
            $contract->setTypeContrat($typeRepo->find($request->request->get('type_contrat_id')));
            $contract->setCandidate($userRepo->find($request->request->get('candidate_id')));
            $contract->setRecruiter($userRepo->find($request->request->get('recruiter_id')));
            $contract->setJobOffre($jobRepo->find($request->request->get('job_offre_id')));

            $this->contratService->save($contract);

            return $this->redirectToRoute('app_contract_index');
        }

        return $this->render('contract/edit.html.twig', [
            'candidates' => $userRepo->findBy(['role' => 'ROLE_CANDIDAT']),
            'recruiters' => $userRepo->findBy(['role' => 'ROLE_RECRUITER']),
            'types' => $typeRepo->findAll(),
            'offres' => $jobRepo->findAll(),
            'contract' => $contract,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_contract_delete', methods: ['POST'])]
    public function delete(Request $request, Contract $contract): Response
    {
        if ($this->isCsrfTokenValid('delete' . $contract->getId(), $request->request->get('_token'))) {
            $this->contratService->delete($contract);
        }

        return $this->redirectToRoute('app_contract_index');
    }
}
