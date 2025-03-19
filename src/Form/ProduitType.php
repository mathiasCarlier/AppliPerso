<?php

namespace App\Form;

use App\Entity\Categorie;
use App\Entity\Produit;
use App\Entity\Taille;
use App\Entity\SousCategorie;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;

class ProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('en_ligne')
            ->add('ref_produit', FileType::class, [
                'label' => 'Image (PNG ou JPEG)',
                'mapped' => false, // Ne pas lier directement à l'entité
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => ['image/jpeg', 'image/png'],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPEG ou PNG)',
                    ])
                ],
            ])

            ->add('Categorie', EntityType::class, [
                'class' => Categorie::class,
                'choice_label' => 'libelle',
                'placeholder' => 'Sélectionnez une catégorie',
            ])
            ->add('sous_categorie', EntityType::class, [
                'class' => SousCategorie::class,
                'required' => false,
                'choice_label' => 'libelle',
                'placeholder' => 'Sélectionnez une sous-catégorie',
            ])

            ->add('taille', EntityType::class, [
                'class' => Taille::class,
                'choice_label' => 'unite',
                'placeholder' => 'Sélectionnez une taille',
                'mapped' => false,
                'required' => true,
            ])
            ->add('prix', NumberType::class, [
                'mapped' => false,
                'required' => true,
                'label' => 'Prix'
            ]);

            
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Produit::class,
        ]);
    }
}