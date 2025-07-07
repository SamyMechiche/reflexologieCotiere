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

final class AppointmentController extends AbstractController
{
    #[Route('/appointment', name: 'app_appointment', methods: ['GET', 'POST'])]
    public function index(AvailabilitySlotsRepository $slotsRepository, SessionRepository $sessionRepository, Request $request, EntityManagerInterface $em): Response
    {
        $slotId = $request->query->get('slotId');
        $slots = $slotsRepository->findBy(['is_booked' => false], ['date' => 'ASC', 'start_time' => 'ASC']);
        $sessions = $sessionRepository->findAll();
        $success = false;
        $error = null;

        if ($request->isMethod('POST')) {
            $slotId = $request->query->get('slotId');
            $slot = $slotsRepository->find($slotId);
            if (!$slot || $slot->isBooked()) {
                $error = "Ce créneau n'est plus disponible.";
            } else {
                $sessionId = $request->request->get('session_id');
                $comment = $request->request->get('comment');
                $session = $sessionRepository->find($sessionId);
                if (!$session) {
                    $error = "Séance invalide.";
                } else {
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
                            $user->setEmail($email);
                            $user->setPassword(''); // No password for guest
                            $em->persist($user);
                        }
                    }
                    if (!$error) {
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
                        $success = true;
                    }
                }
            }
        }
        return $this->render('appointment/index.html.twig', [
            'controller_name' => 'AppointmentController',
            'slots' => $slots,
            'sessions' => $sessions,
            'success' => $success,
            'error' => $error,
            'slotId' => $slotId,
        ]);
    }
}
