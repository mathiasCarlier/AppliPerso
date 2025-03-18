<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class ProductReportController extends AbstractController
{
    /**
     * @Route("/report", name="product_report")
     */
    public function index(EntityManagerInterface $em, Request $request)
    {
        // Récupération de la période choisie dans l'URL (par défaut "today")
        $periode = $request->query->get('periode', 'today');
        $now = new \DateTime();
        
        // Détermination de la date de début selon la période
        switch ($periode) {
            case 'week':
                // Retourne le lundi de la semaine en cours
                $startDate = new \DateTime('monday this week');
                break;
            case 'month':
                // Retourne le premier jour du mois en cours
                $startDate = new \DateTime('first day of this month');
                break;
            case 'year':
                // Retourne le premier jour de l'année en cours
                $startDate = new \DateTime('first day of January ' . $now->format('Y'));
                break;
            case 'today':
            default:
                // Retourne le début de la journée en cours
                $startDate = new \DateTime('today');
                break;
        }

        // Construction de la requête avec le filtre sur le statut et la date
        $qb = $em->createQueryBuilder();
        $qb->select('p.nom AS productName, t.unite AS taille, SUM(lc.quantite) AS totalQuantity, SUM(lc.quantite * lc.prix) AS totalPrice')
           ->from('App\Entity\LigneCommande', 'lc')
           ->join('lc.Produit', 'p')
           ->join('lc.Taille', 't')
           ->join('lc.Commande', 'c') // Jointure avec Commande
           ->where('c.Statut = :Statut')
           ->andWhere('c.date >= :startDate')
           ->setParameter('Statut', 4)
           ->setParameter('startDate', $startDate)
           ->groupBy('p.id, t.id');

        $result = $qb->getQuery()->getResult();

        // Calcul du total global
        $globalTotal = 0;
        foreach ($result as $row) {
            $globalTotal += $row['totalPrice'];
        }

        return $this->render('report/index.html.twig', [
            'products'    => $result,
            'globalTotal' => $globalTotal,
            'periode'     => $periode, // Pour conserver la sélection dans le formulaire
        ]);
    }
}
