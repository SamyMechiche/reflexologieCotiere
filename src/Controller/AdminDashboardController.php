<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Repository\AppointmentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

#[Route('/admin/dashboard', name: 'admin_dashboard')]
#[IsGranted('ROLE_ADMIN')]
class AdminDashboardController extends AbstractController
{
    public function __invoke(UserRepository $userRepository, AppointmentRepository $appointmentRepository): Response
    {
        $users = $userRepository->findAll();
        $upcomingAppointments = $appointmentRepository->findUpcomingAppointments();
        return $this->render('admin/dashboard.html.twig', [
            'users' => $users,
            'upcomingAppointments' => $upcomingAppointments,
        ]);
    }
} 