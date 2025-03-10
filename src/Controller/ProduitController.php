<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Entity\Categorie;
use App\Entity\Avoir;
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
        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Persiste le produit
                $em->persist($produit);
                $em->flush();

                // Récupère les données du formulaire
                $taille = $form->get('taille')->getData();
                $prix = $form->get('prix')->getData();

                // Crée et persiste l'Avoir
                $avoir = new Avoir();
                $avoir->setProduit($produit);
                $avoir->setTaille($taille);
                $avoir->setPrix($prix);

                $em->persist($avoir);
                $em->flush();

                $this->addFlash('success', 'Le produit a été enregistré avec succès !');
                return $this->redirectToRoute('produit');
            } catch (\Exception $e) {
                $this->addFlash('error', "Erreur lors de l'enregistrement : " . $e->getMessage());
            }
        } else {
            foreach ($form->getErrors(true) as $error) {
                $this->addFlash('error', $error->getMessage());
            }
        }

        return $this->render('produit/new.html.twig', [
            'form' => $form->createView(),
            'categories' => $categorieRepository->findAll(),
        ]);
    }

    #[Route('/api/sous-categories/{categorieId}', name: 'api_sous_categories')]
    public function getSousCategories($categorieId, CategorieRepository $categorieRepository): JsonResponse
    {
        $categorie = $categorieRepository->find($categorieId);
        if (!$categorie) {
            return $this->json([], 200); // Retourner un tableau vide plutôt qu'une erreur
        }
        
        $sousCategories = [];
        foreach ($categorie->getSousCategories() as $sousCategorie) {
            $sousCategories[] = [
                'id' => $sousCategorie->getId(),
                'libelle' => $sousCategorie->getLibelle()
            ];
        }
        
        return $this->json($sousCategories);
    }



}