<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MTCController extends AbstractController
{
    #[Route('/mtc', name: 'app_mtc')]
    public function index(): Response
    {
        return $this->render('mtc/index.html.twig', [
            'controller_name' => 'MTCController',
        ]);
    }
}
