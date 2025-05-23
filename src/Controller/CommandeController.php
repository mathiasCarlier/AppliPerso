<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\Statut;
use App\Repository\CommandeRepository;
use App\Repository\StatutRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Attribute\IsGranted;

/**
 * Contrôleur gérant toutes les opérations liées aux commandes
 * 
 * Ce contrôleur permet de :
 * - Afficher la liste des commandes
 * - Filtrer les commandes par statut
 * - Mettre à jour le statut d'une commande
 * - Consulter les détails d'une commande
 */
final class CommandeController extends AbstractController
{
    /**
     * Affiche la page principale des commandes avec possibilité de filtrage par statut
     * 
     * @param CommandeRepository $commandeRepository Repository pour accéder aux commandes
     * @param StatutRepository $statutRepository Repository pour accéder aux statuts
     * @param Request $request Requête HTTP
     * @return Response Page des commandes avec filtres
     */
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/commande', name: 'commande')]
    public function index(
        CommandeRepository $commandeRepository,
        StatutRepository $statutRepository,
        Request $request
    ): Response {
        $statuts = $statutRepository->findAll();
        $statutId = $request->query->get('statut');

        $commandes = $statutId ?
            $commandeRepository->findBy(['Statut' => $statutId], ['id' => 'DESC']) :
            $commandeRepository->findBy([], ['id' => 'DESC']);

        usort($commandes, function ($a, $b) {
            $statutA = $a->getStatut()->getId();
            $statutB = $b->getStatut()->getId();

            //trier par statut croissant
            return $a->getId() <=> $b->getId();
        });
            
        

        if ($request->isXmlHttpRequest()) {
            return $this->render('commande/index.html.twig', [
                'commandes' => $commandes,
                'statuts' => $statuts,
                'statutId' => $statutId
            ]);
        }
        

        return $this->render('commande/index.html.twig', [
            'commandes' => $commandes,
            'statuts' => $statuts,
            'statutId' => $statutId
        ]);
    }

    /**
     * Affiche une vue alternative des commandes (utilisée pour les mises à jour AJAX)
     * 
     * @param CommandeRepository $commandeRepository Repository pour accéder aux commandes
     * @param StatutRepository $statutRepository Repository pour accéder aux statuts
     * @param Request $request Requête HTTP
     * @return Response Vue alternative des commandes
     */
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/commande/other', name: 'commande_other')]
    public function other(
        CommandeRepository $commandeRepository,
        StatutRepository $statutRepository,
        Request $request
    ): Response {
        $statuts = $statutRepository->findAll();
        $statutId = $request->query->get('statut');

        $commandes = $statutId ?
            $commandeRepository->findBy(['Statut' => $statutId], ['id' => 'DESC']) :
            $commandeRepository->findBy([], ['id' => 'DESC']);

        usort($commandes, function ($a, $b) {
            $statutA = $a->getStatut()->getId();
            $statutB = $b->getStatut()->getId();

            //trier par statut croissant
            return $a->getId() <=> $b->getId();
        });
            
        

        if ($request->isXmlHttpRequest()) {
            return $this->render('commande/_tableau_commande.html.twig', [
                'commandes' => $commandes,
                'statuts' => $statuts,
                'statutId' => $statutId
            ]);
        }

        return $this->render('commande/other.html.twig', [
            'commandes' => $commandes,
            'statuts' => $statuts,
            'statutId' => $statutId
        ]);
    }

    /**
     * Met à jour le statut d'une commande via une requête AJAX
     * 
     * @param Request $request Requête HTTP contenant le nouveau statut
     * @param Commande $commande La commande à mettre à jour
     * @param EntityManagerInterface $em Gestionnaire d'entités Doctrine
     * @return JsonResponse Réponse JSON avec le résultat de la mise à jour
     */
    #[Route('/commande/{id}/update-statut', name: 'update_statut', methods: ['POST'])]
    public function updateStatut(
        Request $request,
        Commande $commande,
        EntityManagerInterface $em
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $statutId = (int) ($data['statutId'] ?? 0);

        $statut = $em->getRepository(Statut::class)->find($statutId);
        if (!$statut) {
            return $this->json([
                'success' => false,
                'message' => 'Statut non trouvé'
            ], 404);
        }

        $commande
            ->setStatut($statut)
            ->setResponsable($this->getUser());

        $em->flush();

        return $this->json([
            'success' => true,
            'newLibelle' => $statut->getLibelle()
        ], 200, [
            'Cache-Control' => 'no-store, max-age=0'
        ]);
    }

    /**
     * Récupère les détails d'une commande spécifique
     * 
     * Retourne toutes les informations détaillées d'une commande incluant :
     * - Informations générales (numéro, date, prix total)
     * - Informations du client
     * - Liste des produits commandés
     * - Statut actuel et liste des statuts possibles
     * 
     * @param Commande $commande La commande dont on veut les détails
     * @param StatutRepository $statutRepository Repository pour accéder aux statuts
     * @return JsonResponse Détails de la commande au format JSON
     */
    #[Route('/commande/details/{id}', name: 'commande_details', methods: ['GET'])]
    public function getDetails(
        Commande $commande,
        StatutRepository $statutRepository
    ): JsonResponse {
        if (!$commande) {
            return $this->json([
                'error' => 'Commande non trouvée'
            ], 404);
        }

        $ligneCommandes = [];
        $totalProduits = 0; // Initialiser la variable pour compter les produits

        foreach ($commande->getLigneCommandes() as $ligne) {
            $produit = $ligne->getProduit();
            $taille = $ligne->getTaille();
            
            $ligneCommandes[] = [
                'produit' => [
                    'nom' => $produit ? $produit->getNom() : 'Produit supprimé',
                ],
                'taille' => $taille ? [
                    'unite' => $taille->getUnite()
                ] : null,
                'quantite' => $ligne->getQuantite()
            ];

        }

        $nombreTotalProduits = 0;
        foreach ($commande->getLigneCommandes() as $ligne) {
            $nombreTotalProduits += $ligne->getQuantite();
        }

        $response = $this->json([
            'id' => $commande->getId(),
            'numeroCommande' => $commande->getNumeroCommande(),
            'heure' => $commande->getHeure()->format('Y-m-d H:i:s'),
            'prixTotal' => $commande->getPrixTotal(),
            'nombreTotalProduits' => $nombreTotalProduits,
            'client' => [
                'nom' => $commande->getClient()->getNom(),
                'prenom' => $commande->getClient()->getPrenom()
            ],
            'statut' => [
                'id' => $commande->getStatut()->getId(),
                'libelle' => $commande->getStatut()->getLibelle()
            ],
            'statuts' => array_map(function($statut) {
                return [
                    'id' => $statut->getId(),
                    'libelle' => $statut->getLibelle()
                ];
            }, $statutRepository->findAll()),
            'ligneCommandes' => $ligneCommandes,
        ]);

        $response->headers->set('Cache-Control', 'no-store, max-age=0');
        return $response;
    }

}