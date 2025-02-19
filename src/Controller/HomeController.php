<?php

namespace App\Controller;
/*
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Responsable;
use App\Form\ResponsableType;
use App\Entity\Role;


class HomeController extends AbstractController
{
    #[Route('/', name: 'home', methods: ['GET', 'POST'])]
    public function index(Request $request, EntityManagerInterface $manager): Response
    {
        $respo = new Responsable();
        $form = $this->createForm(ResponsableType::class, $respo);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            
            $manager->persist($respo);
            $manager->flush();

            return $this->redirectToRoute('home');
        }

        return $this->render('home/index.html.twig', [      
            'form' => $form->createView(),
            'message' => 'Bienvenue sur le formulaire d\'inscription',
        ]);
    }
}

*/
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Responsable;
use App\Form\ResponsableType;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home', methods: ['GET', 'POST'])]
    public function index(Request $request, EntityManagerInterface $manager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $respo = new Responsable();
        $form = $this->createForm(ResponsableType::class, $respo);
        $form->handleRequest($request);
        
        // 🔍 Debug : Vérifier si le formulaire est soumis
        if ($form->isSubmitted()) {
            dump("Formulaire soumis !");
        }

        if ($form->isSubmitted() && $form->isValid()) {
            dump("Formulaire valide !");
            
            // 🔍 Debug : Vérifier si le champ 'mdp' est bien récupéré
            $plainPassword = $form->get('mdp')->getData();
            dump("Mot de passe récupéré :", $plainPassword);

            if (!$plainPassword) {
                dump("⚠️ Le champ mdp est vide !");
                die();
            }

            // Hachage du mot de passe
            $hashedPassword = $passwordHasher->hashPassword($respo, $plainPassword);
            $respo->setPassword($hashedPassword);

            // Sauvegarde en BDD
            $manager->persist($respo);
            $manager->flush();

            return $this->redirectToRoute('home');
        }

        return $this->render('home/index.html.twig', [      
            'form' => $form->createView(),
            'message' => 'Bienvenue sur le formulaire d\'inscription',
        ]);
    }
}
