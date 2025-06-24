<?php

namespace App\Controller;

use App\Entity\AvailabilitySlots;
use App\Form\AvailabilitySlotsForm;
use App\Repository\AvailabilitySlotsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/availability/slots/crud')]
final class AvailabilitySlotsCrudController extends AbstractController
{
    #[Route(name: 'app_availability_slots_crud_index', methods: ['GET'])]
    public function index(AvailabilitySlotsRepository $availabilitySlotsRepository): Response
    {
        return $this->render('availability_slots_crud/index.html.twig', [
            'availability_slots' => $availabilitySlotsRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_availability_slots_crud_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $availabilitySlot = new AvailabilitySlots();
        $form = $this->createForm(AvailabilitySlotsForm::class, $availabilitySlot);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($availabilitySlot);
            $entityManager->flush();

            return $this->redirectToRoute('app_availability_slots_crud_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('availability_slots_crud/new.html.twig', [
            'availability_slot' => $availabilitySlot,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_availability_slots_crud_show', methods: ['GET'])]
    public function show(AvailabilitySlots $availabilitySlot): Response
    {
        return $this->render('availability_slots_crud/show.html.twig', [
            'availability_slot' => $availabilitySlot,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_availability_slots_crud_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, AvailabilitySlots $availabilitySlot, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AvailabilitySlotsForm::class, $availabilitySlot);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_availability_slots_crud_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('availability_slots_crud/edit.html.twig', [
            'availability_slot' => $availabilitySlot,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_availability_slots_crud_delete', methods: ['POST'])]
    public function delete(Request $request, AvailabilitySlots $availabilitySlot, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$availabilitySlot->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($availabilitySlot);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_availability_slots_crud_index', [], Response::HTTP_SEE_OTHER);
    }
}
