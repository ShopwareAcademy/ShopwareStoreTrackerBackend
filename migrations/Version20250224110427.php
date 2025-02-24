<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250224110427 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE shop (shop_id VARCHAR(255) NOT NULL, shop_url VARCHAR(255) NOT NULL, shop_secret VARCHAR(255) NOT NULL, shop_client_id VARCHAR(255) DEFAULT NULL, shop_client_secret VARCHAR(255) DEFAULT NULL, shop_active BOOLEAN NOT NULL, PRIMARY KEY(shop_id))');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE shop');
    }
}
