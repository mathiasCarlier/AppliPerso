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
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

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
            'produits'   => $produits,
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
            // Récupérer le fichier uploadé
            $file = $form->get('ref_produit')->getData();
            
            if ($file) {
                // Vérifier que c'est bien un fichier
                if (!$file instanceof UploadedFile) {
                    $this->addFlash('error', "Le fichier uploadé est invalide.");
                    return $this->redirectToRoute('produit_new');
                }

                // Générer un nom unique pour le fichier
                $newFilename = uniqid() . '.' . $file->guessExtension();

                // Définir le dossier d'upload (configuré dans services.yaml)
                $uploadDir = $this->getParameter('images_directory');

                // Vérifier si le dossier existe, sinon le créer
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                try {
                    // Déplacer le fichier vers le dossier d'upload
                    $file->move($uploadDir, $newFilename);
                    $produit->setRefProduit($newFilename); // Enregistrer le nom du fichier en base
                } catch (FileException $e) {
                    $this->addFlash('error', "Erreur lors de l'upload de l'image : " . $e->getMessage());
                    return $this->redirectToRoute('produit_new');
                }
            }

            try {
                // Persister le produit
                $em->persist($produit);
                $em->flush();

                // Création de l'entité Avoir (relation avec taille et prix)
                $taille = $form->get('taille')->getData();
                $prix = $form->get('prix')->getData();

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

    #[Route('/produit/{id}/delete', name: 'produit_delete', methods: ['POST'])]
    public function delete(Produit $produit, EntityManagerInterface $em, Request $request, CsrfTokenManagerInterface $csrfTokenManager): Response
    {
        $token = $request->request->get('_token');

        if (!$csrfTokenManager->isTokenValid(new CsrfToken('delete' . $produit->getId(), $token))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('produit');
        }

        try {
            $em->remove($produit);
            $em->flush();

            $this->addFlash('success', 'Produit supprimé avec succès.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la suppression du produit.');
        }

        return $this->redirectToRoute('produit');
    }

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
