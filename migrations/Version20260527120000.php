<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260527120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Align Symfony schema with final PetMatch PHP features';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users ADD roles LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('UPDATE users SET roles = \'["ROLE_USER"]\' WHERE roles IS NULL');
        $this->addSql('ALTER TABLE users MODIFY roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('ALTER TABLE users ADD bio LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD profile_image VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD date_of_birth DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE users ADD updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP');

        $this->addSql('ALTER TABLE pets ADD created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE pets ADD updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP');

        $this->addSql('ALTER TABLE messages ADD pet_match_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE messages ADD read_at DATETIME DEFAULT NULL');
        $this->addSql('CREATE INDEX IDX_DB021E962341DD8C ON messages (pet_match_id)');
        $this->addSql('ALTER TABLE messages ADD CONSTRAINT FK_DB021E962341DD8C FOREIGN KEY (pet_match_id) REFERENCES pet_matches (id) ON DELETE CASCADE');

        $this->addSql('CREATE TABLE notifications (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, pet_match_id INT DEFAULT NULL, message VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, read_at DATETIME DEFAULT NULL, INDEX IDX_6000B0D3A76ED395 (user_id), INDEX IDX_6000B0D32341DD8C (pet_match_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE notifications ADD CONSTRAINT FK_6000B0D3A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE notifications ADD CONSTRAINT FK_6000B0D32341DD8C FOREIGN KEY (pet_match_id) REFERENCES pet_matches (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE notifications DROP FOREIGN KEY FK_6000B0D3A76ED395');
        $this->addSql('ALTER TABLE notifications DROP FOREIGN KEY FK_6000B0D32341DD8C');
        $this->addSql('DROP TABLE notifications');

        $this->addSql('ALTER TABLE messages DROP FOREIGN KEY FK_DB021E962341DD8C');
        $this->addSql('DROP INDEX IDX_DB021E962341DD8C ON messages');
        $this->addSql('ALTER TABLE messages DROP pet_match_id');
        $this->addSql('ALTER TABLE messages DROP read_at');

        $this->addSql('ALTER TABLE pets DROP created_at');
        $this->addSql('ALTER TABLE pets DROP updated_at');

        $this->addSql('ALTER TABLE users DROP roles');
        $this->addSql('ALTER TABLE users DROP bio');
        $this->addSql('ALTER TABLE users DROP profile_image');
        $this->addSql('ALTER TABLE users DROP date_of_birth');
        $this->addSql('ALTER TABLE users DROP created_at');
        $this->addSql('ALTER TABLE users DROP updated_at');
    }
}
