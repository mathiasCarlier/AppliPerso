<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Contrôleur gérant la page d'accueil de l'application
 * 
 * Ce contrôleur :
 * - Affiche la page d'accueil principale
 * - Nécessite une authentification complète
 * - Sert de point d'entrée après la connexion
 */
final class AccueilController extends AbstractController
{
    /**
     * Affiche la page d'accueil de l'application
     * 
     * Cette page est accessible uniquement aux utilisateurs authentifiés
     * et sert de tableau de bord principal pour accéder aux différentes
     * fonctionnalités de l'application.
     * 
     * @return Response Vue de la page d'accueil
     */
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/accueil', name: 'accueil')]
    public function index(): Response
    {
        return $this->render('accueil/accueil.html.twig', [
            'controller_name' => 'AccueilController',
        ]);
    }
}
