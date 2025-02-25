<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Entity\SousCategorie;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    // src/Controller/ApiController.php

    #[Route('/api/sous-categories/{categorieId}', name: 'api_sous_categories', methods: ['GET'])]
    public function getSousCategories(int $categorieId, EntityManagerInterface $em): JsonResponse
    {
        $categorie = $em->getRepository(Categorie::class)->find($categorieId);

        if (!$categorie) {
            return $this->json(['error' => 'Catégorie non trouvée'], 404);
        }

        $sousCategories = $categorie->getSousCategories();

        $data = [];
        foreach ($sousCategories as $sousCategorie) {
            $data[] = [
                'id' => $sousCategorie->getId(),
                'libelle' => $sousCategorie->getLibelle(),
            ];
        }

        return $this->json($data);
    }
}