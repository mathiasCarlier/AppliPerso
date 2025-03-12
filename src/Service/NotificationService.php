<?php
namespace App\Service;

use App\Repository\ResponsableRepository;
use Symfony\Component\Mailer\MailerInterface;
use App\Entity\Responsable;
use Symfony\Component\Mime\Email;
use Psr\Log\LoggerInterface;

class NotificationService
{
    private $mailer;
    private $responsableRepository;
    private $logger;

    public function __construct(MailerInterface $mailer, ResponsableRepository $responsableRepository, LoggerInterface $logger)
    {
        $this->mailer = $mailer;
        $this->responsableRepository = $responsableRepository;
        $this->logger = $logger;
    }

    public function notifyAdmins($newResponsable)
    {
        // Récupérer les responsables ayant le rôle d'admin (ID du rôle = 1)
        $admins = $this->responsableRepository->findBy(['Role' => 1]);

        // Parcourir tous les admins et envoyer l'email uniquement si l'email est défini
        foreach ($admins as $admin) {
            $adminEmail = $admin->getMail();
            if ($adminEmail) {
                $email = (new Email())
                    ->from('mathiasc1811@gmail.com')
                    ->to($adminEmail)
                    ->subject('Nouvelle inscription')
                    ->html('<p>Un nouveau responsable a été inscrit : ' . $newResponsable->getNom() . ' ' . $newResponsable->getPrenom() . '.</p>');

                // Envoi de l'email
                $this->mailer->send($email);

                // Log l'email envoyé
                $this->logger->info('Email envoyé à ' . $adminEmail);
            }
        }
    }

    public function testMailer(MailerInterface $mailer)
    {
        $email = (new Email())
            ->from( 'mathiasc1811@gmail.com')
            ->to('mthscarlier@gmail.com')
            ->subject('Test Email')
            ->text('Ceci est un test.');

        $mailer->send($email);
    }
}

