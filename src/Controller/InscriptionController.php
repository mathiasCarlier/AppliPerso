<?php
namespace App\Controller;

use App\Entity\Responsable;
use App\Form\ResponsableType;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Role;

class InscriptionController extends AbstractController
{
    #[Route('/inscription', name: 'inscription', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        EntityManagerInterface $manager,
        UserPasswordHasherInterface $passwordHasher,
        MailerInterface $mailer,
        LoggerInterface $logger
    ): Response {
        $respo = new Responsable();

        $form = $this->createForm(ResponsableType::class, $respo, [
            'csrf_protection' => false,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Hash du mot de passe
                $hashedPassword = $passwordHasher->hashPassword($respo, $respo->getMdp());
                $respo->setMdp($hashedPassword);

                // Sauvegarde en base de données
                $manager->persist($respo);
                $manager->flush();

                // Récupération des administrateurs (role_id = 1)
                $adminRole = $manager->getRepository(Role::class)->find(1);

                if ($adminRole) {
                    $adminList = $manager->getRepository(Responsable::class)->findBy(['Role' => $adminRole]);
                    $adminEmails = array_map(fn($admin) => $admin->getMail(), $adminList);

                    if (!empty($adminEmails)) {
                        // Préparation du corps de l'email
                        $body = '<h1>Nouvelle inscription</h1>' .
                            '<p>Bonjour,</p>' .
                            '<p>Un nouveau responsable vient de s\'inscrire.</p>' .
                            '<p><strong>Nom :</strong> ' . htmlspecialchars($respo->getNom()) . '</p>' .
                            '<p><strong>Prénom :</strong> ' . htmlspecialchars($respo->getPrenom()) . '</p>' .
                            '<p>Merci de vérifier cette nouvelle inscription.</p>';

                        // Création de l'email
                        $notificationEmail = (new Email())
                            ->from('lefauteuilrougetest@gmail.com')
                            ->to(...$adminEmails)
                            ->subject('Nouvelle inscription')
                            ->html($body);

                        // Log avant envoi
                        $logger->info('Envoi de notification par email', [
                            'from'    => 'lefauteuilrougetest@gmail.com',
                            'to'      => $adminEmails,
                            'subject' => 'Nouvelle inscription',
                            'body'    => $body,
                        ]);

                        // Envoi de l'email
                        try {
                            $mailer->send($notificationEmail);
                            $logger->info('Email envoyé avec succès');
                        } catch (\Exception $e) {
                            $logger->error('Erreur lors de l’envoi du mail', ['error' => $e->getMessage()]);
                            $this->addFlash('error', 'Erreur lors de l’envoi du mail : ' . $e->getMessage());
                        }
                    } else {
                        $logger->warning('Aucun administrateur trouvé pour recevoir l’email.');
                        $this->addFlash('warning', 'Aucun administrateur trouvé pour recevoir l’email.');
                    }
                } else {
                    $logger->warning('Le rôle Administrateur (id=1) n\'existe pas.');
                    $this->addFlash('warning', 'Le rôle Administrateur (id=1) n\'existe pas.');
                }

                $this->addFlash('success', 'Votre inscription a été réalisée avec succès.');
                return $this->redirectToRoute('app_login');

            } catch (\Exception $e) {
                $logger->error('Erreur lors de l’inscription', ['error' => $e->getMessage()]);
                $this->addFlash('error', 'Une erreur est survenue lors de l\'inscription : ' . $e->getMessage());
            }
        }

        return $this->render('inscription/index.html.twig', [
            'form'    => $form->createView(),
            'message' => 'Formulaire d\'inscription',
        ]);
    }
}
