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
    public function index(CommandeRepository $commandeRepository, StatutRepository $statutRepository, Request $request): Response
    {
        $statuts = $statutRepository->findAll(); 
        $statutId = $request->query->get('statut');

        // Trie des commandes directement dans la requête du contrôleur
        $commandes = $statutId ? 
            $commandeRepository->findBy(['Statut' => $statutId], ['id' => 'DESC']) :
            $commandeRepository->findBy([], ['id' => 'DESC']);

        // Vérification si la requête est AJAX
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

    #[Route('/commande/{id}/update-statut', name: 'update_statut', methods: ['POST'])]
    public function updateStatut(Request $request, Commande $commande, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $statutId = (int) $data['statutId'];

        $statut = $em->getRepository(Statut::class)->find($statutId);
        if (!$statut) {
            return $this->json(['success' => false, 'message' => 'Statut non trouvé']);
        }

        $commande->setStatut($statut);

        // Ajouter l'utilisateur connecté comme responsable
        $user = $this->getUser(); // Récupération de l'utilisateur actuellement connecté
        if ($user) {
            $commande->setResponsable($user);
        }

        $em->flush();

        return $this->json(['success' => true]);
    }

    #[Route('/commande/details/{id}', name: 'commande_details', methods: ['GET'])]
    public function getDetails(Commande $commande, StatutRepository $statutRepository): JsonResponse
    {
        // Vérifier que la commande existe
        if (!$commande) {
            return $this->json(['error' => 'Commande non trouvée'], 404);
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

        return $this->json([
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
    }
}

