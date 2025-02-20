<?php

namespace App\Controller;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Responsable;
use App\Form\ResponsableType;
use Doctrine\ORM\EntityManagerInterface;

class HomeController extends AbstractController
{
    #[Route('/home', name: 'home', methods: ['GET', 'POST'])]
    public function index(Request $request, EntityManagerInterface $manager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $respo = new Responsable();
        $form = $this->createForm(ResponsableType::class, $respo);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hash le mot de passe avant de le persister
            $hashedPassword = $passwordHasher->hashPassword($respo, $respo->getMdp());
            $respo->setMdp($hashedPassword);  // Le setter est `setMdp` pour stocker le mot de passe haché

            // Persiste l'utilisateur
            $manager->persist($respo);
            $manager->flush();

            return $this->redirectToRoute('app_login');
        }

        return $this->render('home/index.html.twig', [
            'form' => $form->createView(),
            'message' => 'Bienvenue sur le formulaire d\'inscription',
        ]);
    }
}
