<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200111140608 extends AbstractMigration
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
        $this->addSql('ALTER TABLE citation_v2 CHANGE auteur_id auteur_id INT DEFAULT NULL, CHANGE imageChanged imageChanged INT DEFAULT NULL, CHANGE date_citation_du_jour date_citation_du_jour DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE roles roles JSON NOT NULL');
        $this->addSql('ALTER TABLE article CHANGE picture picture VARCHAR(255) DEFAULT NULL, CHANGE publication_date publication_date DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE article CHANGE picture picture VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE publication_date publication_date DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE citation_v2 CHANGE auteur_id auteur_id INT DEFAULT NULL, CHANGE date_citation_du_jour date_citation_du_jour DATETIME DEFAULT \'NULL\', CHANGE imageChanged imageChanged INT DEFAULT NULL');
        $this->addSql('ALTER TABLE fb_page_post CHANGE page_id page_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE roles roles LONGTEXT NOT NULL COLLATE utf8mb4_bin');
    }
}
