<?php
namespace App\Controller;

use App\Entity\Categorie;
use App\Entity\SousCategorie;
use App\Entity\Taille;
use App\Entity\Possede;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AjaxController extends AbstractController
{
    #[Route('/produit/new/add-categorie', name: 'add_categorie', methods: ['POST'])]
    public function addCategorie(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $categorie = new Categorie();
        $categorie->setLibelle($data['libelle']);
        
        $em->persist($categorie);
        $em->flush();

        return $this->json([
            'id' => $categorie->getId(),
            'libelle' => $categorie->getLibelle()
        ]);
    }

    #[Route('/produit/new/add-sous_categorie', name: 'add_sous_categorie', methods: ['POST'])]
    public function addSousCategorie(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $sousCategorie = new SousCategorie();
        $sousCategorie->setLibelle($data['libelle']);
        
        // Associer à la catégorie via Possede
        $categorie = $em->getRepository(Categorie::class)->find($data['categorieId']);
        if ($categorie) {
            $possede = new Possede();
            $possede->setCategorie($categorie);
            $possede->setSousCategorie($sousCategorie);
            
            $em->persist($possede);
            $em->persist($sousCategorie);
            $em->flush();
        }

        return $this->json([
            'id' => $sousCategorie->getId(),
            'libelle' => $sousCategorie->getLibelle()
        ]);
    }

    #[Route('/produit/new/add-taille', name: 'add_taille', methods: ['POST'])]
    public function addTaille(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $taille = new Taille();
        $taille->setUnite($data['unite']);
        
        $em->persist($taille);
        $em->flush();

        return $this->json([
            'id' => $taille->getId(),
            'unite' => $taille->getUnite()
        ]);
    }
}