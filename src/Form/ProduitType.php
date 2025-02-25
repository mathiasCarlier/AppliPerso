<?php

namespace App\Form;

use App\Entity\Categorie;
use App\Entity\Produit;
use App\Entity\SousCategorie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('description')
            ->add('en_ligne')
            ->add('est_remise')
            ->add('est_menu')
            ->add('valeur')
            ->add('ref_produit')
            // src/Form/ProduitType.php

            ->add('Categorie', EntityType::class, [
                'class' => Categorie::class,
                'choice_label' => 'libelle',
                'placeholder' => 'Sélectionnez une catégorie',
            ])
            ->add('sous_categorie', EntityType::class, [
                'class' => SousCategorie::class,
                'choice_label' => 'libelle',
                'placeholder' => 'Sélectionnez une sous-catégorie',
                'choices' => [], // Initialement vide, sera rempli via JavaScript
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Produit::class,
        ]);
    }
}