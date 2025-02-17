<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250217135327 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE appartient CHANGE produit_id produit_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE avoir CHANGE produit_id produit_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE client CHANGE comment comment VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE ligne_commande CHANGE produit_id produit_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE possede CHANGE categorie_id categorie_id INT DEFAULT NULL, CHANGE sous_categorie_id sous_categorie_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE produit DROP en_rupture, CHANGE prix prix DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE responsable CHANGE role_id role_id INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client CHANGE comment comment VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE ligne_commande CHANGE produit_id produit_id INT NOT NULL');
        $this->addSql('ALTER TABLE avoir CHANGE produit_id produit_id INT NOT NULL');
        $this->addSql('ALTER TABLE responsable CHANGE role_id role_id INT NOT NULL');
        $this->addSql('ALTER TABLE appartient CHANGE produit_id produit_id INT NOT NULL');
        $this->addSql('ALTER TABLE produit ADD en_rupture TINYINT(1) NOT NULL, CHANGE prix prix DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE possede CHANGE categorie_id categorie_id INT NOT NULL, CHANGE sous_categorie_id sous_categorie_id INT NOT NULL');
    }
}
