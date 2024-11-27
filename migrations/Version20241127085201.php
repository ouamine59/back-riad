<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241127085201 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE media_object DROP FOREIGN KEY FK_14D431326C8A81A9');
        $this->addSql('ALTER TABLE media_object CHANGE products_id products_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE media_object ADD CONSTRAINT FK_14D431326C8A81A9 FOREIGN KEY (products_id) REFERENCES products (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE media_object DROP FOREIGN KEY FK_14D431326C8A81A9');
        $this->addSql('ALTER TABLE media_object CHANGE products_id products_id INT NOT NULL');
        $this->addSql('ALTER TABLE media_object ADD CONSTRAINT FK_14D431326C8A81A9 FOREIGN KEY (products_id) REFERENCES products (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
