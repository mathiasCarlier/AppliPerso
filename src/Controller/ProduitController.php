<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Entity\Categorie;
use App\Form\ProduitType;
use App\Repository\ProduitRepository;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProduitController extends AbstractController
{
    #[Route('/produit', name: 'produit')]
    public function index(ProduitRepository $produitRepository,CategorieRepository $categorieRepository,Request $request): Response {
        // Récupérer l'ID de la catégorie depuis la requête
        $categorieId = $request->query->get('categorie');

        // Récupérer tous les produits (filtrés par catégorie si nécessaire)
        $produits = $categorieId
            ? $produitRepository->findByCategorie($categorieId)
            : $produitRepository->findAllWithTaille();

        // Récupérer toutes les catégories pour la liste déroulante
        $categories = $categorieRepository->findAll();

        return $this->render('produit/index.html.twig', [
            'produits' => $produits,
            'categories' => $categories,
        ]);
    }

    #[Route('/produit/{id}/update-en-ligne', name: 'update_en_ligne', methods: ['POST'])]
    public function updateEnLigne(Request $request, Produit $produit, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $enLigne = (bool) $data['enLigne'];

        $produit->setEnLigne($enLigne);
        $em->flush();

        return $this->json(['success' => true]);
    }

    #[Route('/produit/new', name: 'produit_new')]
    public function new(Request $request, EntityManagerInterface $em, CategorieRepository $categorieRepository): Response
    {
        // Créer une nouvelle instance de Produit
        $produit = new Produit();

        // Créer le formulaire
        $form = $this->createForm(ProduitType::class, $produit);

        // Récupérer toutes les catégories pour les passer au template
        $categories = $categorieRepository->findAll();

        // Gérer la soumission du formulaire
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Enregistrer le produit en base de données
            $em->persist($produit);
            $em->flush();

            // Rediriger vers une autre page (par exemple, la liste des produits)
            return $this->redirectToRoute('produit_list');
        }

        // Afficher le formulaire avec les catégories
        return $this->render('produit/new.html.twig', [
            'form' => $form->createView(),
            'categories' => $categories, // Passer les catégories au template
        ]);
    }
}