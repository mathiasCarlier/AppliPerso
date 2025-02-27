<?php

namespace App\Repository;

use App\Entity\Produit;
use App\Entity\Categorie;
use App\Entity\SousCategorie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Produit>
 */
class ProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Produit::class);
    }

    public function findAllWithTaille(): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.avoirs', 'a')
            ->leftJoin('a.Taille', 't')
            ->addSelect('a', 't')
            ->getQuery()
            ->getResult();
    }

    public function findByCategorie(int $categorieId): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.avoirs', 'a')
            ->leftJoin('a.Taille', 't')
            ->leftJoin('p.Categorie', 'c')
            ->andWhere('c.id = :categorieId')
            ->setParameter('categorieId', $categorieId)
            ->addSelect('a', 't')
            ->getQuery()
            ->getResult();
    }


    public function findByFilters(?int $categorieId, ?int $sousCategorieId): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.Categorie', 'c') // 'Categorie' est le nom de la propriété dans Produit
            ->leftJoin('p.sous_categorie', 'sc'); // 'sous_categorie' est le nom de la propriété dans Produit

        if ($categorieId) {
            $qb->andWhere('c.id = :categorieId')
            ->setParameter('categorieId', $categorieId);
        }

        if ($sousCategorieId) {
            $qb->andWhere('sc.id = :sousCategorieId')
            ->setParameter('sousCategorieId', $sousCategorieId);
        }

        return $qb->getQuery()->getResult();
    }

    public function findByCategorieSorted(?int $categorieId, string $sortField, string $sortOrder): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.avoirs', 'a')
            ->leftJoin('a.Taille', 't')
            ->leftJoin('p.Categorie', 'c')
            ->addSelect('a', 't');

        if ($categorieId) {
            $qb->andWhere('c.id = :categorieId')
                ->setParameter('categorieId', $categorieId);
        }

        switch ($sortField) {
            case 'price':
                $qb->orderBy('a.prix', $sortOrder);
                break;
            case 'name':
                $qb->orderBy('p.nom', $sortOrder);
                break;
            default:
                $qb->orderBy('c.libelle', 'ASC'); // Tri par catégorie par défaut
                break;
        }

        return $qb->getQuery()->getResult();
    }

    public function findByFiltersAndSort(?int $categorieId, string $sort): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.Categorie', 'c')
            ->leftJoin('p.avoirs', 'a')
            ->leftJoin('a.Taille', 't')
            ->addSelect('c', 'a', 't');

        if ($categorieId) {
            $qb->andWhere('c.id = :categorieId')
                ->setParameter('categorieId', $categorieId);
        }

        switch ($sort) {
            case 'price_asc':
                $qb->orderBy('a.prix', 'ASC');
                break;
            case 'price_desc':
                $qb->orderBy('a.prix', 'DESC');
                break;
            case 'name_asc':
                $qb->orderBy('p.nom', 'ASC');
                break;
            case 'name_desc':
                $qb->orderBy('p.nom', 'DESC');
                break;
            default:
                $qb->orderBy('c.libelle', 'ASC'); // Tri par défaut
        }

        return $qb->getQuery()->getResult();
    }



    //    /**
    //     * @return Produit[] Returns an array of Produit objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Produit
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
