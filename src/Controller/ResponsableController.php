<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Responsable;
use App\Repository\ResponsableRepository;
use Symfony\Component\Security\Http\Attribute\IsGranted;


class ResponsableController extends AbstractController
{
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/responsable', name: 'responsable', methods: ['GET'])]
    public function index(ResponsableRepository $repository): Response
    {
        $donnees = $repository->findAll();
        
        return $this->render('/responsable/responsable.html.twig', [
            'controller_name' => 'ResponsableController',
            'donnees' => $donnees,
        ]);
    }

    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/responsable/{id}', name: 'responsable_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(?Responsable $donnee): Response
    {
        return $this->render('/responsable/show.html.twig',[
            'donnee' => $donnee,
        ]);
    }
}
