<?php

namespace App\Controller;

use App\Repository\ForumPostRepository;
use App\Repository\ForumCategoryRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/', name: 'app_dashboard')]
    public function index(ForumPostRepository $postRepo, ForumCategoryRepository $catRepo, UserRepository $userRepo): Response
    {
        return $this->render('dashboard/index.html.twig', [
            'count_users' => $userRepo->count([]),
            'count_posts' => $postRepo->count([]),
            'count_categories' => $catRepo->count([]),
            'latest_posts' => $postRepo->findBy([], ['createdAt' => 'DESC'], 5),
        ]);
    }
}
