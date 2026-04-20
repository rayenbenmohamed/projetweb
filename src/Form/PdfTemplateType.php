<?php

namespace App\Form;

use App\Entity\PdfTemplate;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class PdfTemplateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du modèle',
                'attr' => ['placeholder' => 'Ex: Contrat Freelance Premium']
            ])
            ->add('primaryColor', ColorType::class, [
                'label' => 'Couleur Primaire',
            ])
            ->add('secondaryColor', ColorType::class, [
                'label' => 'Couleur Secondaire',
            ])
            ->add('logoFile', FileType::class, [
                'label' => 'Logo (PNG/JPG)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Veuillez uploader une image valide (JPG, PNG)',
                    ])
                ],
            ])
            ->add('headerHtml', TextareaType::class, [
                'label' => 'HTML de l\'en-tête',
                'required' => false,
                'attr' => ['rows' => 4, 'placeholder' => '<div style="text-align: right;">...</div>']
            ])
            ->add('bodyHtml', TextareaType::class, [
                'label' => 'HTML du corps (Placeholders autorisés)',
                'required' => false,
                'attr' => ['rows' => 15]
            ])
            ->add('footerHtml', TextareaType::class, [
                'label' => 'HTML du pied de page',
                'required' => false,
                'attr' => ['rows' => 4]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PdfTemplate::class,
        ]);
    }
}
