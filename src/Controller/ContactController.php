<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Message;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

final class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact', methods: ['GET', 'POST'])]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $name = $request->request->get('name');
            $email = $request->request->get('email');
            $phone = $request->request->get('phone');
            $content = $request->request->get('content');
            $subject = $request->request->get('subject');
            if ($name && $email && $phone && $content && $subject) {
                $msg = new Message();
                $msg->setName($name);
                $msg->setEmail($email);
                $msg->setPhone($phone);
                $msg->setContent($content);
                $msg->setSubject($subject);
                $msg->setSentAt(new \DateTime());
                $msg->setIsRead(false);
                $em->persist($msg);
                $em->flush();
                $this->addFlash('success', 'Votre message a bien été envoyé.');
                return $this->redirectToRoute('app_contact');
            } else {
                $this->addFlash('error', 'Merci de remplir tous les champs.');
            }
        }
        return $this->render('contact/index.html.twig', [
            'controller_name' => 'ContactController',
        ]);
    }
}
