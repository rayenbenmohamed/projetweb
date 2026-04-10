<?php

namespace App\Controller;

use App\Entity\TypeContrat;
use App\Repository\TypeContratRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/type-contrat')]
class TypeContratController extends AbstractController
{
    #[Route('/', name: 'app_type_contrat_index', methods: ['GET'])]
    public function index(Request $request, TypeContratRepository $typeContratRepository): Response
    {
        $q = (string) $request->query->get('q', '');
        $page = (int) $request->query->get('page', 1);
        $limit = (int) $request->query->get('limit', 10);
        $result = $typeContratRepository->searchPaginated($q, $page, $limit);

        return $this->render('type_contrat/index.html.twig', [
            'types' => $result['items'],
            'total' => $result['total'],
            'pages' => $result['pages'],
            'page' => $result['page'],
            'limit' => $result['limit'],
            'q' => $q,
        ]);
    }

    #[Route('/new', name: 'app_type_contrat_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $type = new TypeContrat();

        if ($request->isMethod('POST')) {
            $name = trim((string) $request->request->get('name'));
            $description = trim((string) $request->request->get('description'));

            if ($name === '') {
                $this->addFlash('error', 'Le nom est obligatoire.');
            } else {
                $type->setName($name);
                $type->setDescription($description !== '' ? $description : null);

                $entityManager->persist($type);
                $entityManager->flush();

                $this->addFlash('success', 'Type de contrat créé.');
                return $this->redirectToRoute('app_type_contrat_index');
            }
        }

        return $this->render('type_contrat/new.html.twig', [
            'type' => $type,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_type_contrat_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, TypeContrat $type, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            $name = trim((string) $request->request->get('name'));
            $description = trim((string) $request->request->get('description'));

            if ($name === '') {
                $this->addFlash('error', 'Le nom est obligatoire.');
            } else {
                $type->setName($name);
                $type->setDescription($description !== '' ? $description : null);

                $entityManager->flush();

                $this->addFlash('success', 'Type mis à jour.');
                return $this->redirectToRoute('app_type_contrat_index');
            }
        }

        return $this->render('type_contrat/edit.html.twig', [
            'type' => $type,
        ]);
    }

    #[Route('/{id}', name: 'app_type_contrat_delete', methods: ['POST'])]
    public function delete(Request $request, TypeContrat $type, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete_type_contrat_' . $type->getId(), (string) $request->request->get('_token'))) {
            $entityManager->remove($type);
            $entityManager->flush();
            $this->addFlash('success', 'Type supprimé.');
        }

        return $this->redirectToRoute('app_type_contrat_index');
    }
}

