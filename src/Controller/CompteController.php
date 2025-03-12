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
    public function index(Request $request, EntityManagerInterface $manager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $compte = $this->getUser();

        if (!$compte) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour modifier votre compte.');
        }

        $form = $this->createForm(CompteType::class, $compte);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $hasError = false;

            $oldPassword = $form->get('oldPassword')->getData();
            $newPassword = $form->get('newPassword')->getData();
            $confirmNewPassword = $form->get('confirmNewPassword')->getData();

            if ($newPassword || $confirmNewPassword) {
                if (!$passwordHasher->isPasswordValid($compte, $oldPassword)) {
                    $form->get('oldPassword')->addError(new FormError('Ancien mot de passe incorrect.'));
                    $hasError = true;
                }
                
                if ($newPassword !== $confirmNewPassword) {
                    $form->get('confirmNewPassword')->addError(new FormError('Les nouveaux mots de passe ne correspondent pas.'));
                    $hasError = true;
                }

                if (!$hasError) {
                    $hashedPassword = $passwordHasher->hashPassword($compte, $newPassword);
                    $compte->setMdp($hashedPassword);
                }
            }

            if (!$hasError) {
                $manager->persist($compte);
                $manager->flush();
                return $this->redirectToRoute('accueil');
            }
        }

        return $this->render('compte/index.html.twig', [
            'form' => $form->createView(),
            'message' => 'Mon Compte',
        ]);
    }

}
