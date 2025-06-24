<?php

namespace App\Form;

use App\Entity\Appointment;
use App\Entity\AvailabilitySlots;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AvailabilitySlotsForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date')
            ->add('start_time')
            ->add('end_time')
            ->add('is_booked')
            ->add('created_at')
            ->add('appointment', EntityType::class, [
                'class' => Appointment::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AvailabilitySlots::class,
        ]);
    }
}
