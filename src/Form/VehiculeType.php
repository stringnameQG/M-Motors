<?php

namespace App\Form;

use App\Entity\Vehicule;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class VehiculeType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('photosFiles', FileType::class, [
                'multiple' => true,
                'mapped' => false,
                'required' => false,
                'label' => 'Photos (JPEG/PNG/WebP, max 5Mo)',
                'attr'     => [
                    'accept' => 'image/*',
                    'multiple' => 'multiple'
                ]
            ])
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'vente' => 'vente',
                    'location' => 'location'
                ],
                'label' => 'type',
            ])
            ->add('vin')
            ->add('immatriculation')
            ->add('marque')
            ->add('modele')
            ->add('version')
            ->add('dateMiseEnCirculation')
            ->add('energie')
            ->add('boiteVitesse')
            ->add('puissanceFiscale')
            ->add('kilometrage')
            ->add('couleur')
            ->add('nombrePortes')
            ->add('nombrePlaces')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Vehicule::class,
        ]);
    }
}
