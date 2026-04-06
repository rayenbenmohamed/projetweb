<?php

namespace App\Form;

use App\Entity\Contract;
use App\Entity\JobOffre;
use App\Entity\TypeContrat;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContractType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('startDate', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de début',
            ])
            ->add('endDate', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de fin',
                'required' => false,
            ])
            ->add('salary', NumberType::class, [
                'label' => 'Salaire Brut',
            ])
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'En Attente' => 'En Attente',
                    'Actif' => 'Actif',
                    'Expiré' => 'Expire',
                    'Annulé' => 'Annule',
                ],
                'label' => 'Statut',
            ])
            ->add('typeContrat', EntityType::class, [
                'class' => TypeContrat::class,
                'choice_label' => 'name',
                'label' => 'Type de contrat',
            ])
            ->add('candidate', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'email',
                'label' => 'Candidat',
            ])
            ->add('recruiter', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'email',
                'label' => 'Recruteur',
                'required' => false,
            ])
            ->add('jobOffre', EntityType::class, [
                'class' => JobOffre::class,
                'choice_label' => 'title',
                'label' => 'Offre d\'emploi',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Contract::class,
        ]);
    }
}
