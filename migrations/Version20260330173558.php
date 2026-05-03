<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260330173558 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE dossier (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) NOT NULL, statut VARCHAR(255) NOT NULL, documents JSON NOT NULL, user_id INT NOT NULL, vehicule_id INT NOT NULL, INDEX IDX_3D48E037A76ED395 (user_id), INDEX IDX_3D48E0374A4A3511 (vehicule_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE vehicule (id INT AUTO_INCREMENT NOT NULL, collection_photo_lien JSON DEFAULT NULL, type VARCHAR(255) NOT NULL, vin VARCHAR(255) NOT NULL, immatriculation VARCHAR(255) NOT NULL, marque VARCHAR(255) NOT NULL, modele VARCHAR(255) NOT NULL, version VARCHAR(255) NOT NULL, date_mise_en_circulation DATE NOT NULL, energie VARCHAR(255) NOT NULL, boite_vitesse VARCHAR(255) NOT NULL, puissance_fiscale INT NOT NULL, kilometrage INT NOT NULL, couleur VARCHAR(255) NOT NULL, nombre_portes INT NOT NULL, nombre_places INT NOT NULL, prix NUMERIC(10, 2) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE dossier ADD CONSTRAINT FK_3D48E037A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE dossier ADD CONSTRAINT FK_3D48E0374A4A3511 FOREIGN KEY (vehicule_id) REFERENCES vehicule (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dossier DROP FOREIGN KEY FK_3D48E037A76ED395');
        $this->addSql('ALTER TABLE dossier DROP FOREIGN KEY FK_3D48E0374A4A3511');
        $this->addSql('DROP TABLE dossier');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE vehicule');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
