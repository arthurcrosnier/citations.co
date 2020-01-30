<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191216175243 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE fb_page_post CHANGE page_id page_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE citation_v2 CHANGE auteur_id auteur_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE Citation CHANGE nom_image nom_image VARCHAR(200) NOT NULL, CHANGE auteur auteur VARCHAR(240) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Citation CHANGE nom_image nom_image VARCHAR(100) NOT NULL COLLATE utf8_general_ci, CHANGE auteur auteur VARCHAR(100) NOT NULL COLLATE latin1_swedish_ci');
        $this->addSql('ALTER TABLE citation_v2 CHANGE auteur_id auteur_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE fb_page_post CHANGE page_id page_id INT DEFAULT NULL');
    }
}
