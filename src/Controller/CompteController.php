<?php

namespace App\Controller;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Responsable;
use App\Form\CompteType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormError;

class CompteController extends AbstractController
{
    #[Route('/compte', name: 'compte', methods: ['GET', 'POST'])]
    public function index(Request $request, EntityManagerInterface $manager, UserPasswordHasherInterface $passwordHasher): Response
    {
        // Récupérer l'utilisateur actuellement authentifié
        $compte = $this->getUser();

        // Vérifier que l'utilisateur est bien connecté et qu'il a un compte
        if (!$compte) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour modifier votre compte.');
        }

        // Création du formulaire pré-rempli avec les données existantes
        $form = $this->createForm(CompteType::class, $compte);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérification de la correspondance des mots de passe
            $mdp = $form->get('mdp')->getData();
            $confirmMdp = $form->get('confirm_mdp')->getData();

            if ($mdp === $confirmMdp) {
                // Hacher le mot de passe uniquement s'il a été modifié
                if ($mdp) {
                    $hashedPassword = $passwordHasher->hashPassword($compte, $mdp);
                    $compte->setMdp($hashedPassword);
                }

                // Sauvegarde des modifications
                $manager->persist($compte);
                $manager->flush();

                // Rediriger l'utilisateur après la mise à jour
                return $this->redirectToRoute('accueil');
            } else {
                // Ajouter une erreur si les mots de passe ne correspondent pas
                $form->get('confirm_mdp')->addError(new FormError('Les mots de passe ne correspondent pas.'));
            }
        }

        return $this->render('compte/index.html.twig', [
            'form' => $form->createView(),
            'message' => 'Mon Compte',
        ]);
    }
}
