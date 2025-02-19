<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Responsable;
use App\Repository\ResponsableRepository;

class ConnexionController extends AbstractController
{
    #[Route('/connexion', name: 'connexion', methods: ['GET'])]
    public function index(ResponsableRepository $repository): Response
    {
        $donnees = $repository->findAll();
        
        return $this->render('/connexion/connexion.html.twig', [
            'controller_name' => 'ConnexionController',
            'donnees' => $donnees,
        ]);
    }

    #[Route('/connexion/{id}', name: 'connexion_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(?Responsable $donnee): Response
    {
        return $this->render('/connexion/show.html.twig',[
            'donnee' => $donnee,
        ]);
    }
}
