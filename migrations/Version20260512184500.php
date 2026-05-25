<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260512184500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Allow one owner to swipe and match with several pets';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE pet_likes DROP INDEX unique_user_pet_like');
        $this->addSql('ALTER TABLE pet_matches DROP INDEX unique_user_pet_match');
        $this->addSql('ALTER TABLE pet_likes ADD owner_pet_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE pet_matches ADD owner_pet_id INT DEFAULT NULL');
        $this->addSql('UPDATE pet_likes pl SET owner_pet_id = (SELECT p.id FROM pets p WHERE p.owner_id = pl.user_id ORDER BY p.id ASC LIMIT 1)');
        $this->addSql('UPDATE pet_matches pm SET owner_pet_id = (SELECT p.id FROM pets p WHERE p.owner_id = pm.user_id ORDER BY p.id ASC LIMIT 1)');
        $this->addSql('DELETE FROM pet_likes WHERE owner_pet_id IS NULL');
        $this->addSql('DELETE FROM pet_matches WHERE owner_pet_id IS NULL');
        $this->addSql('ALTER TABLE pet_likes MODIFY owner_pet_id INT NOT NULL');
        $this->addSql('ALTER TABLE pet_matches MODIFY owner_pet_id INT NOT NULL');
        $this->addSql('CREATE INDEX IDX_987E49243168D6BF ON pet_likes (owner_pet_id)');
        $this->addSql('CREATE UNIQUE INDEX unique_owner_pet_like ON pet_likes (owner_pet_id, pet_id)');
        $this->addSql('CREATE INDEX IDX_EAB52A13168D6BF ON pet_matches (owner_pet_id)');
        $this->addSql('CREATE UNIQUE INDEX unique_owner_pet_match ON pet_matches (owner_pet_id, pet_id)');
        $this->addSql('ALTER TABLE pet_likes ADD CONSTRAINT FK_987E49243168D6BF FOREIGN KEY (owner_pet_id) REFERENCES pets (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pet_matches ADD CONSTRAINT FK_EAB52A13168D6BF FOREIGN KEY (owner_pet_id) REFERENCES pets (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE pet_likes DROP FOREIGN KEY FK_987E49243168D6BF');
        $this->addSql('ALTER TABLE pet_matches DROP FOREIGN KEY FK_EAB52A13168D6BF');
        $this->addSql('DROP INDEX unique_owner_pet_like ON pet_likes');
        $this->addSql('DROP INDEX unique_owner_pet_match ON pet_matches');
        $this->addSql('DROP INDEX IDX_987E49243168D6BF ON pet_likes');
        $this->addSql('DROP INDEX IDX_EAB52A13168D6BF ON pet_matches');
        $this->addSql('ALTER TABLE pet_likes DROP owner_pet_id');
        $this->addSql('ALTER TABLE pet_matches DROP owner_pet_id');
        $this->addSql('CREATE UNIQUE INDEX unique_user_pet_like ON pet_likes (user_id, pet_id)');
        $this->addSql('CREATE UNIQUE INDEX unique_user_pet_match ON pet_matches (user_id, pet_id)');
    }
}
