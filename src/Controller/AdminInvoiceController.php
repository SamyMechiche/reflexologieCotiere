<?php

namespace App\Controller;

use App\Entity\Invoice;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[Route('/admin/invoice')]
#[IsGranted('ROLE_ADMIN')]
class AdminInvoiceController extends AbstractController
{
    #[Route('/upload', name: 'admin_invoice_upload', methods: ['GET', 'POST'])]
    public function upload(Request $request, EntityManagerInterface $em, UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();
        $error = null;
        $success = null;

        if ($request->isMethod('POST')) {
            $userId = $request->request->get('user_id');
            /** @var UploadedFile|null $pdfFile */
            $pdfFile = $request->files->get('pdf_file');

            if ($userId && $pdfFile && $pdfFile->getClientOriginalExtension() === 'pdf') {
                $user = $userRepository->find($userId);
                if ($user) {
                    $filename = uniqid('facture_') . '.' . $pdfFile->getClientOriginalExtension();
                    $pdfFile->move($this->getParameter('kernel.project_dir') . '/public/invoices', $filename);

                    $invoice = new Invoice();
                    $invoice->setUser($user);
                    $invoice->setPdfFilename($filename);

                    $em->persist($invoice);
                    $em->flush();

                    $success = 'Facture générée et associée à l\'utilisateur.';
                } else {
                    $error = 'Utilisateur introuvable.';
                }
            } else {
                $error = 'Veuillez séléctionner un utilisateur et un fichier valide.';
            }
        }

        return $this->render('admin/invoice_upload.html.twig', [
            'users' => $users,
            'error' => $error,
            'success' => $success,
        ]);
    }
} 