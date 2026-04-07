<?php

namespace App\Form;

use App\Entity\Cv;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CvType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('fullName', TextType::class, [
                'label' => 'Nom complet',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Jean Dupont']
            ])
            ->add('email', EmailType::class, [
                'label' => 'Adresse email',
                'attr' => ['class' => 'form-control', 'placeholder' => 'jean.dupont@example.com']
            ])
            ->add('phone', TelType::class, [
                'label' => 'Téléphone',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => '+216 -- --- ---']
            ])
            ->add('address', TextType::class, [
                'label' => 'Adresse',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Tunis, Tunisie']
            ])
            ->add('birthDate', DateType::class, [
                'label' => 'Date de naissance',
                'widget' => 'single_text',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('title', TextType::class, [
                'label' => 'Titre professionnel',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Développeur Web Fullstack']
            ])
            ->add('summary', TextareaType::class, [
                'label' => 'Résumé',
                'attr' => ['class' => 'form-control', 'rows' => 3, 'placeholder' => 'En quelques phrases...']
            ])
            ->add('education', TextareaType::class, [
                'label' => 'Formation',
                'attr' => ['class' => 'form-control', 'rows' => 4]
            ])
            ->add('experience', TextareaType::class, [
                'label' => 'Expérience professionnelle',
                'attr' => ['class' => 'form-control', 'rows' => 4]
            ])
            ->add('skills', TextareaType::class, [
                'label' => 'Compétences',
                'attr' => ['class' => 'form-control', 'rows' => 3]
            ])
            ->add('isPublic', CheckboxType::class, [
                'label' => 'Rendre ce CV public',
                'required' => false,
                'attr' => ['class' => 'form-check-input']
            ])
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'email',
                'label' => 'Utilisateur',
                'attr' => ['class' => 'form-select select2']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver.setDefaults([
            'data_class' => Cv::class,
        ]);
    }
}
