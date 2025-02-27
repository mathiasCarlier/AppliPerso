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


    
}
