<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Message;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\RedirectResponse;

final class MessageController extends AbstractController
{
    #[Route('/message', name: 'app_message')]
    public function index(): Response
    {
        return $this->render('message/index.html.twig', [
            'controller_name' => 'MessageController',
        ]);
    }

    #[Route('/user/message/send', name: 'app_message_send', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function send(Request $request, EntityManagerInterface $em): RedirectResponse
    {
        $user = $this->getUser();
        $subject = $request->request->get('subject');
        $content = $request->request->get('content');
        if ($subject && $content) {
            $msg = new Message();
            $msg->setUser($user);
            $msg->setSubject($subject);
            $msg->setContent($content);
            $msg->setSentAt(new \DateTime());
            $msg->setIsRead(false);
            $em->persist($msg);
            $em->flush();
        }
        return $this->redirectToRoute('app_user_dashboard');
    }

    #[Route('/admin/messages', name: 'admin_messages')]
    #[IsGranted('ROLE_ADMIN')]
    public function adminIndex(EntityManagerInterface $em): Response
    {
        $messages = $em->getRepository(Message::class)->findBy([], ['sent_at' => 'DESC']);
        return $this->render('message/admin_index.html.twig', [
            'messages' => $messages,
        ]);
    }
}
