<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class ProductReportController extends AbstractController
{
    /**
     * @Route("/report", name="product_report")
     */
    public function index(EntityManagerInterface $em)
    {
        // On utilise le QueryBuilder pour regrouper par produit et taille
        $qb = $em->createQueryBuilder();
        $qb->select('p.nom AS productName, t.unite AS taille, SUM(lc.quantite) AS totalQuantity, SUM(lc.quantite * lc.prix) AS totalPrice')
           ->from('App\Entity\LigneCommande', 'lc')
           ->join('lc.produit', 'p')
           ->join('lc.taille', 't')
           ->groupBy('p.id, t.id');
        
        $result = $qb->getQuery()->getResult();

        // Calcul du prix total global
        $globalTotal = 0;
        foreach ($result as $row) {
            $globalTotal += $row['totalPrice'];
        }

        return $this->render('report/index.html.twig', [
            'products' => $result,
            'globalTotal' => $globalTotal,
        ]);
    }
}
