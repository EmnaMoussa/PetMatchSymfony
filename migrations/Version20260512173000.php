<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260512173000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create PetMatch Symfony schema and demo data';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, prenom VARCHAR(100) NOT NULL, nom VARCHAR(100) NOT NULL, email VARCHAR(180) NOT NULL, password_hash VARCHAR(255) NOT NULL, telephone VARCHAR(40) DEFAULT NULL, ville VARCHAR(100) DEFAULT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pets (id INT AUTO_INCREMENT NOT NULL, owner_id INT NOT NULL, nom VARCHAR(100) NOT NULL, espece VARCHAR(60) NOT NULL, race VARCHAR(100) DEFAULT NULL, age INT DEFAULT NULL, sexe VARCHAR(20) DEFAULT NULL, ville VARCHAR(100) DEFAULT NULL, bio LONGTEXT DEFAULT NULL, photo VARCHAR(255) NOT NULL, INDEX IDX_4C3A3BBE7E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pet_likes (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, pet_id INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_987E4924A76ED395 (user_id), INDEX IDX_987E4924E4529B85 (pet_id), UNIQUE INDEX unique_user_pet_like (user_id, pet_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pet_matches (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, pet_id INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_EAB52A1A76ED395 (user_id), INDEX IDX_EAB52A1E4529B85 (pet_id), UNIQUE INDEX unique_user_pet_match (user_id, pet_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messages (id INT AUTO_INCREMENT NOT NULL, sender_id INT NOT NULL, receiver_id INT NOT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_DB021E96F624B39D (sender_id), INDEX IDX_DB021E96CD53EDB6 (receiver_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pets ADD CONSTRAINT FK_4C3A3BBE7E3C61F9 FOREIGN KEY (owner_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pet_likes ADD CONSTRAINT FK_987E4924A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pet_likes ADD CONSTRAINT FK_987E4924E4529B85 FOREIGN KEY (pet_id) REFERENCES pets (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pet_matches ADD CONSTRAINT FK_EAB52A1A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pet_matches ADD CONSTRAINT FK_EAB52A1E4529B85 FOREIGN KEY (pet_id) REFERENCES pets (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE messages ADD CONSTRAINT FK_DB021E96F624B39D FOREIGN KEY (sender_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE messages ADD CONSTRAINT FK_DB021E96CD53EDB6 FOREIGN KEY (receiver_id) REFERENCES users (id) ON DELETE CASCADE');

        $hash = password_hash('password', PASSWORD_DEFAULT);
        $this->addSql("INSERT INTO users (prenom, nom, email, password_hash, telephone, ville) VALUES
            ('Sara', 'Ben Ali', 'sara@petmatch.tn', '$hash', '+216 20 111 222', 'Tunis'),
            ('Karim', 'Mansour', 'karim@petmatch.tn', '$hash', '+216 21 333 444', 'Sfax'),
            ('Nour', 'Trabelsi', 'nour@petmatch.tn', '$hash', '+216 22 555 666', 'Sousse'),
            ('Yasmine', 'Kacem', 'yasmine@petmatch.tn', '$hash', '+216 23 777 888', 'Bizerte')");
        $this->addSql("INSERT INTO pets (owner_id, nom, espece, race, age, sexe, ville, bio, photo) VALUES
            (1, 'Luna', 'chien', 'Labrador', 3, 'Femelle', 'Tunis', 'Douce et caline, Luna adore jouer au parc.', 'chat1.webp'),
            (2, 'Max', 'chien', 'Rottweiler', 2, 'Male', 'Sfax', 'Joueur et affectueux, Max adore les promenades.', 'chat2.jpg'),
            (3, 'Coco', 'oiseau', 'Perroquet', 1, 'Male', 'Sousse', 'Coco dit bonjour a tout le monde.', 'chat3.webp'),
            (4, 'Simba', 'chat', 'Maine Coon', 2, 'Male', 'Bizerte', 'Majestueux et independant, Simba aime les calins.', 'match.webp')");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE messages DROP FOREIGN KEY FK_DB021E96CD53EDB6');
        $this->addSql('ALTER TABLE messages DROP FOREIGN KEY FK_DB021E96F624B39D');
        $this->addSql('ALTER TABLE pet_matches DROP FOREIGN KEY FK_EAB52A1A76ED395');
        $this->addSql('ALTER TABLE pet_matches DROP FOREIGN KEY FK_EAB52A1E4529B85');
        $this->addSql('ALTER TABLE pet_likes DROP FOREIGN KEY FK_987E4924A76ED395');
        $this->addSql('ALTER TABLE pet_likes DROP FOREIGN KEY FK_987E4924E4529B85');
        $this->addSql('ALTER TABLE pets DROP FOREIGN KEY FK_4C3A3BBE7E3C61F9');
        $this->addSql('DROP TABLE messages');
        $this->addSql('DROP TABLE pet_matches');
        $this->addSql('DROP TABLE pet_likes');
        $this->addSql('DROP TABLE pets');
        $this->addSql('DROP TABLE users');
    }
}
