<?php

namespace App\Form;

use App\Entity\Dossier;
use App\Entity\User;
use App\Entity\Vehicule;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class DossierType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('vehicule', EntityType::class, [
                'class' => Vehicule::class,
                'choice_label' => 'id',
            ])
            ->add('documentFiles', FileType::class, [
                'multiple' => true,
                'mapped' => false,
                'required' => false,
                'label' => 'Documents (JPEG/PNG/WebP/PDF, max 5Mo)',
                'attr'     => [
                    'accept' => 'image/*', 'application/pdf',
                    'multiple' => 'multiple'
                ]
            ]);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Dossier::class,
        ]);
    }
}
