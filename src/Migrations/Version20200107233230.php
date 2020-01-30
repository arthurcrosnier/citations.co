<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200107233230 extends AbstractMigration
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
        $this->addSql('ALTER TABLE citation_v2 ADD date_citation_du_jour DATETIME DEFAULT NULL, CHANGE auteur_id auteur_id INT DEFAULT NULL, CHANGE imageChanged imageChanged INT DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE citation_v2 DROP date_citation_du_jour, CHANGE auteur_id auteur_id INT DEFAULT NULL, CHANGE imageChanged imageChanged INT DEFAULT NULL');
        $this->addSql('ALTER TABLE fb_page_post CHANGE page_id page_id INT DEFAULT NULL');
    }
}
