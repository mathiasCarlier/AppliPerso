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
    public function index(ProduitRepository $produitRepository, CategorieRepository $categorieRepository, Request $request): Response
    {
        $categorieId = $request->query->get('categorie');
        $sort = $request->query->get('sort', 'category'); // Par défaut, tri par catégorie

        // Appliquer le filtre et le tri
        $produits = $produitRepository->findByFiltersAndSort($categorieId, $sort);

    
        // Déterminer le champ et l'ordre de tri
        $sortField = 'category';
        $sortOrder = 'ASC';
    
        switch ($sort) {
            case 'price_asc':
                $sortField = 'price';
                $sortOrder = 'ASC';
                break;
            case 'price_desc':
                $sortField = 'price';
                $sortOrder = 'DESC';
                break;
            case 'name_asc':
                $sortField = 'name';
                $sortOrder = 'ASC';
                break;
            case 'name_desc':
                $sortField = 'name';
                $sortOrder = 'DESC';
                break;
        }
    
        // Récupération des produits avec tri et filtre
        $produits = $produitRepository->findByCategorieSorted($categorieId, $sortField, $sortOrder);
    
        // Récupération des catégories pour le menu déroulant
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