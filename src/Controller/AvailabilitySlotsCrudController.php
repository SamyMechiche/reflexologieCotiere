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
use Carbon\Carbon;

#[Route('/admin/availability/slots/crud')]
final class AvailabilitySlotsCrudController extends AbstractController
{
    #[Route(name: 'app_availability_slots_crud_index', methods: ['GET'])]
    public function index(Request $request, AvailabilitySlotsRepository $availabilitySlotsRepository): Response
    {
        $weekOffset = $request->query->getInt('week', 0);
        $startOfWeek = (new Carbon())->addWeeks($weekOffset)->startOfWeek();
        $endOfWeek = $startOfWeek->copy()->endOfWeek();

        $existingSlots = $availabilitySlotsRepository->findBetweenDates($startOfWeek, $endOfWeek);
        $slotsByDay = $this->getSlotsByDay($startOfWeek, $existingSlots);

        return $this->render('availability_slots_crud/index.html.twig', [
            'slotsByDay' => $slotsByDay,
            'currentWeek' => $startOfWeek->format('d/m/Y') . ' - ' . $endOfWeek->format('d/m/Y'),
            'prevWeek' => $weekOffset - 1,
            'nextWeek' => $weekOffset + 1,
        ]);
    }

    #[Route('/add', name: 'app_availability_slots_crud_add', methods: ['POST'])]
    public function add(Request $request, EntityManagerInterface $entityManager): Response
    {
        $slotTime = $request->request->get('slot');
        $dateTime = Carbon::parse($slotTime);

        $availabilitySlot = new AvailabilitySlots();
        $availabilitySlot->setDate(\DateTime::createFromFormat('Y-m-d', $dateTime->format('Y-m-d')));
        $availabilitySlot->setStartTime(\DateTime::createFromFormat('H:i', $dateTime->format('H:i')));
        $availabilitySlot->setEndTime(\DateTime::createFromFormat('H:i', $dateTime->copy()->addHour()->format('H:i')));
        $availabilitySlot->setIsBooked(false);
        $availabilitySlot->setCreatedAt(new \DateTime());

        $entityManager->persist($availabilitySlot);
        $entityManager->flush();

        $weekOffset = $request->query->getInt('week', 0);
        return $this->redirectToRoute('app_availability_slots_crud_index', ['week' => $weekOffset]);
    }

    private function getSlotsByDay(Carbon $startOfWeek, array $existingSlots): array
    {
        $slotsByDay = [];
        for ($i = 0; $i < 7; $i++) {
            $day = $startOfWeek->copy()->addDays($i);
            $slotsByDay[$day->format('Y-m-d')] = [
                'dayName' => $day->format('l'),
                'slots' => [],
            ];

            for ($hour = 8; $hour < 19; $hour++) {
                $slotTime = $day->copy()->hour($hour)->minute(0)->second(0);
                $isAvailable = false;
                $slotId = null;

                foreach ($existingSlots as $existingSlot) {
                    if (
                        $existingSlot->getDate()->format('Y-m-d') === $slotTime->format('Y-m-d') &&
                        $existingSlot->getStartTime()->format('H:i') === $slotTime->format('H:i')
                    ) {
                        $isAvailable = true;
                        $slotId = $existingSlot->getId();
                        break;
                    }
                }

                $slotsByDay[$day->format('Y-m-d')]['slots'][] = [
                    'time' => $slotTime->format('H:i'),
                    'full_datetime' => $slotTime->toDateTimeString(),
                    'is_available' => $isAvailable,
                    'id' => $slotId,
                ];
            }
        }
        return $slotsByDay;
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
