<?php
namespace App\Service;

use App\Repository\ResponsableRepository;
use Symfony\Component\Mailer\MailerInterface;
use App\Entity\Responsable;
use Symfony\Component\Mime\Email;
use Psr\Log\LoggerInterface;  // Importer l'interface LoggerInterface

class NotificationService
{
    private $mailer;
    private $responsableRepository;
    private $logger;  // Déclare la propriété logger

    public function __construct(MailerInterface $mailer, ResponsableRepository $responsableRepository, LoggerInterface $logger)
    {
        $this->mailer = $mailer;
        $this->responsableRepository = $responsableRepository;
        $this->logger = $logger;  // Injecte le logger dans le constructeur
    }

    public function notifyAdmins($newResponsable)
    {
        // Récupérer les responsables ayant le rôle d'admin (ID du rôle = 1)
        $admins = $this->responsableRepository->findBy(['Role' => 1]);

        // Parcourir tous les admins et envoyer l'email uniquement si l'email est défini
        foreach ($admins as $admin) {
            // Vérifie si l'adresse email de l'admin est valide
            $adminEmail = $admin->getMail();
            if ($adminEmail) {
                $email = (new Email())
                    ->from('no-reply@votre-site.com') // Adresse de l'expéditeur
                    ->to($adminEmail) // Destinataire (adresse mail de l'admin)
                    ->subject('Nouvelle inscription') // Sujet de l'email
                    ->html('<p>Un nouveau responsable a été inscrit : ' . $newResponsable->getNom() . ' ' . $newResponsable->getPrenom() . '.</p>');

                // Envoi de l'email
                $this->mailer->send($email);

                // Log l'email envoyé
                $this->logger->info('Email envoyé à ' . $adminEmail);
            }
        }
    }

    // Méthode de test pour envoyer un email
    public function testMailer(MailerInterface $mailer)
    {
        $email = (new Email())
            ->from('no-reply@votre-site.com')
            ->to('ton-adresse-email@example.com')
            ->subject('Test Email')
            ->text('Ceci est un test.');

        $mailer->send($email);
    }

}

