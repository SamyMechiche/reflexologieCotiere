<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\AppointmentRepository;
use App\Repository\InvoiceRepository;
use App\Repository\MessageRepository;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class UserController extends AbstractController
{
    #[Route('/user', name: 'app_user')]
    public function index(): Response
    {
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    #[Route('/user/dashboard', name: 'app_user_dashboard')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function dashboard(AppointmentRepository $appointmentRepository, InvoiceRepository $invoiceRepository, MessageRepository $messageRepository): Response
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

        return $this->render('user/dashboard.html.twig', [
            'pastAppointments' => $pastAppointments,
            'upcomingAppointments' => $upcomingAppointments,
            'calendarEvents' => json_encode($calendarEvents),
            'invoices' => $invoices,
            'messages' => $messages,
            'user' => $user,
        ]);
    }
}
