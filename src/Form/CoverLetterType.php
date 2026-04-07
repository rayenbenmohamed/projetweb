<?php

namespace App\Form;

use App\Entity\CoverLetter;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CoverLetterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('companyName', TextType::class, [
                'label' => 'Nom de l\'entreprise',
                'attr' => ['class' => 'form-control']
            ])
            ->add('position', TextType::class, [
                'label' => 'Poste ciblé',
                'attr' => ['class' => 'form-control']
            ])
            ->add('recipientName', TextType::class, [
                'label' => 'Nom du destinataire',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('recipientTitle', TextType::class, [
                'label' => 'Titre du destinataire',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('companyAddress', TextareaType::class, [
                'label' => 'Adresse de l\'entreprise',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 2]
            ])
            ->add('letterContent', TextareaType::class, [
                'label' => 'Contenu de la lettre',
                'attr' => ['class' => 'form-control', 'rows' => 10]
            ])
            ->add('isPublic', CheckboxType::class, [
                'label' => 'Rendre publique',
                'required' => false,
                'attr' => ['class' => 'form-check-input']
            ])
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'email',
                'label' => 'Utilisateur',
                'attr' => ['class' => 'form-select']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver.setDefaults([
            'data_class' => CoverLetter::class,
        ]);
    }
}
