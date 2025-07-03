<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

#[Route('/admin/dashboard', name: 'admin_dashboard')]
#[IsGranted('ROLE_ADMIN')]
class AdminDashboardController extends AbstractController
{
    public function __invoke(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();
        return $this->render('admin/dashboard.html.twig', [
            'users' => $users,
        ]);
    }
} 