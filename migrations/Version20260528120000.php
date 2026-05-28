<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260528120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Allow pets to be created without a photo';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE pets MODIFY photo VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql("UPDATE pets SET photo = 'profil1.webp' WHERE photo IS NULL");
        $this->addSql("ALTER TABLE pets MODIFY photo VARCHAR(255) NOT NULL");
    }
}
