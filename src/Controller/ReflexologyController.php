<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ReflexologyController extends AbstractController
{
    #[Route('/reflexology', name: 'app_reflexology')]
    public function index(): Response
    {
        return $this->render('reflexology/index.html.twig', [
            'controller_name' => 'ReflexologyController',
        ]);
    }
}
