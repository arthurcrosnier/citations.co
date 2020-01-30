<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191205143021 extends AbstractMigration
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
        $this->addSql('ALTER TABLE citation_tag DROP FOREIGN KEY FK_3D08F3B5500A8AB7');
        $this->addSql('ALTER TABLE citation_tag DROP FOREIGN KEY FK_3D08F3B5BAD26311');
        $this->addSql('ALTER TABLE citation_tag ADD CONSTRAINT FK_3D08F3B5500A8AB7 FOREIGN KEY (citation_id) REFERENCES citation_v2 (id)');
        $this->addSql('ALTER TABLE citation_tag ADD CONSTRAINT FK_3D08F3B5BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id)');
        $this->addSql('ALTER TABLE citation_attente_tag DROP FOREIGN KEY FK_7D6EDACDBAD26311');
        $this->addSql('ALTER TABLE citation_attente_tag DROP FOREIGN KEY FK_7D6EDACDE928945D');
        $this->addSql('ALTER TABLE citation_attente_tag ADD CONSTRAINT FK_7D6EDACDBAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id)');
        $this->addSql('ALTER TABLE citation_attente_tag ADD CONSTRAINT FK_7D6EDACDE928945D FOREIGN KEY (citation_attente_id) REFERENCES citation_attente (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE citation_attente_tag DROP FOREIGN KEY FK_7D6EDACDE928945D');
        $this->addSql('ALTER TABLE citation_attente_tag DROP FOREIGN KEY FK_7D6EDACDBAD26311');
        $this->addSql('ALTER TABLE citation_attente_tag ADD CONSTRAINT FK_7D6EDACDE928945D FOREIGN KEY (citation_attente_id) REFERENCES citation_attente (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE citation_attente_tag ADD CONSTRAINT FK_7D6EDACDBAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE citation_tag DROP FOREIGN KEY FK_3D08F3B5500A8AB7');
        $this->addSql('ALTER TABLE citation_tag DROP FOREIGN KEY FK_3D08F3B5BAD26311');
        $this->addSql('ALTER TABLE citation_tag ADD CONSTRAINT FK_3D08F3B5500A8AB7 FOREIGN KEY (citation_id) REFERENCES citation_v2 (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE citation_tag ADD CONSTRAINT FK_3D08F3B5BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE citation_v2 CHANGE auteur_id auteur_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE fb_page_post CHANGE page_id page_id INT DEFAULT NULL');
    }
}
