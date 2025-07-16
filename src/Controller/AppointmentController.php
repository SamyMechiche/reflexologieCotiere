<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\AvailabilitySlotsRepository;
use App\Entity\Appointment;
use App\Entity\User;
use App\Entity\Session;
use App\Repository\SessionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;

final class AppointmentController extends AbstractController
{
    // Appointment booking page: Handles both GET (show slots) and POST (book appointment)
    #[Route('/appointment', name: 'app_appointment', methods: ['GET', 'POST'])]
    public function index(AvailabilitySlotsRepository $slotsRepository, SessionRepository $sessionRepository, Request $request, EntityManagerInterface $em, MailerInterface $mailer): Response
    {
        $slotId = $request->query->get('slotId');
        // Query all available slots (not booked)
        $slots = $slotsRepository->findBy(['is_booked' => false], ['date' => 'ASC', 'start_time' => 'ASC']);
        // Query all available session types
        $sessions = $sessionRepository->findAll();
        $success = false;
        if (!$request->isMethod('POST')) {
            $success = $request->query->get('success') == 1;
        }
        $error = null;

        if ($request->isMethod('POST')) {
            $slotId = $request->request->get('slotId');
            $slot = $slotsRepository->find($slotId);
            // Error if slot is already booked
            if (!$slot || $slot->isBooked()) {
                $error = "Ce créneau n'est plus disponible.";
            } else {
                $sessionId = $request->request->get('session_id');
                $comment = $request->request->get('comment');
                $session = $sessionRepository->find($sessionId);
                if (!$session) {
                    $error = "Séance invalide.";
                } else {
                    // If user is logged in, use their account; otherwise, create a guest user
                    if ($this->getUser()) {
                        $user = $this->getUser();
                    } else {
                        $name = $request->request->get('name');
                        $email = $request->request->get('email');
                        if (!$name || !$email) {
                            $error = "Merci de remplir vos informations.";
                        } else {
                            $user = new User();
                            $user->setFirstName($name);
                            $user->setLastName('-'); // Set default last name for guest
                            $user->setEmail($email);
                            $user->setPassword(''); // No password for guest
                            $em->persist($user);
                        }
                    }
                    if (!$error) {
                        // Create and persist the new appointment
                        $appointment = new Appointment();
                        $appointment->setUser($user);
                        $appointment->setSession($session);
                        $appointment->setAppointmentDate((new \DateTime($slot->getDate()->format('Y-m-d').' '.$slot->getStartTime()->format('H:i:s'))));
                        $appointment->setHealthInformations($comment);
                        $appointment->setCreatedAt(new \DateTime());
                        $appointment->setAvailabilitySlot($slot);
                        $slot->setIsBooked(true);
                        $em->persist($appointment);
                        $em->flush();

                        // Send confirmation email
                        $emailMessage = (new TemplatedEmail())
                            ->from(new Address('mailer@gmail.com', 'Réflexologie Côtière'))
                            ->to($user->getEmail())
                            ->subject('Confirmation de votre rendez-vous')
                            ->htmlTemplate('appointment/confirmation_email.html.twig')
                            ->context([
                                'user' => $user,
                                'appointment' => $appointment,
                            ]);
                        $mailer->send($emailMessage);

                        return $this->redirectToRoute('app_appointment', ['success' => 1]);
                    }
                }
            }
        }

        // Prepare slots for FullCalendar (JS calendar integration)
        $calendarEvents = array_map(function($slot) {
            return [
                'id' => $slot->getId(),
                'title' => 'Disponible',
                'start' => $slot->getDate()->format('Y-m-d') . 'T' . $slot->getStartTime()->format('H:i:s'),
                'end' => $slot->getDate()->format('Y-m-d') . 'T' . $slot->getEndTime()->format('H:i:s'),
            ];
        }, $slots);

        return $this->render('appointment/index.html.twig', [
            'controller_name' => 'AppointmentController',
            'slots' => $slots,
            'sessions' => $sessions,
            'success' => $success,
            'error' => $error,
            'slotId' => $slotId,
            'calendarEvents' => json_encode($calendarEvents),
        ]);
    }
}
