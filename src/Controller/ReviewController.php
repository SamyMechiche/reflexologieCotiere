<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Review;
use App\Entity\Session;

final class ReviewController extends AbstractController
{
    #[Route('/review', name: 'app_review')]
    public function index(): Response
    {
        return $this->render('review/index.html.twig', [
            'controller_name' => 'ReviewController',
        ]);
    }

    #[Route('/submit-review', name: 'submit_review', methods: ['POST'])]
    public function submitReview(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['success' => false, 'message' => 'Vous devez être connecté pour laisser un avis.'], 401);
        }
        $sessionId = $request->request->get('session');
        $stars = $request->request->get('stars');
        $comment = $request->request->get('comment');
        if (!$sessionId || !$stars) {
            return new JsonResponse(['success' => false, 'message' => 'Veuillez sélectionner une séance et une note.'], 400);
        }
        $session = $em->getRepository(Session::class)->find($sessionId);
        if (!$session) {
            return new JsonResponse(['success' => false, 'message' => 'Séance non trouvée.'], 404);
        }
        $review = new Review();
        $review->setUser($user);
        $review->setSession($session);
        $review->setRating((int)$stars);
        $review->setComment($comment);
        $review->setCreatedAt(new \DateTime());
        $em->persist($review);
        $em->flush();
        return new JsonResponse(['success' => true, 'message' => 'Merci pour votre avis !']);
    }
}
