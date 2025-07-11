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
use App\Repository\ReviewRepository;

final class ReviewController extends AbstractController
{
    #[Route('/admin/review', name: 'app_review')]
    public function index(ReviewRepository $reviewRepository): Response
    {
        $reviews = $reviewRepository->findAll();
        return $this->render('review/index.html.twig', [
            'reviews' => $reviews,
        ]);
    }

    #[Route('/admin/review/{id}/delete', name: 'admin_review_delete', methods: ['POST'])]
    public function deleteReview(int $id, ReviewRepository $reviewRepository, EntityManagerInterface $em, Request $request): Response
    {
        $review = $reviewRepository->find($id);
        if (!$review) {
            $this->addFlash('danger', 'Avis non trouvé.');
            return $this->redirectToRoute('app_review');
        }
        if ($this->isCsrfTokenValid('delete_review_' . $review->getId(), $request->request->get('_token'))) {
            $em->remove($review);
            $em->flush();
            $this->addFlash('success', 'Avis supprimé avec succès.');
        } else {
            $this->addFlash('danger', 'Token CSRF invalide.');
        }
        return $this->redirectToRoute('app_review');
    }

    #[Route('/review', name: 'public_review', methods: ['GET'])]
    public function publicIndex(ReviewRepository $reviewRepository, Request $request): Response
    {
        $sort = $request->query->get('sort', 'date_new');
        switch ($sort) {
            case 'alpha':
                $reviews = $reviewRepository->createQueryBuilder('r')
                    ->leftJoin('r.user', 'u')
                    ->orderBy('u.firstName', 'ASC')
                    ->getQuery()->getResult();
                break;
            case 'rating_high':
                $reviews = $reviewRepository->createQueryBuilder('r')
                    ->orderBy('r.rating', 'DESC')
                    ->getQuery()->getResult();
                break;
            case 'rating_low':
                $reviews = $reviewRepository->createQueryBuilder('r')
                    ->orderBy('r.rating', 'ASC')
                    ->getQuery()->getResult();
                break;
            case 'date_old':
                $reviews = $reviewRepository->createQueryBuilder('r')
                    ->orderBy('r.created_at', 'ASC')
                    ->getQuery()->getResult();
                break;
            case 'date_new':
            default:
                $reviews = $reviewRepository->createQueryBuilder('r')
                    ->orderBy('r.created_at', 'DESC')
                    ->getQuery()->getResult();
                break;
        }
        return $this->render('review/index.html.twig', [
            'reviews' => $reviews,
            'sort' => $sort,
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
