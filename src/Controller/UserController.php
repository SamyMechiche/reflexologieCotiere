<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\AppointmentRepository;
use App\Repository\InvoiceRepository;
use App\Repository\MessageRepository;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\ReviewRepository;
use App\Repository\SessionRepository;

final class UserController extends AbstractController
{
    #[Route('/user', name: 'app_user')]
    public function index(): Response
    {
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    // User dashboard: Only accessible to authenticated users
    #[Route('/user/dashboard', name: 'app_user_dashboard')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function dashboard(AppointmentRepository $appointmentRepository, InvoiceRepository $invoiceRepository, MessageRepository $messageRepository, ReviewRepository $reviewRepository, SessionRepository $sessionRepository, Request $request): Response
    {
        $user = $this->getUser();
        // Fetch all appointments for the user
        $appointments = $appointmentRepository->findBy(['user' => $user], ['appointment_date' => 'DESC']);
        // Fetch all invoices for the user
        $invoices = $invoiceRepository->findBy(['user' => $user]);
        // Fetch all messages for the user
        $messages = $messageRepository->findBy(['user' => $user], ['sent_at' => 'DESC']);

        // Split appointments into past and upcoming
        $now = new \DateTime();
        $pastAppointments = [];
        $upcomingAppointments = [];
        foreach ($appointments as $appt) {
            if ($appt->getAppointmentDate() < $now) {
                $pastAppointments[] = $appt;
            } else {
                $upcomingAppointments[] = $appt;
            }
        }

        // Prepare appointment data for JS calendar
        $calendarEvents = array_map(function($appt) {
            return [
                'title' => $appt->getSession() ? $appt->getSession()->getName() : 'Séance',
                'start' => $appt->getAppointmentDate()->format('Y-m-d'),
            ];
        }, $upcomingAppointments);

        // Fetch all sessions for filter dropdown
        $sessions = $sessionRepository->findAll();
        // Get filter values from query parameters
        $filterSessionId = $request->query->get('filter_session');
        $filterRating = $request->query->get('filter_rating');
        $filterSession = $filterSessionId ? $sessionRepository->find($filterSessionId) : null;
        $filterRatingInt = $filterRating ? (int)$filterRating : null;
        // Fetch reviews with filters
        $reviews = $reviewRepository->findByUserWithFilters($user, $filterSession, $filterRatingInt);

        return $this->render('user/dashboard.html.twig', [
            'pastAppointments' => $pastAppointments,
            'upcomingAppointments' => $upcomingAppointments,
            'calendarEvents' => json_encode($calendarEvents),
            'invoices' => $invoices,
            'messages' => $messages,
            'user' => $user,
            'reviews' => $reviews,
            'sessions' => $sessions,
            'filterSessionId' => $filterSessionId,
            'filterRating' => $filterRating,
        ]);
    }

    // Appointment cancellation: Only the owner can cancel, with CSRF protection
    #[Route('/user/appointment/{id}/cancel', name: 'app_user_appointment_cancel', methods: ['POST'])]
    public function cancelAppointment(int $id, AppointmentRepository $appointmentRepository, EntityManagerInterface $entityManager, Request $request): Response
    {
        $user = $this->getUser();
        $appointment = $appointmentRepository->find($id);
        // Security: Ensure the appointment exists and belongs to the current user
        if (!$appointment || $appointment->getUser() !== $user) {
            throw $this->createNotFoundException('Rendez-vous non trouvé.');
        }
        // CSRF protection: Validate the token before proceeding
        if ($this->isCsrfTokenValid('cancel'.$appointment->getId(), $request->request->get('_token'))) {
            $slot = $appointment->getAvailabilitySlot();
            if ($slot) {
                $slot->setIsBooked(false);
                $slot->setAppointment(null);
                $entityManager->persist($slot);
            }
            $entityManager->remove($appointment);
            $entityManager->flush();
            $this->addFlash('success', 'Rendez-vous annulé avec succès.');
        } else {
            $this->addFlash('danger', 'Token CSRF invalide.');
        }
        return $this->redirectToRoute('app_user_dashboard');
    }
}
