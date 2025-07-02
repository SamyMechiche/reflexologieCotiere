<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Repository\SessionRepository;
use Doctrine\ORM\EntityManagerInterface;

final class SessionController extends AbstractController
{
    #[Route('/session', name: 'app_session')]
    public function index(EntityManagerInterface $em): Response
    {
        $sessions = $em->createQuery(
            'SELECT s, r, u FROM App\\Entity\\Session s
             LEFT JOIN s.reviews r
             LEFT JOIN r.user u'
        )->getResult();
        return $this->render('session/index.html.twig', [
            'sessions' => $sessions,
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
