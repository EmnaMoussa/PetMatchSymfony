<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260527133000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Separate match and message notifications';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE notifications ADD type VARCHAR(40) NOT NULL DEFAULT "match"');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE notifications DROP type');
    }
}
