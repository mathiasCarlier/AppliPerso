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
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use App\Repository\RoleRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Contrôleur gérant les opérations liées aux responsables du cinéma
 * 
 * Ce contrôleur permet de :
 * - Lister les responsables avec filtrage par rôle
 * - Afficher les détails d'un responsable
 * - Gérer les vérifications et les rôles des responsables
 * - Supprimer des responsables
 * 
 * Toutes les actions nécessitent une authentification complète
 */
class ResponsableController extends AbstractController
{
    /**
     * Affiche la liste des responsables avec possibilité de filtrage par rôle
     * 
     * @param Request $request Requête HTTP contenant les paramètres de filtrage
     * @param ResponsableRepository $repository Repository pour accéder aux responsables
     * @return Response Vue de la liste des responsables
     */
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/responsable', name: 'responsable', methods: ['GET'])]
    public function index(Request $request, ResponsableRepository $repository): Response
    {
        $roleFilter = $request->query->get('roleFilter');

        if ($roleFilter !== null && $roleFilter !== '') {
            if ($roleFilter === 'null') {
                // Recherche des responsables dont le rôle est null
                $donnees = $repository->findBy(['Role' => null]);
            } else {
                // Recherche des responsables avec le rôle correspondant
                $donnees = $repository->findBy(['Role' => $roleFilter]);
            }
        } else {
            $donnees = $repository->findAll();
        }

        return $this->render('/responsable/responsable.html.twig', [
            'controller_name' => 'ResponsableController',
            'donnees' => $donnees,
        ]);
    }

    /**
     * Affiche les détails d'un responsable spécifique
     * 
     * @param Responsable|null $donnee Le responsable à afficher
     * @return Response Vue détaillée du responsable
     */
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/responsable/{id}', name: 'responsable_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(?Responsable $donnee): Response
    {
        return $this->render('/responsable/show.html.twig',[
            'donnee' => $donnee,
        ]);
    }

    /**
     * Met à jour le statut de vérification et le rôle d'un responsable
     * 
     * Cette méthode permet de :
     * - Vérifier un responsable
     * - Attribuer un rôle (Admin ou Responsable)
     * - Révoquer les droits d'un responsable
     * 
     * @param Responsable $responsable Le responsable à mettre à jour
     * @param string $value La valeur du nouveau rôle (1: Admin, 2: Responsable, autre: aucun rôle)
     * @param EntityManagerInterface $em Gestionnaire d'entités Doctrine
     * @param Request $request Requête HTTP
     * @param CsrfTokenManagerInterface $csrfTokenManager Gestionnaire de tokens CSRF
     * @param RoleRepository $roleRepository Repository pour accéder aux rôles
     * @return Response Confirmation de la mise à jour
     */
    #[Route('/responsable/{id}/verif/{value}', name: 'responsable_verif', methods: ['POST'])]
    public function updateVerif(
        Responsable $responsable,
        $value,
        EntityManagerInterface $em,
        Request $request,
        CsrfTokenManagerInterface $csrfTokenManager,
        RoleRepository $roleRepository
    ): Response {
        // Vérification CSRF
        $submittedToken = $request->headers->get('X-CSRF-Token');
        if (!$csrfTokenManager->isTokenValid(new CsrfToken('verif', $submittedToken))) {
            return new JsonResponse(['status' => 'invalid csrf'], 403);
        }

        // Définition des valeurs en fonction du choix
        switch ($value) {
            case '2':
                $role = $roleRepository->find(2); // Responsable
                $responsable->setVerifResponsable(true);
                break;
            case '1':
                $role = $roleRepository->find(1); // Admin
                $responsable->setVerifResponsable(true);
                break;
            default:
                $role = null;
                $responsable->setVerifResponsable(false);
                break;
        }

        $responsable->setRole($role);
        $em->flush();

        return new JsonResponse(['status' => 'success']);
    }

    /**
     * Supprime un responsable du système
     * 
     * Restrictions :
     * - Impossible de supprimer un administrateur (rôle = 1)
     * - Vérification du token CSRF requise
     * 
     * @param Request $request Requête HTTP
     * @param Responsable $responsable Le responsable à supprimer
     * @param EntityManagerInterface $em Gestionnaire d'entités Doctrine
     * @return Response Redirection vers la liste des responsables
     */
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/responsable/{id}/delete', name: 'responsable_delete', methods: ['POST'])]
    public function delete(Request $request, Responsable $responsable, EntityManagerInterface $em): Response
    {
        // Empêcher la suppression si le rôle est égal à 1
        if ($responsable->getRole() && $responsable->getRole()->getId() === 1) {
            $this->addFlash('error', 'Vous ne pouvez pas supprimer un administrateur.');
            return $this->redirectToRoute('responsable');
        }

        // Vérification du token CSRF
        if ($this->isCsrfTokenValid('delete' . $responsable->getId(), $request->request->get('_token'))) {
            $em->remove($responsable);
            $em->flush();
            $this->addFlash('success', 'Responsable supprimé avec succès.');
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('responsable');
    }
}