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
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Contrôleur gérant toutes les opérations liées aux produits du cinéma
 * 
 * Ce contrôleur permet de :
 * - Lister les produits avec filtrage et tri
 * - Créer de nouveaux produits
 * - Modifier l'état en ligne/hors ligne des produits
 * - Gérer les prix des produits
 * - Supprimer des produits
 * - Gérer les catégories et sous-catégories
 */
final class ProduitController extends AbstractController
{
    /**
     * Affiche la liste des produits avec options de filtrage et tri
     * 
     * @param ProduitRepository $produitRepository Repository pour accéder aux produits
     * @param CategorieRepository $categorieRepository Repository pour accéder aux catégories
     * @param Request $request Requête HTTP contenant les paramètres de filtrage et tri
     * @return Response Vue de la liste des produits
     */
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
            'produits'   => $produits,
            'categories' => $categories,
        ]);
    }

    /**
     * Met à jour le statut en ligne/hors ligne d'un produit via AJAX
     * 
     * @param Request $request Requête HTTP contenant le nouveau statut
     * @param Produit $produit Le produit à mettre à jour
     * @param EntityManagerInterface $em Gestionnaire d'entités Doctrine
     * @return JsonResponse Confirmation de la mise à jour
     */
    #[Route('/produit/{id}/update-en-ligne', name: 'update_en_ligne', methods: ['POST'])]
    public function updateEnLigne(Request $request, Produit $produit, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $enLigne = (bool) $data['enLigne'];

        $produit->setEnLigne($enLigne);
        $em->flush();

        return $this->json(['success' => true]);
    }

    /**
     * Affiche et traite le formulaire de création d'un nouveau produit
     * 
     * Cette méthode gère :
     * - L'upload de l'image du produit
     * - La création du produit avec ses attributs
     * - La gestion des menus et réductions
     * - L'association avec les tailles et prix
     * 
     * @param Request $request Requête HTTP
     * @param EntityManagerInterface $em Gestionnaire d'entités Doctrine
     * @param CategorieRepository $categorieRepository Repository pour accéder aux catégories
     * @return Response Vue du formulaire ou redirection
     */
    #[Route('/produit/new', name: 'produit_new')]
    public function new(Request $request, EntityManagerInterface $em, CategorieRepository $categorieRepository): Response
    {
        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit, [
            // Désactivation de la protection CSRF sur ce formulaire si souhaité
            'csrf_protection' => false,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion de l'upload de l'image
            $file = $form->get('ref_produit')->getData();
            if ($file) {
                if (!$file instanceof UploadedFile) {
                    $this->addFlash('error', "Le fichier uploadé est invalide.");
                    return $this->redirectToRoute('produit_new');
                }

                $newFilename = uniqid() . '.' . $file->guessExtension();
                $uploadDir = $this->getParameter('images_directory');
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                try {
                    $file->move($uploadDir, $newFilename);
                    $produit->setRefProduit($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', "Erreur lors de l'upload de l'image : " . $e->getMessage());
                    return $this->redirectToRoute('produit_new');
                }
            }

            // Traitement des champs supplémentaires "Menu" et "Réduction"
            // Ces champs ne sont pas intégrés dans le form type et sont récupérés manuellement
            $menuCheckbox = $request->request->get('menuCheckbox'); // "on" si coché, sinon null
            $reduction     = $request->request->get('reduction');     // Valeur saisie pour la réduction

            $categorie = $form->get('Categorie')->getData();
            if ($categorie && $categorie->getId() == 1) { // Si la catégorie est "boisson"
                if ($menuCheckbox) {
                    $produit->setEstMenuBoisson(1);
                    if (!empty($reduction)) {
                        $produit->setValeur($reduction);
                    }
                } else {
                    $produit->setEstMenuBoisson(0);
                }
                // Ici, éventuellement, on peut s'assurer que est_menu est à 0 pour les boissons
                $produit->setEstMenu(0);
            } else { // Pour les autres catégories
                if ($menuCheckbox) {
                    $produit->setEstMenu(1);
                } else {
                    $produit->setEstMenu(0);
                }
                // S'assurer que pour les non-boissons, est_menu_boisson reste à 0
                $produit->setEstMenuBoisson(0);
            }

            try {
                // Persister le produit
                $em->persist($produit);
                $em->flush();

                // Création de l'entité Avoir (relation taille et prix)
                $taille = $form->get('taille')->getData();
                $prix   = $form->get('prix')->getData();

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
            'form'       => $form->createView(),
            'categories' => $categorieRepository->findAll(),
        ]);
    }

    /**
     * Récupère la liste des sous-catégories pour une catégorie donnée (API)
     * 
     * @param int $categorieId ID de la catégorie parent
     * @param CategorieRepository $categorieRepository Repository pour accéder aux catégories
     * @return JsonResponse Liste des sous-catégories au format JSON
     */
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
                'id'      => $sousCategorie->getId(),
                'libelle' => $sousCategorie->getLibelle()
            ];
        }
        
        return $this->json($sousCategories);
    }

    /**
     * Supprime un produit du catalogue
     * 
     * @param Produit $produit Le produit à supprimer
     * @param EntityManagerInterface $em Gestionnaire d'entités Doctrine
     * @return Response Redirection vers la liste des produits
     */
    #[Route('/produit/{id}/delete', name: 'produit_delete', methods: ['POST'])]
    public function delete(Produit $produit, EntityManagerInterface $em): Response
    {

        try {
            $em->remove($produit);
            $em->flush();

            $this->addFlash('success', 'Produit supprimé avec succès.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la suppression du produit.');
        }

        return $this->redirectToRoute('produit');
    }

    /**
     * Met à jour le prix d'un produit pour une taille donnée via AJAX
     * 
     * @param Request $request Requête HTTP contenant le nouveau prix
     * @param Avoir $avoir Relation produit-taille à mettre à jour
     * @param EntityManagerInterface $em Gestionnaire d'entités Doctrine
     * @return JsonResponse Confirmation de la mise à jour
     */
    #[Route('/avoir/{id}/update-prix', name: 'update_prix', methods: ['POST'])]
    public function updatePrix(Request $request, Avoir $avoir, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (isset($data['prix'])) {
            $avoir->setPrix(floatval($data['prix']));
            $em->flush();

            return $this->json(['success' => true]);
        }

        return $this->json(['success' => false, 'message' => 'Données invalides'], 400);
    }
}
