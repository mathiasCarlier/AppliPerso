<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Responsable;
use App\Form\ResponsableType;

class HomeController extends AbstractController
{
   # #[Route('/', name: 'home')]
   # public function index(): Response
   # {
   #     return $this->render('home/index.html.twig', [
   #         'message' => 'Bienvenue sur ma première page Symfony !',
   #     ]);
   # }

    #[Route('/', name: 'home', methods: ['GET'])]
    public function index(): Response
    {
        $respo = new Responsable();
        $form = $this->createForm(ResponsableType::class, $respo);
        
        return $this->render('home/index.html.twig', [      
            'form' => $form,
        ]);
    }
}
