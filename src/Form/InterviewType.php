<?php

namespace App\Form;

use App\Entity\Interview;
use App\Entity\JobApplication;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InterviewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('scheduledAt', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Date et Heure',
                'attr' => ['class' => 'form-control'],
                'required' => true,
            ])
            ->add('notes', TextareaType::class, [
                'label' => 'Notes / Préparation',
                'attr' => ['class' => 'form-control', 'rows' => 4],
                'required' => true,
            ])
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'Prévue' => 'Prévue',
                    'Confirmée' => 'Confirmée',
                    'Annulée' => 'Annulée',
                    'Réalisée' => 'Réalisée',
                ],
                'attr' => ['class' => 'form-select'],
                'label' => 'Statut de l\'entretien',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Interview::class,
        ]);
    }
}
