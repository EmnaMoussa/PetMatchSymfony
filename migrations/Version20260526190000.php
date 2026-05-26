<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260526190000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Store every swipe decision so skipped pets do not reappear';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE pet_swipes (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, owner_pet_id INT NOT NULL, pet_id INT NOT NULL, decision VARCHAR(10) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_A41A0B69A76ED395 (user_id), INDEX IDX_A41A0B693168D6BF (owner_pet_id), INDEX IDX_A41A0B69E4529B85 (pet_id), UNIQUE INDEX unique_owner_pet_swipe (owner_pet_id, pet_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pet_swipes ADD CONSTRAINT FK_A41A0B69A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pet_swipes ADD CONSTRAINT FK_A41A0B693168D6BF FOREIGN KEY (owner_pet_id) REFERENCES pets (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pet_swipes ADD CONSTRAINT FK_A41A0B69E4529B85 FOREIGN KEY (pet_id) REFERENCES pets (id) ON DELETE CASCADE');
        $this->addSql('INSERT INTO pet_swipes (user_id, owner_pet_id, pet_id, decision, created_at) SELECT user_id, owner_pet_id, pet_id, "like", created_at FROM pet_likes');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE pet_swipes DROP FOREIGN KEY FK_A41A0B69A76ED395');
        $this->addSql('ALTER TABLE pet_swipes DROP FOREIGN KEY FK_A41A0B693168D6BF');
        $this->addSql('ALTER TABLE pet_swipes DROP FOREIGN KEY FK_A41A0B69E4529B85');
        $this->addSql('DROP TABLE pet_swipes');
    }
}
