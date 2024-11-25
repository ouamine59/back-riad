<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241125091451 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE rows_order (id INT AUTO_INCREMENT NOT NULL, orders_id INT NOT NULL, products_id INT NOT NULL, amount INT NOT NULL, price VARCHAR(6) NOT NULL, INDEX IDX_272AFF3ECFFE9AD6 (orders_id), INDEX IDX_272AFF3E6C8A81A9 (products_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE rows_order ADD CONSTRAINT FK_272AFF3ECFFE9AD6 FOREIGN KEY (orders_id) REFERENCES orders (id)');
        $this->addSql('ALTER TABLE rows_order ADD CONSTRAINT FK_272AFF3E6C8A81A9 FOREIGN KEY (products_id) REFERENCES products (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE rows_order DROP FOREIGN KEY FK_272AFF3ECFFE9AD6');
        $this->addSql('ALTER TABLE rows_order DROP FOREIGN KEY FK_272AFF3E6C8A81A9');
        $this->addSql('DROP TABLE rows_order');
    }
}
