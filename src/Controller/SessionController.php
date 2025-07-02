<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Repository\SessionRepository;

final class SessionController extends AbstractController
{
    #[Route('/session', name: 'app_session')]
    public function index(): Response
    {
        return $this->render('session/index.html.twig', [
            'controller_name' => 'SessionController',
        ]);
    }

    #[Route('/sessions-for-review', name: 'sessions_for_review', methods: ['GET'])]
    public function sessionsForReview(SessionRepository $sessionRepository): JsonResponse
    {
        $sessions = $sessionRepository->findAll();
        $data = [];
        foreach ($sessions as $session) {
            $data[] = [
                'id' => $session->getId(),
                'name' => $session->getName(),
                'duration' => $session->getDurationMinutes(),
            ];
        }
        return new JsonResponse($data);
    }
}
