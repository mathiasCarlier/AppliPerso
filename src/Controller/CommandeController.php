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
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\SecurityBundle\Attribute\IsGranted;

final class CommandeController extends AbstractController
{
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/commande', name: 'commande')]
    public function index(CommandeRepository $commandeRepository, StatutRepository $statutRepository, Request $request): Response
    {
        $statuts = $statutRepository->findAll(); //where commande_affichage = true
        $statutId = $request->query->get('statut');

        $commandes = $statutId ? $commandeRepository->findBy(['Statut' => $statutId]) : $commandeRepository->findAll();

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


    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/commande/update-statut', name: 'update_statut', methods: ['POST'])]
    public function updateStatut(Request $request, CommandeRepository $commandeRepository, StatutRepository $statutRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $commandeId = $request->request->get('commandeId');
        $statutId = $request->request->get('statutId');

        $commande = $commandeRepository->find($commandeId);
        $statut = $statutRepository->find($statutId);

        if (!$commande || !$statut) {
            return new JsonResponse(['success' => false, 'message' => 'Commande ou Statut introuvable'], 400);
        }

        $commande->setStatut($statut);
        $entityManager->persist($commande);
        $entityManager->flush();

        return new JsonResponse(['success' => true]);
    }

    
}
