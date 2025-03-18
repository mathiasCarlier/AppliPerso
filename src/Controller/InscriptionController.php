<?php
namespace App\Controller;

use App\Entity\Responsable;
use App\Form\ResponsableType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class InscriptionController extends AbstractController
{
    #[Route('/inscription', name: 'inscription', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        EntityManagerInterface $manager,
        UserPasswordHasherInterface $passwordHasher,
        MailerInterface $mailer
    ): Response {
        // Création de l'entité Responsable
        $respo = new Responsable();

        // Création du formulaire à partir du type ResponsableType
        // Pour la démonstration, la protection CSRF est désactivée (à réactiver en prod)
        $form = $this->createForm(ResponsableType::class, $respo, [
            'csrf_protection' => false,
        ]);

        // Traitement de la soumission du formulaire
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Hachage du mot de passe via le UserPasswordHasherInterface
                $hashedPassword = $passwordHasher->hashPassword($respo, $respo->getMdp());
                $respo->setMdp($hashedPassword);

                // Enregistrement de l'entité en base de données
                $manager->persist($respo);
                $manager->flush();

                // Récupération de la liste des administrateurs (role_id = 1)
                $adminList = $manager->getRepository(Responsable::class)->findBy(['role_id' => 1]);
                $adminEmails = [];
                foreach ($adminList as $admin) {
                    $adminEmails[] = $admin->getMail();
                }

                // Envoi d'une notification par email si au moins un administrateur est trouvé
                if (!empty($adminEmails)) {
                    $notificationEmail = (new Email())
                        ->from('lefauteuilrougetest@gmail.com') // Adresse d'envoi
                        ->to(...$adminEmails) // Envoi à tous les admins
                        ->subject('Nouvelle inscription')
                        ->html(
                            '<h1>Nouvelle inscription</h1>' .
                            '<p>Bonjour,</p>' .
                            '<p>Un nouveau responsable vient de s\'inscrire.</p>' .
                            '<p><strong>Nom :</strong> ' . $respo->getNom() . '</p>' .
                            '<p><strong>Prénom :</strong> ' . $respo->getPrenom() . '</p>' .
                            '<p>Merci de vérifier cette nouvelle inscription sur votre interface.</p>'
                        );

                    $mailer->send($notificationEmail);
                }

                // Message flash de succès et redirection vers la page de login
                $this->addFlash('success', 'Votre inscription a été réalisée avec succès.');
                return $this->redirectToRoute('app_login');
            } catch (\Exception $e) {
                // Gestion de l'erreur et affichage d'un message flash
                $this->addFlash('error', 'Une erreur est survenue lors de l\'inscription : ' . $e->getMessage());
            }
        }

        // Affichage du formulaire dans la vue Twig
        return $this->render('inscription/index.html.twig', [
            'form' => $form->createView(),
            'message' => 'Formulaire d\'inscription',
        ]);
    }
}