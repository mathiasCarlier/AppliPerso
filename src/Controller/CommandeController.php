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

final class CommandeController extends AbstractController
{
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
            return $this->render('commande/_tableau_commande.html.twig', [
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

        $response = $this->json([
            'id' => $commande->getId(),
            'numeroCommande' => $commande->getNumeroCommande(),
            'heure' => $commande->getHeure()->format('Y-m-d H:i:s'),
            'prixTotal' => $commande->getPrixTotal(),
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
            'ligneCommandes' => $ligneCommandes
        ]);

        $response->headers->set('Cache-Control', 'no-store, max-age=0');
        return $response;
    }
}