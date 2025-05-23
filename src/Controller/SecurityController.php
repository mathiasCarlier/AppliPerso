<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Contrôleur gérant l'authentification et la déconnexion des utilisateurs
 * 
 * Ce contrôleur gère :
 * - La page de connexion et le processus d'authentification
 * - La déconnexion des utilisateurs
 * - La gestion des erreurs d'authentification
 */
class SecurityController extends AbstractController
{
    /**
     * Affiche et gère le formulaire de connexion
     * 
     * Cette méthode :
     * - Affiche le formulaire de connexion
     * - Gère les erreurs d'authentification
     * - Conserve le dernier nom d'utilisateur saisi
     * 
     * @param AuthenticationUtils $authenticationUtils Utilitaire d'authentification Symfony
     * @return Response Page de connexion avec les éventuelles erreurs
     */
    #[Route(path: '/', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);

    }

    /**
     * Gère la déconnexion des utilisateurs
     * 
     * Cette méthode est interceptée par le firewall de Symfony.
     * Elle n'a pas besoin d'implémenter de logique car la déconnexion
     * est gérée automatiquement par le système de sécurité.
     * 
     * @throws \LogicException Cette exception ne sera jamais lancée car la méthode est interceptée
     */
    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
