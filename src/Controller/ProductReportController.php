<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Contrôleur gérant les rapports de ventes des produits
 * 
 * Ce contrôleur permet de :
 * - Générer des rapports de ventes par période
 * - Calculer les statistiques de ventes par produit
 * - Afficher les totaux par taille de produit
 * - Calculer le chiffre d'affaires global
 * 
 * Périodes disponibles :
 * - Aujourd'hui (today)
 * - Cette semaine (week)
 * - Ce mois (month)
 * - Cette année (year)
 */
class ProductReportController extends AbstractController
{
    /**
     * Génère et affiche le rapport des ventes de produits
     * 
     * Cette méthode :
     * - Détermine la période d'analyse selon le paramètre fourni
     * - Calcule les statistiques de ventes pour chaque produit
     * - Agrège les données par taille de produit
     * - Calcule le chiffre d'affaires total
     * 
     * Les données calculées incluent :
     * - Nom du produit
     * - Taille du produit
     * - Quantité totale vendue
     * - Chiffre d'affaires par produit
     * - Chiffre d'affaires global
     * 
     * Note : Seules les commandes avec le statut 4 (livrées) sont prises en compte
     * 
     * @param EntityManagerInterface $em Gestionnaire d'entités Doctrine
     * @param Request $request Requête HTTP contenant la période demandée
     * @return Response Vue du rapport avec les statistiques
     */
    #[Route("/report", name: "product_report")]
    public function index(EntityManagerInterface $em, Request $request): Response
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
