<?php

namespace App\Controller;

use App\Entity\Cv;
use App\Service\CvService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/cv')]
class CvController extends AbstractController
{
    private CvService $cvService;

    public function __construct(CvService $cvService)
    {
        $this->cvService = $cvService;
    }

    #[Route('/', name: 'app_cv_index')]
    public function index(): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $cvs = $this->cvService->getCvByUserId($user->getId());

        return $this->render('cv/index.html.twig', [
            'cvs' => $cvs,
        ]);
    }

    #[Route('/new', name: 'app_cv_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $cv = new Cv();
        // Here you would handle the form submission and use $this->cvService->save($cv, $this->getUser());

        return $this->render('cv/new.html.twig', [
            'cv' => $cv,
        ]);
    }

    #[Route('/{id}', name: 'app_cv_show')]
    public function show(Cv $cv): Response
    {
        return $this->render('cv/show.html.twig', [
            'cv' => $cv,
        ]);
    }
}
