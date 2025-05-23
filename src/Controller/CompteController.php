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

/**
 * Contrôleur gérant les opérations liées au compte utilisateur
 * 
 * Ce contrôleur permet aux utilisateurs de :
 * - Consulter leurs informations personnelles
 * - Modifier leurs informations de compte
 * - Changer leur mot de passe de manière sécurisée
 * 
 * Sécurité :
 * - Nécessite une authentification
 * - Vérifie l'ancien mot de passe avant modification
 * - Valide la correspondance des nouveaux mots de passe
 */
class CompteController extends AbstractController
{
    /**
     * Affiche et traite le formulaire de modification du compte utilisateur
     * 
     * Cette méthode :
     * - Vérifie que l'utilisateur est connecté
     * - Affiche le formulaire avec les informations actuelles
     * - Valide les modifications demandées
     * - Gère le changement de mot de passe de manière sécurisée
     * - Persiste les modifications en base de données
     * 
     * Processus de changement de mot de passe :
     * 1. Vérifie l'ancien mot de passe
     * 2. Vérifie que les nouveaux mots de passe correspondent
     * 3. Hash le nouveau mot de passe avant enregistrement
     * 
     * @param Request $request Requête HTTP
     * @param EntityManagerInterface $manager Gestionnaire d'entités Doctrine
     * @param UserPasswordHasherInterface $passwordHasher Service de hashage des mots de passe
     * @return Response Vue du formulaire ou redirection
     * @throws AccessDeniedException Si l'utilisateur n'est pas connecté
     */
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
