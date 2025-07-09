<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    // Login route: Handles user authentication and displays login errors if any
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    // Logout route: The logic is handled by Symfony's security system (firewall)
    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        // This method is never executed. Symfony intercepts this route to log out the user securely.
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
