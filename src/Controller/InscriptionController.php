<?php

namespace App\Controller;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Responsable;
use App\Form\ResponsableType;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\NotificationService;

class InscriptionController extends AbstractController
{
    #[Route('/inscription', name: 'inscription', methods: ['GET', 'POST'])]
    public function index(Request $request, EntityManagerInterface $manager, UserPasswordHasherInterface $passwordHasher, NotificationService $notificationService): Response
    {
        $respo = new Responsable();
        $form = $this->createForm(ResponsableType::class, $respo);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hash le mot de passe avant de le persister
            $hashedPassword = $passwordHasher->hashPassword($respo, $respo->getMdp());
            $respo->setMdp($hashedPassword);

            // Persiste l'utilisateur
            $manager->persist($respo);
            $manager->flush();

            // Notifier les administrateurs après l'inscription
            $notificationService->notifyAdmins($respo);

            // Redirection vers la page de connexion ou une autre page de confirmation
            return $this->redirectToRoute('app_login');
        }

        return $this->render('inscription/index.html.twig', [
            'form' => $form->createView(),
            'message' => 'Formulaire d\'inscription',
        ]);
    }
}
