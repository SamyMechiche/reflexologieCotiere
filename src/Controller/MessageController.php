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
            $msg->setUser($user); // user is always set due to IS_AUTHENTICATED_FULLY
            $msg->setSubject($subject);
            $msg->setContent($content);
            $msg->setSentAt(new \DateTime());
            $msg->setIsRead(false);
            $em->persist($msg);
            $em->flush();
            $this->addFlash('success', 'Votre message a bien été envoyé.');
        }
        return $this->redirectToRoute('app_user_dashboard');
    }

    #[Route('/admin/messages', name: 'admin_messages')]
    #[IsGranted('ROLE_ADMIN')]
    public function adminIndex(EntityManagerInterface $em): Response
    {
        $messages = $em->getRepository(Message::class)->findBy([], ['sent_at' => 'DESC']);
        $users = $em->getRepository(\App\Entity\User::class)->findBy([], ['lastName' => 'ASC', 'firstName' => 'ASC']);
        return $this->render('message/admin_index.html.twig', [
            'messages' => $messages,
            'users' => $users,
        ]);
    }

    #[Route('/admin/message/send', name: 'admin_message_send', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminSend(Request $request, EntityManagerInterface $em): RedirectResponse
    {
        $userId = $request->request->get('user_id');
        $subject = $request->request->get('subject');
        $content = $request->request->get('content');
        $user = $em->getRepository(\App\Entity\User::class)->find($userId);
        if ($user && $subject && $content) {
            $msg = new Message();
            $msg->setUser($user);
            $msg->setSubject($subject);
            $msg->setContent($content);
            $msg->setSentAt(new \DateTime());
            $msg->setIsRead(false);
            $em->persist($msg);
            $em->flush();
            $this->addFlash('success', 'Message envoyé à l\'utilisateur.');
        } else {
            $this->addFlash('danger', 'Utilisateur non trouvé ou champs manquants.');
        }
        return $this->redirectToRoute('admin_messages');
    }

    #[Route('/admin/message/{id}', name: 'admin_message_show', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminShow(int $id, EntityManagerInterface $em): Response
    {
        $message = $em->getRepository(Message::class)->find($id);
        if (!$message) {
            throw $this->createNotFoundException('Message non trouvé.');
        }
        return $this->render('message/index.html.twig', [
            'message' => $message,
        ]);
    }
}
