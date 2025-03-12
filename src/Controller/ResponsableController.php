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


class ResponsableController extends AbstractController
{
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/responsable', name: 'responsable', methods: ['GET'])]
    public function index(ResponsableRepository $repository): Response
    {
        $donnees = $repository->findAll();
        
        return $this->render('/responsable/responsable.html.twig', [
            'controller_name' => 'ResponsableController',
            'donnees' => $donnees,
        ]);
    }

    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/responsable/{id}', name: 'responsable_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(?Responsable $donnee): Response
    {
        return $this->render('/responsable/show.html.twig',[
            'donnee' => $donnee,
        ]);
    }

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