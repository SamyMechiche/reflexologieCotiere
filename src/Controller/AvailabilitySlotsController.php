<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AvailabilitySlotsController extends AbstractController
{
    #[Route('/availability/slots', name: 'app_availability_slots')]
    public function index(): Response
    {
        return $this->render('availability_slots/index.html.twig', [
            'controller_name' => 'AvailabilitySlotsController',
        ]);
    }
}
