<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260228154446 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE vehicule (id INT AUTO_INCREMENT NOT NULL, collection_photo_lien JSON DEFAULT NULL, type VARCHAR(255) NOT NULL, vin VARCHAR(255) NOT NULL, immatriculation VARCHAR(255) NOT NULL, marque VARCHAR(255) NOT NULL, modele VARCHAR(255) NOT NULL, version VARCHAR(255) NOT NULL, date_mise_en_circulation DATE NOT NULL, energie VARCHAR(255) NOT NULL, boite_vitesse VARCHAR(255) NOT NULL, puissance_fiscale INT NOT NULL, kilometrage INT NOT NULL, couleur VARCHAR(255) NOT NULL, nombre_portes INT NOT NULL, nombre_places INT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE vehicule');
    }
}
